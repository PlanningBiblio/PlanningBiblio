<?php

namespace App\Planno\Helper;

use App\Planno\Helper\BaseHelper;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionHistory;

class PlanningPositionHistoryHelper extends BaseHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function add($date, $start, $end, $site, $position, $login_id, $perso_id, $playBefore = false): void
    {
        $action = $this->save('add', $date, $start, $end, $site, $position, $login_id, array($perso_id));

        // There was an action before (i.e cross)
        if ($playBefore) {
            $action->setPlayBefore(true);
            $this->entityManager->persist($action);
            $this->entityManager->flush();
        }
    }

    public function disable($date, $start, $end, $site, $position, $login_id, $perso_id_origine): void
    {
        $action = $this->save('disable', $date, $start, $end, $site, $position, $login_id, array($perso_id_origine));

        // There was an agent in the disabled cell.
        // So map this action with the previous (delete)
        // built in ajax.updateCell.php.
        if ($perso_id_origine) {
            $action->setPlayBefore(true);
            $this->entityManager->persist($action);
            $this->entityManager->flush();
        }
    }

    public function put($date, $start, $end, $site, $position, $login_id, $perso_id, $perso_id_origin = 0): void
    {
        // Check from DB if there is deletion before because $perso_id_origin is not reset when the original cell is empty
        // (perso_id_origin may be != 0 even if the cell is empty).
        $before = $this->entityManager->getRepository(PlanningPosition::class)->findOneBy([
            'date' => \DateTime::createFromFormat('Y-m-d', $date),
            'site' => $site,
            'debut' => \DateTime::createFromFormat('H:i:s', $start),
            'fin' => \DateTime::createFromFormat('H:i:s', $end),
            'poste' => $position,
        ]);

        $deleteBefore = (bool) $before;

        if ($deleteBefore) {
            $this->delete($date, $start, $end, $site, $position, $login_id, $perso_id_origin);
        }

        $this->save('put', $date, $start, $end, $site, $position, $login_id, array($perso_id), $deleteBefore);
    }

    public function cross($date, $start, $end, $site, $position, $login_id, $perso_id = null): void
    {
        // Select agents who are not crossed before
        $perso_ids = array();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.perso_id')
            ->from(PlanningPosition::class, 'p')
            ->where("p.date = '$date'")
            ->andwhere("p.debut = '$start'")
            ->andwhere("p.fin = '$end'")
            ->andwhere("p.site = $site")
            ->andwhere("p.poste = $position")
            ->andwhere("p.absent <> '1'");

        $res = $qb->getQuery();
        $result = $res->getResult();

        if (!empty($result)) {
            foreach($result as $elem) {
                $perso_ids[] = $elem['perso_id'];
            }

            if (!empty($perso_id)) {
                $perso_ids = in_array($perso_id, $perso_ids) ? array(intval($perso_id)) : array();
            }

            if (!empty($perso_ids)) {
                $this->save('cross', $date, $start, $end, $site, $position, $login_id, $perso_ids);
            }
        }
    }

    public function delete($date, $start, $end, $site, $position, $login_id, $perso_id = null): void
    {
        // Select agents who are in the cell before
        $perso_ids = array();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.perso_id')
            ->from(PlanningPosition::class, 'p')
            ->where("p.date = '$date'")
            ->andwhere("p.debut = '$start'")
            ->andwhere("p.fin = '$end'")
            ->andwhere("p.site = $site")
            ->andwhere("p.poste = $position");

        $res = $qb->getQuery();
        $result = $res->getResult();

        if (!empty($result)) {
            foreach($result as $elem) {
                $perso_ids[] = $elem['perso_id'];
            }

            if (!empty($perso_id)) {
                $perso_ids = in_array($perso_id, $perso_ids) ? array(intval($perso_id)) : array();
            }

            if (!empty($perso_ids)) {
                $this->save('delete', $date, $start, $end, $site, $position, $login_id, $perso_ids);
            }
        }
    }

    public function delete_plannings($session, $start, $end, $site, $reason = 'delete-planning'): void
    {
        $from = \DateTime::createFromFormat('Y-m-d', $start);
        $to = \DateTime::createFromFormat('Y-m-d', $end);

        $interval = \DateInterval::createfromdatestring('+1 day');

        while ($from->format('Y-m-d') <= $to->format('Y-m-d')) {
            $action = $this->save(
                $reason,
                $from->format('Y-m-d'),
                '00:00:00',
                '23:59:59',
                $site,
                0,
                $session->get('loginId'),
                array()
            );

            $this->entityManager->getRepository(PlanningPositionHistory::class)
                 ->archive($from->format('Y-m-d'), $site);

            $from->add($interval);
        }
    }

    private function save($action, $date, $start, $end, $site, $position, $login_id, $perso_ids, $playBefore = false): \App\Entity\PlanningPositionHistory
    {

        // Format data
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $start = \DateTime::createFromFormat('H:i:s', $start);
        $end = \DateTime::createFromFormat('H:i:s', $end);

        $history = new PlanningPositionHistory;
        $history->setUsers($perso_ids);
        $history->setDate($date);
        $history->setStart($start);
        $history->setEnd($end);
        $history->setSite($site);
        $history->setPosition($position);
        $history->setAction($action);
        $history->setPlayBefore($playBefore);
        $history->setUpdatedBy($login_id);
        $history->setUpdatedAt(new \DateTime());

        try{
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }

        return $history;
    }
}
