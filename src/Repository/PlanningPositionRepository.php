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

    /**
     * Updates the deletion flag for a given user.
     *
     * This method sets the deletion flag to active
     * for the user identified by the given ID.
     *
     * @param int $userId User ID
     * @return int Number of affected rows
     */
    public function updateAsDeletedByUserId(int $userId)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb
            ->update()
            ->set('p.supprime', 0)
            ->where('p.perso_id = :persoId')
            ->setParameter('persoId', $userId)
            ->getQuery()
            ->execute();
    }

    /**
     * Updates users as deleted for a given user and after a given date.
     *
     * This method sets the deletion flag for users
     * whose date is after the given value.
     *
     * @param array|int $userIds User ID or list of user IDs
     * @param string $date Date threshold
     * @return int Number of affected rows
     */
    public function updateAsDeleteByUserIdAndAfterDate($userIds, string $date)
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

    /**
     * Updates the deletion flag for a user on a given date.
     *
     * This method marks the user as deleted
     * when the user ID and date match.
     *
     * @param int $userId User ID
     * @param string $date Date value
     * @return int Number of affected rows
     */
    public function updateAsDeletedByUserIdAndThatDate(int $userId, string $date)
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
