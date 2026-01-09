<?php

namespace App\Repository;

use App\Entity\PlanningPosition;
use Doctrine\ORM\EntityRepository;

class PlanningPositionRepository extends EntityRepository
{

    public function getPositions()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(array('p'))
                ->from(PlanningPosition::class, 'p')
                ->addGroupBy('p.poste');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function countDistinctDatesBetween($start, $end): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.date)')
            ->where('p.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function updateDeletionByUserId(int $persoId)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->update()
            ->set('p.supprime', 0)
            ->where('p.perso_id = :persoId')
            ->setParameter('persoId', $persoId)
            ->getQuery()
            ->execute();
    }

    public function updateAsDeleteAfterDate($userIds, string $date)
    {
        $userIds = is_array($userIds) ? $userIds : [$userIds];

        return $this->createQueryBuilder('p')
            ->update()
            ->set('p.supprime', 1)
            ->where('p.perso_id IN (:perso_ids)')
            ->andWhere('p.date > :date')
            ->setParameter('perso_ids', $userIds)
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }

    public function updateDeletionByIdAndDate(int $userId, string $date)
    {
        return $this->createQueryBuilder('p')
            ->update()
            ->set('p.supprime', 1)
            ->where('p.perso_id = :perso_id')
            ->andWhere('p.date = :date')
            ->setParameter('perso_id', $userId)
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
