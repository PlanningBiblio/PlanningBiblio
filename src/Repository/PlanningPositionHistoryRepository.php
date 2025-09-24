<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

use App\Entity\PlanningPositionHistory;

class PlanningPositionHistoryRepository extends EntityRepository
{
    public function archive($date, $site, $nextOnly = false)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $entityManager = $this->getEntityManager();

        $filter = array('date' => $date, 'site' => $site, 'archive' => 0);

        if ($nextOnly) {
            $filter['undone'] = 1;
        }

        $history = $entityManager->getRepository(PlanningPositionHistory::class)
            ->findBy($filter);

        foreach ($history as $action) {
            $action->setArchive(true);
            $entityManager->persist($action);
        }

        $entityManager->flush();

        return $history;
    }

    public function undoable($date, $site)
    {
        if (!$date || !$site) {
            return array();
        }

        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();

        $history = $qb->select(array('h'))
            ->from(PlanningPositionHistory::class, 'h')
            ->where('h.date = :date')
            ->andWhere('h.site = :site')
            ->andWhere('h.undone = :undone')
            ->andWhere('h.archive = :archive')
            ->setParameter('date', $date)
            ->setParameter('site', $site)
            ->setParameter('undone', 0)
            ->setParameter('archive', 0)
            ->orderBy('h.id', 'DESC')
            ->getQuery()
            ->getArrayResult();

        // If the last change is not made by the logged in agent, undo is not allowed
        if (empty($history) || $history[0]['updated_by'] != $_SESSION['login_id']) {
            return array();
        }

        return $history;
    }

    public function redoable($date, $site)
    {
        if (!$date || !$site) {
            return array();
        }

        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();

        $history = $qb->select(array('h'))
            ->from(PlanningPositionHistory::class, 'h')
            ->where('h.date = :date')
            ->andWhere('h.site = :site')
            ->andWhere('h.undone = :undone')
            ->andWhere('h.archive = :archive')
            ->setParameter('date', $date)
            ->setParameter('site', $site)
            ->setParameter('undone', 1)
            ->setParameter('archive', 0)
            ->orderBy('h.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        // If the last undo is not made by the logged in agent, redo is not allowed
        if (empty($history) || $history[0]['updated_by'] != $_SESSION['login_id']) {
            return array();
        }

        return $history;
    }
}
