<?php

namespace App\Repository;

use App\Entity\RecurringAbsence;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<RecurringAbsence>
 */
class RecurringAbsenceRepository extends EntityRepository
{
    public function findRecurringAbsenceActiveNotCheckedToday(): array
    {
        return $this->createQueryBuilder('ra')
            ->andWhere('ra.end = :end')
            ->andWhere('ra.last_check < CURRENT_DATE()')
            ->setParameter('end', 0)
            ->getQuery()
            ->getResult();
    }
}
