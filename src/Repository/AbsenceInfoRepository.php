<?php

namespace App\Repository;

use App\Entity\AbsenceInfo;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<AbsenceInfo>
 */
class AbsenceInfoRepository extends EntityRepository
{
    public function getByDateRange($start, $end = null)
    {
        $end = $end ?? $start;
        $start = $start->setTime(0,0);
        $end = $end->setTime(23,59);

        return $this->createQueryBuilder('a')
            ->andWhere('a.debut <= :end')
            ->andWhere('a.fin >= :start')
            ->orderBy('a.debut', 'ASC')
            ->orderBy('a.fin', 'ASC')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }
}
