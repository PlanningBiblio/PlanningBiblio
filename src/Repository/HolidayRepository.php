<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Holiday;
use Doctrine\ORM\EntityRepository;

class HolidayRepository extends EntityRepository
{

    public function get($start, $end = null, $valid = true)
    {
        $end = $end ?? $start;

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('h')
                ->from(Holiday::class, 'h')
                ->andWhere('h.debut < :end')
                ->andWhere('h.fin > :start')
                ->andWhere('h.information = 0')
                ->andWhere('h.supprime = 0')
                ->setParameter('start', $start)
                ->setParameter('end', $end);

        if ($valid) {
            $builder->andWhere('h.valide > 0');
        } else {
            $builder->andWhere('h.valide = 0');
        }

        $results = $builder->getQuery()->getResult();

        return $results;
    }

    public function insert($userId, $credits, $action="modif", $cron=false, $origin_id = 0)
    {
        $entityManager = $this->getEntityManager();

       // Ajoute une ligne faisant apparaître la mise à jour des crédits dans le tableau Congés
        if ($action=="modif") {
            $agent = $entityManager->getRepository(Agent::class)->find($userId);
            $old = array("conges_credit"=>$agent->getHolidayCredit(), "comp_time"=>$agent->getHolidayCompTime(),
    "conges_reliquat"=>$agent->getHolidayRemainder(), "conges_anticipation"=>$agent->getHolidayAnticipation());
        } else {
            $old = array("conges_credit"=>0, "comp_time"=>0, "conges_reliquat"=>0, "conges_anticipation"=>0);
        }

        unset($credits["conges_annuel"]);

        if ($credits!=$old) {
            if ($origin_id) {
                $holiday = $entityManager->getRepository(Holiday::class)->find($origin_id);
                $holiday->setStart($holiday->getStart());
                $holiday->setEnd($holiday->getEnd());
                $holiday->setHours($old['comp_time'] - $credits['comp_time']);
                $holiday->setOriginId($origin_id);            
            } else {
                $holiday = new Holiday();
                $holiday->setStart(null);
                $holiday->setEnd(null);
            }

            $holiday->setUser($userId);
            $holiday->setPreviousCredit($old['conges_credit']);
            $holiday->setPreviousCompTime($old['comp_time']);
            $holiday->setPreviousRemainder($old['conges_reliquat']);
            $holiday->setPreviousAnticipation($old['conges_anticipation']);
            $holiday->setActualCredit($credits['conges_credit']);
            $holiday->setActualCompTime($credits['comp_time']);
            $holiday->setActualRemainder($credits['conges_reliquat']);
            $holiday->setActualAnticipation($credits['conges_anticipation']);
            $holiday->setInfo($cron?999999999:$_SESSION['login_id']);
            $holiday->setInfoDate((new \DateTime()));

            $entityManager->persist($holiday);
            $entityManager->flush();
        }
    }
}
