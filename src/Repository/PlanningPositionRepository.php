<?php

namespace App\Repository;

use App\Entity\Absence;
use App\Entity\Holiday;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionHours;
use App\Entity\PlanningPositionTabAffectation;
use DateTime;
use Doctrine\ORM\EntityRepository;

class PlanningPositionRepository extends EntityRepository
{

    public function getPositions()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(array('p'))
                ->from(PlanningPosition::class, 'p')
                ->addGroupBy('p.poste');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function countDistinctDatesBetween($start, $end): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.date)')
            ->where('p.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getEndOfServicePositions(string $date, string $site = '1')
    {
        $em = $this->getEntityManager();

        $date_dt = DateTime::createFromFormat('Y-m-d', $date);

        // Sélection du tableau utilisé
        $tableau = $em->getRepository(PlanningPositionTabAffectation::class)
            ->findOneBy(['date' => $date_dt, 'site' => $site])
            ->getTable();

        // Sélection de l'heure de fin
        $fin = $em->getRepository(PlanningPositionHours::class)
            ->getTableLast($tableau)
            ->getEnd()
            ->format('H:i:s');

        $qb = $this->createQueryBuilder('p');
        $qb->where('p.fin = :fin');
        $qb->andWhere('p.site = :site');
        $qb->andWhere('p.date = :date');
        $qb->andWhere('p.supprime = 0');
        $qb->andWhere('p.absent = 0');

        $qb->setParameter('fin', $fin);
        $qb->setParameter('site', $site);
        $qb->setParameter('date', $date);

        $absenceSubQb = $em->createQueryBuilder()
            ->select('a')
            ->from(Absence::class, 'a')
            ->where('a.perso_id = p.perso_id')
            ->andWhere('a.valide > 0')
            ->andWhere('p.fin BETWEEN a.debut AND a.fin');
        $qb->andWhere($qb->expr()->not($qb->expr()->exists($absenceSubQb->getDql())));

        $holidaySubQb = $em->createQueryBuilder()
            ->select('h')
            ->from(Holiday::class, 'h')
            ->where('h.perso_id = p.perso_id')
            ->andWhere('h.valide > 0')
            ->andWhere('p.fin BETWEEN h.debut AND h.fin');
        $qb->andWhere($qb->expr()->not($qb->expr()->exists($holidaySubQb->getDql())));

        $query = $qb->getQuery();

        return $query->getResult();
    }
}
