<?php

namespace App\Repository;

use App\Model\PlanningPositionLock;
use Doctrine\ORM\EntityRepository;

class PlanningPositionLockRepository extends EntityRepository
{

    public function delete($start, $end = null, $site = 1)
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
}
