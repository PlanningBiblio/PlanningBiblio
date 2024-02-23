<?php

namespace App\Repository;

use App\Model\PlanningPositionHours;
use Doctrine\ORM\EntityRepository;

class PlanningPositionHoursRepository extends EntityRepository
{

    public function delete($number)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete()
                ->from(PlanningPositionHours::class, 'p')
                ->where('p.numero = :number')
                ->setParameter('number', $number);
        $results = $builder->getQuery()->getResult();
    }
}
