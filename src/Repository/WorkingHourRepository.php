<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

use App\Model\WorkingHour;

class WorkingHourRepository extends EntityRepository
{

    public function get($start, $end = null, $valid = true, $perso_id = null)
    {

        $end = $end ?? $start;

        $entityManager = $this->getEntityManager();

        $builder = $entityManager->createQueryBuilder();

        $builder->select('w')
            ->from(WorkingHour::class, 'w')
            ->andWhere('w.debut <= :end')
            ->andWhere('w.fin >= :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        if ($perso_id) {
            $builder->andWhere('w.perso_id = :perso_id')
                ->setParameter('perso_id', $perso_id);
        }

        if ($valid) {
            $builder->andWhere('w.valide > 0');
        }

        return $builder->getQuery()->getResult();
    }

}
