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

        try{
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }
    }
}
