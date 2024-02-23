<?php

namespace App\Repository;

use App\Model\PlanningPositionCells;
use Doctrine\ORM\EntityRepository;

class PlanningPositionCellsRepository extends EntityRepository
{

    public function delete($number)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete()
                ->from(PlanningPositionCells::class, 'p')
                ->where('p.numero = :number')
                ->setParameter('number', $number);
        $results = $builder->getQuery()->getResult();
    }
}
