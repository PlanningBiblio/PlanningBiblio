<?php

namespace App\Repository;

use App\Entity\Agent;
use App\Entity\Holiday;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;

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

    /**
    * @method insert
    * @param int $userId
    * @param array $credits
    * @param string $action
    * @param bool $cron
    * @param int $originId. Holiday id that generated this regularization.
    * Les crédits obtenus à des dates supérieures sont déduits
    */
    public function insert($userId, $credits, $action = 'update', $cron = false, $originId = 0): void
    {
        $session = new Session();
        $loginId = $cron ? 999999999 : (int) $session->get('loginId');

        $entityManager = $this->getEntityManager();

       // Ajoute une ligne faisant apparaître la mise à jour des crédits dans le tableau Congés
       // TODO / FIXME : lorsqu'entityManager sera utilisé dans AgentController, nous pourrons récupérer $userId aussi bien en modification qu'en ajout,
       // et nous pourrons supprimer le contrôle if($action) suivant. La variable $action ne sera plus nécessaire et pourra être supprimée.
        if ($action == 'update') {
            $agent = $entityManager->getRepository(Agent::class)->find($userId);
        } else {
            $agent = new Agent();
        }

        $old = array(
            'conges_credit' => $agent->getHolidayCredit(),
            'comp_time' => $agent->getHolidayCompTime(),
            'conges_reliquat' => $agent->getHolidayRemainder(),
            'conges_anticipation' => $agent->getHolidayAnticipation(),
        );

        unset($credits['conges_annuel']);

        if ($credits != $old) {
            if ($originId) {
                $holiday = $entityManager->getRepository(Holiday::class)->find($originId);
                $holiday->setStart($holiday->getStart());
                $holiday->setEnd($holiday->getEnd());
                $holiday->setHours($old['comp_time'] - $credits['comp_time']);
                $holiday->setOriginId($originId);
            } else {
                $holiday = new Holiday();
                $holiday->setStart(new \DateTime(date('Y-m-d 00:00:00')));
                $holiday->setEnd(new \DateTime(date('Y-m-d 00:00:00')));
            }

            $holiday->setUser($userId);
            $holiday->setPreviousCredit($old['conges_credit']);
            $holiday->setPreviousCompTime($old['comp_time']);
            $holiday->setPreviousRemainder($old['conges_reliquat']);
            $holiday->setPreviousAnticipation($old['conges_anticipation']);
            $holiday->setActualCredit((float)$credits['conges_credit']);
            $holiday->setActualCompTime((float)$credits['comp_time']);
            $holiday->setActualRemainder((float)$credits['conges_reliquat']);
            $holiday->setActualAnticipation((float)$credits['conges_anticipation']);
            $holiday->setInfo($loginId);
            $holiday->setInfoDate((new \DateTime()));

            $entityManager->persist($holiday);
            $entityManager->flush();
        }
    }
}
