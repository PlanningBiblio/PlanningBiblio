<?php

namespace App\Repository;

use App\Model\PlanningPositionLines;
use Doctrine\ORM\EntityRepository;

class PlanningPositionLinesRepository extends EntityRepository
{

    public function delete($number)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete()
                ->from(PlanningPositionLines::class, 'p')
                ->where('p.numero = :number')
                ->setParameter('number', $number);
        $results = $builder->getQuery()->getResult();
    }
}
