<?php

namespace App\Repository;

use App\Entity\IPBlocker;
use DateTimeInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<IPBlocker>
 */
class IPBlockerRepository extends EntityRepository
{
    public function ipIsBlocked(string $ipAddress, DateTimeInterface $waitSince): bool
    {
        return (bool) $this->createQueryBuilder('i')
            ->andWhere('i.ip = :ip')
            ->andWhere('i.status = \'blocked\'')
            ->andWhere('i.timestamp >= :timestamp')
            ->orderBy('i.timestamp', 'ASC')
            ->setParameter('ip', $ipAddress)
            ->setParameter('timestamp', $waitSince)
            ->getQuery()
            ->getResult();
    }
}
