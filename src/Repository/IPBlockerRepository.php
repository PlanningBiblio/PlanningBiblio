<?php

namespace App\Repository;

use App\Entity\IPBlocker;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Types\Types;

/**
 * @extends EntityRepository<IPBlocker>
 */
class IPBlockerRepository extends EntityRepository
{
    // Returns the number of failed login attempts from a provided IP address over the last X minutes.
    public function getNumberOfFailures(String $address, DateTimeInterface $timestamp): int
    {
        return $this->createQueryBuilder('i')
            ->select('count(i.ip)')
            ->andWhere('i.ip = :ip')
            ->andWhere('i.status = \'failure\'')
            ->andWhere('i.timestamp >= :timestamp')
            ->setParameter('ip', $address)
            ->setParameter('timestamp', $timestamp, Types::DATETIME_MUTABLE)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
