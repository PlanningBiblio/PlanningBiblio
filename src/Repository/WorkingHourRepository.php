<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;
use App\Entity\WorkingHour;

class WorkingHourRepository extends EntityRepository
{

    public function changeCurrent(): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update(WorkingHour::class, 'w')
            ->set('w.actuel', 0)
            ->where('w.debut > CURRENT_DATE() OR w.fin < CURRENT_DATE()')
            ->getQuery()
            ->execute();

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update(WorkingHour::class, 'w')
            ->set('w.actuel', 1)
            ->where('w.debut <= CURRENT_DATE() AND w.fin >= CURRENT_DATE()')
            ->getQuery()
            ->execute();

    }

    /**
     * Returns all the entities that have been imported (cle is not null)
     */
    public function findImported(): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.cle IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

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

        $result = $builder->getQuery()->getResult();

        return $result;
    }

}
