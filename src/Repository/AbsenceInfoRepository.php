<?php

namespace App\Repository;

use App\Entity\AbsenceInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AbsenceInfo>
 */
class AbsenceInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbsenceInfo::class);
    }

    public function get($start, $end)
    {
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
