<?php

namespace App\Repository;

use App\Entity\HolidayInfo;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Types;

/**
 * @extends AbstractInfoRepository<HolidayInfo>
 */
class HolidayInfoRepository extends EntityRepository
{
    /**
     * Returns all entities whose start-end date range intersects with the
     * passed date range
     *
     * $end defaults to $start if not passed
     *
     * @return HolidayInfo[]
     */
    public function findByDateRange(DateTimeInterface $start, ?DateTimeInterface $end = null): array
    {
        $end = $end ?? $start;

        return $this->createQueryBuilder('a')
            ->andWhere('a.debut <= :end')
            ->andWhere('a.fin >= :start')
            ->orderBy('a.debut', 'ASC')
            ->addOrderBy('a.fin', 'ASC')
            ->setParameter('start', $start, Types::DATE_MUTABLE)
            ->setParameter('end', $end, Types::DATE_MUTABLE)
            ->getQuery()
            ->getResult();
    }
}
