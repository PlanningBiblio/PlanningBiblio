<?php

namespace App\Repository;

use App\Entity\AdminInfo;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Base class of AdminInfoRepository, HolidayInfoRepository and
 * AbsenceInfoRepository
 *
 * @template T of object
 * @extends EntityRepository<T>
 */
abstract class AbstractInfoRepository extends EntityRepository
{
    /**
     * Filter out entities that are not within the given date range. An entity
     * is within a date range if its start date or its end date is within the
     * date range.
     * Both bounds are inclusive.
     * Both bounds are optional.
     */
    public function filterByDateRange(QueryBuilder $qb, ?DateTimeInterface $since, ?DateTimeInterface $until): void
    {
        $rootAliases = $qb->getRootAliases();
        $rootAlias = $rootAliases[0];

        if ($since) {
            $qb->andWhere($qb->expr()->gte("$rootAlias.fin", ':since'));
            $qb->setParameter('since', $since->format('Y-m-d'));
        }

        if ($until) {
            $qb->andWhere($qb->expr()->lte("$rootAlias.debut", ':until'));
            $qb->setParameter('until', $until->format('Y-m-d'));
        }
    }
}
