<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Entity\PlanningPositionHours;

class PlanningPositionHoursRepository extends EntityRepository
{
    public function getTableLast(string $table): ?PlanningPositionHours
    {
        $qb = $this->createQueryBuilder('h');
        $qb->where($qb->expr()->eq('h.numero', $table));
        $qb->orderBy('h.fin', 'desc');

        $query = $qb->getQuery();
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }
}
