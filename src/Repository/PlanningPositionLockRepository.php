<?php

namespace App\Repository;

use App\Entity\PlanningPositionLock;
use App\Planno\DateTime\TimeSlot;
use Doctrine\ORM\EntityRepository;

class PlanningPositionLockRepository extends EntityRepository
{

    public function delete($start, $end = null, $site = 1): void
    {
        $end = $end ?? $start;

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete()
                ->from(PlanningPositionLock::class, 'p')
                ->andWhere('p.date BETWEEN :start AND :end')
                ->andWhere('p.site = :site')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->setParameter('site', $site);
        $results = $builder->getQuery()->getResult();
    }

    /**
     * @param TimeSlot[] $timeSlots Only dates within at least one of these
     *                              time slots will be returned
     * @return string[] A list of dates (YYYY-MM-DD)
     */
    public function findApprovedDatesByTimeSlots(array $timeSlots): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('DISTINCT p.date');
        $qb->andWhere('p.verrou2 = 1');

        $paramIdx = 0;
        $dateExpr = $qb->expr()->orX();
        foreach ($timeSlots as $timeSlot) {
            $startParamName = sprintf('start_%d', $paramIdx);
            $endParamName = sprintf('end_%d', $paramIdx);
            $dateExpr->add($qb->expr()->between('p.date', ":$startParamName", ":$endParamName"));
            $qb->setParameter($startParamName, $timeSlot->start->format('Y-m-d'));
            $qb->setParameter($endParamName, $timeSlot->end->format('Y-m-d'));
            $paramIdx++;
        }
        $qb->andWhere($dateExpr);

        $qb->orderBy('p.date', 'ASC');

        return $qb->getQuery()->getSingleColumnResult();
    }
}
