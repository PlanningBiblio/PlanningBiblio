<?php

namespace App\Repository;

use App\Entity\Absence;
use App\Entity\Holiday;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionHours;
use App\Entity\PlanningPositionTabAffectation;
use App\Planno\DateTime\TimeSlot;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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
     * Updates users as deleted for a given user and after a given date.
     *
     * This method sets the deletion flag for users
     * whose date is after the given value.
     *
     * @param array $userIds, array of user IDs
     * @param string $date Date threshold
     * @return int Number of affected rows
     */
    // TODO: In AgentController::save, the former action set supprime = 0 for all lines for concerned Agents before setting 1 after the date.
    // Check if it's still necesarry, have tests with deletion date changements
    public function updateAsDeleteByUserIdAndAfterDate($userIds, $date): void
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.supprime', 0)
            ->where('p.perso_id IN (:perso_ids)')
            ->setParameter('perso_ids', $userIds)
            ->getQuery()
            ->execute();

        if ($date != null) {
            $this->createQueryBuilder('p')
                ->update()
                ->set('p.supprime', 1)
                ->where('p.perso_id IN (:perso_ids)')
                ->andWhere('p.date > :date')
                ->setParameter('perso_ids', $userIds)
                ->setParameter('date', $date)
                ->getQuery()
                ->execute();
        }
    }

    public function getEndOfServicePositions(string $date, string $site = '1')
    {
        $em = $this->getEntityManager();

        $date_dt = DateTime::createFromFormat('Y-m-d', $date);

        // Sélection du tableau utilisé
        $table = $em->getRepository(PlanningPositionTabAffectation::class)
            ->findOneBy(['date' => $date_dt, 'site' => $site])
            ->getTable();

        // Sélection de l'heure de fin
        $end = $em->getRepository(PlanningPositionHours::class)
            ->getTableLast($table)
            ->getEnd()
            ->format('H:i:s');

        $qb = $this->createQueryBuilder('p');
        $qb->where('p.fin = :fin');
        $qb->andWhere('p.site = :site');
        $qb->andWhere('p.date = :date');
        $qb->andWhere('p.supprime = 0');
        $qb->andWhere('p.absent = 0');

        $qb->setParameter('fin', $end);
        $qb->setParameter('site', $site);
        $qb->setParameter('date', $date);

        $absenceSubQb = $em->createQueryBuilder()
            ->select('a')
            ->from(Absence::class, 'a')
            ->where('a.perso_id = p.perso_id')
            ->andWhere('a.valide > 0')
            ->andWhere('a.debut < ' . $qb->expr()->concat('p.date', $qb->expr()->literal(' '), 'p.fin'))
            ->andWhere('a.fin > ' . $qb->expr()->concat('p.date', $qb->expr()->literal(' '), 'p.debut'));
        $qb->andWhere($qb->expr()->not($qb->expr()->exists($absenceSubQb->getDql())));

        $holidaySubQb = $em->createQueryBuilder()
            ->select('h')
            ->from(Holiday::class, 'h')
            ->where('h.perso_id = p.perso_id')
            ->andWhere('h.valide > 0')
            ->andWhere('h.information = 0')
            ->andWhere('h.supprime = 0')
            ->andWhere('h.debut < ' . $qb->expr()->concat('p.date', $qb->expr()->literal(' '), 'p.fin'))
            ->andWhere('h.fin > ' . $qb->expr()->concat('p.date', $qb->expr()->literal(' '), 'p.debut'));
        $qb->andWhere($qb->expr()->not($qb->expr()->exists($holidaySubQb->getDql())));

        $query = $qb->getQuery();

        return $query->getResult();
    }

    /**
     * @param int[] $agentIds
     * @param TimeSlot[] $timeSlots Only positions within at least one of these
     *                              time slots will be returned
     * @return PlanningPosition[]
     */
    public function findByAgentsAndTimeSlots(array $agentIds, array $timeSlots): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->in('p.perso_id', ':perso_ids'));
        $qb->setParameter('perso_ids', $agentIds);

        $dateExpr = $qb->expr()->orX();
        $paramIdx = 0;
        foreach ($timeSlots as $timeSlot) {
            $startParamName = sprintf('start_%d', $paramIdx);
            $endParamName = sprintf('end_%d', $paramIdx);
            $dateExpr->add($qb->expr()->between('p.date', ":$startParamName", ":$endParamName"));
            $qb->setParameter($startParamName, $timeSlot->start->format('Y-m-d'));
            $qb->setParameter($endParamName, $timeSlot->end->format('Y-m-d'));
            $paramIdx++;
        }
        $qb->andWhere($dateExpr);
        $qb->addOrderBy('p.date', 'ASC');
        $qb->addOrderBy('p.debut', 'ASC');
        $qb->addOrderBy('p.fin', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Adds a filter to keep only rows that insersects with at least one of
     * given time slots.
     *
     * @param QueryBuilder $qb
     * @param TimeSlot[] $timeSlots
     */
    protected function filterByTimeSlots(QueryBuilder $qb, array $timeSlots): void
    {
        $rootAliases = $qb->getRootAliases();
        $rootAlias = $rootAliases[0];

        $dateExpr = $qb->expr()->orX();
        $paramIdx = 0;
        foreach ($timeSlots as $timeSlot) {
            $startParamName = sprintf('start_%d', $paramIdx);
            $endParamName = sprintf('end_%d', $paramIdx);
            $dateExpr->add(
                $qb->expr()->andX(
                    // These conditions cannot use database indices so the
                    // query might be slow on large datasets.
                    // One possible solution: create two generated columns
                    $qb->expr()->lt(
                        $qb->expr()->concat("$rootAlias.date", $qb->expr()->literal(' '), "$rootAlias.debut"),
                        ":$endParamName"
                    ),
                    $qb->expr()->gt(
                        $qb->expr()->concat("$rootAlias.date", $qb->expr()->literal(' '), "$rootAlias.fin"),
                        ":$startParamName"
                    )
                )
            );
            $qb->setParameter($startParamName, $timeSlot->start->format('Y-m-d H:i:s'));
            $qb->setParameter($endParamName, $timeSlot->end->format('Y-m-d H:i:s'));
            $paramIdx++;
        }
        $qb->andWhere($dateExpr);
    }

    /**
     * @param int[] $agentIds
     * @param TimeSlot[] $timeSlots Only positions within at least one of these
     *                              time slots will be returned
     * @return string[]
     */
    public function findApprovedDatesByAgentsAndTimeSlots(array $agentIds, array $timeSlots): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('DISTINCT p.date');
        $qb->andWhere($qb->expr()->in('p.perso_id', ':perso_ids'));
        $qb->setParameter('perso_ids', $agentIds);

        $this->filterByTimeSlots($qb, $timeSlots);

        $lockQb = $this->getEntityManager()->createQueryBuilder();
        $lockQb->from('App\Entity\PlanningPositionLock', 'lock');
        $lockQb->select('lock');
        $lockQb->andWhere('lock.date = p.date');
        $lockQb->andWhere('lock.site = p.site');
        $lockQb->andWhere('lock.verrou2 = 1');

        $qb->andWhere($qb->expr()->exists($lockQb->getDql()));

        return $qb->getQuery()->getSingleColumnResult();
    }

    /**
     * @param int[] $siteIds
     * @param TimeSlot[] $timeSlots Only positions within at least one of these
     *                              time slots will be returned
     * @return string[]
     */
    public function findInProgressDatesBySitesAndTimeSlots(array $siteIds, array $timeSlots): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('DISTINCT p.date');
        $qb->andWhere($qb->expr()->in('p.site', ':site_ids'));
        $qb->setParameter('site_ids', $siteIds);

        $this->filterByTimeSlots($qb, $timeSlots);

        $lockQb = $this->getEntityManager()->createQueryBuilder();
        $lockQb->from('App\Entity\PlanningPositionLock', 'lock');
        $lockQb->select('lock');
        $lockQb->andWhere('lock.date = p.date');
        $lockQb->andWhere('lock.site = p.site');
        $lockQb->andWhere('lock.verrou2 = 1');

        $qb->andWhere(
            $qb->expr()->not(
                $qb->expr()->exists($lockQb->getDql())
            )
        );

        return $qb->getQuery()->getSingleColumnResult();
    }
}
