<?php

namespace App\Repository;

use App\Entity\AdminInfo;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Types;

/**
 * @extends EntityRepository<AdminInfo>
 */
class AdminInfoRepository extends EntityRepository
{
    /**
     * Returns all entities whose start-end date range intersects with the
     * passed date range
     *
     * $end defaults to $start if not passed
     *
     * If $site is passed, only the messages displayed on this site are
     * returned (messages for all sites included). Without $site, no filter
     * is applied (administration list).
     *
     * Messages with the same dates are sorted by creation order (id).
     *
     * @return AdminInfo[]
     */
    public function findByDateRange(DateTimeInterface $start, ?DateTimeInterface $end = null, ?int $site = null): array
    {
        $end = $end ?? $start;

        $result = $this->createQueryBuilder('a')
            ->andWhere('a.debut <= :end')
            ->andWhere('a.fin >= :start')
            ->orderBy('a.debut', 'ASC')
            ->addOrderBy('a.fin', 'ASC')
            ->addOrderBy('a.id', 'ASC')
            ->setParameter('start', $start, Types::DATE_MUTABLE)
            ->setParameter('end', $end, Types::DATE_MUTABLE)
            ->getQuery()
            ->getResult();

        if ($site === null) {
            return $result;
        }

        return array_values(array_filter(
            $result,
            fn (AdminInfo $info) => $info->isDisplayedOnSite($site)
        ));
    }
}
