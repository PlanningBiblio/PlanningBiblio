<?php

namespace App\Repository;

use App\Model\Holiday;
use Doctrine\ORM\EntityRepository;

class HolidayRepository extends EntityRepository
{

    public function get($start, $end = null)
    {
        $end = $end ?? $start;

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('h')
                ->from(Holiday::class, 'h')
                ->andWhere('h.debut < :end')
                ->andWhere('h.fin > :start')
                ->andWhere('h.valide > 0')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        $results = $builder->getQuery()->getResult();

        return $results;
    }
}
