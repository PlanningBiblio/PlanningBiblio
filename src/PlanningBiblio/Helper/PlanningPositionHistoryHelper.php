<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;
use App\Model\PlanningPosition;
use App\Model\PlanningPositionHistory;

class PlanningPositionHistoryHelper extends BaseHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    public function add($date, $beginning, $end, $site, $position, $login_id, $perso_id, $play_before = false)
    {
        $action = $this->save('add', $date, $beginning, $end, $site, $position, $login_id, array($perso_id));

        // There was an action before (i.e cross)
        if ($play_before) {
            $action->play_before(1);
            $this->entityManager->persist($action);
            $this->entityManager->flush();
        }
    }

    public function disable($date, $beginning, $end, $site, $position, $login_id, $perso_id_origine)
    {
        $action = $this->save('disable', $date, $beginning, $end, $site, $position, $login_id, array($perso_id_origine));

        // There was an agent in the disabled cell.
        // So map this action with the previous (delete)
        // built in ajax.updateCell.php.
        if ($perso_id_origine) {
            $action->play_before(1);
            $this->entityManager->persist($action);
            $this->entityManager->flush();
        }
    }

    public function put($date, $beginning, $end, $site, $position, $login_id, $perso_id)
    {
        //FIXME check here if we add an agent
        // in an empty cell or if replace an
        // existing agent. If we replace, we
        // must create a double action (play_before).

        $this->save('put', $date, $beginning, $end, $site, $position, $login_id, array($perso_id));
    }

    public function cross($date, $beginning, $end, $site, $position, $login_id, $perso_id = null)
    {
        // Select agents who are not crossed before
        $perso_ids = array();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.perso_id')
            ->from(PlanningPosition::class, 'p')
            ->where("p.date = '$date'")
            ->andwhere("p.debut = '$beginning'")
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
                if (in_array($perso_id, $perso_ids)) {
                    $perso_ids = array(intval($perso_id));
                } else {
                    $perso_ids = array();
                }
            }

            if (!empty($perso_ids)) {
                $this->save('cross', $date, $beginning, $end, $site, $position, $login_id, $perso_ids);
            }
        }
    }

    public function delete($date, $beginning, $end, $site, $position, $login_id, $perso_id = null)
    {
        // Select agents who are in the cell before
        $perso_ids = array();
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p.perso_id')
            ->from(PlanningPosition::class, 'p')
            ->where("p.date = '$date'")
            ->andwhere("p.debut = '$beginning'")
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
                if (in_array($perso_id, $perso_ids)) {
                    $perso_ids = array(intval($perso_id));
                } else {
                    $perso_ids = array();
                }
            }

            if (!empty($perso_ids)) {
                $this->save('delete', $date, $beginning, $end, $site, $position, $login_id, $perso_ids);
            }
        }
    }

    public function delete_plannings($start, $end, $site, $reason = 'delete-planning')
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
                $_SESSION['login_id'],
                array()
            );

            $this->entityManager->getRepository(PlanningPositionHistory::class)
                 ->archive($from->format('Y-m-d'), $site);

            $from->add($interval);
        }
    }

    private function save($action, $date, $beginning, $end, $site, $position, $login_id, $perso_ids) {

        // Format data
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $beginning = \DateTime::createFromFormat('H:i:s', $beginning);
        $end = \DateTime::createFromFormat('H:i:s', $end);

        $history = new PlanningPositionHistory;
        $history->perso_ids($perso_ids);
        $history->date($date);
        $history->beginning($beginning);
        $history->end($end);
        $history->site($site);
        $history->position($position);
        $history->action($action);
        $history->updated_by($login_id);
        $history->updated_at(new \DateTime());

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
