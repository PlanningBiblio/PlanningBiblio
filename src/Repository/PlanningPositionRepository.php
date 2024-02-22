<?php

namespace App\Repository;

use App\Model\PlanningPosition;
use Doctrine\ORM\EntityRepository;

class PlanningPositionRepository extends EntityRepository
{

    public function getByDate($date, $site = 1)
    {

        if (is_array($date)) {
            foreach ($date as &$d) {
                $d = $d->format('Y-m-d');
            }
        } else {
            $date = array($date->format('Y-m-d'));
        }

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('p.date')
                ->from(PlanningPosition::class, 'p')
                ->andWhere('p.date IN (:date)')
                ->andWhere('p.site = :site')
                ->orderBy('p.date', 'ASC')
                ->setParameter('date', $date)
                ->setParameter('site', $site);
        $results = $builder->getQuery()->getResult();

        return $results;
    }
}
