<?php

use App\Model\Agent;
require_once __DIR__."/../../conges/class.conges.php";


class AgentsPlanning {

    private $availables = array();

    private $date, $start, $end;

    public function __construct($date, $start, $end) {
        $this->date = $date;
        $this->start = $start;
        $this->end = $end;
        $entityManager = $GLOBALS['entityManager'];
        $this->availables = $entityManager->getRepository(Agent::class)->findAll();
    }

    public function getAvailables() {
        return $this->availables;
    }

    public function getNames() {
        $names = array_map(function ($agent) {
            return $agent->nom() . " " . $agent->prenom();
        }, $this->availables);
        return $names;
    }

    // Removes workers that are not available for any reason
    public function removeForAnyReason($start, $end) {
        $this->removeExcluded();
        $this->removeForTimes($start, $end);
        $this->removeForAbsences(true);
        $this->removeForHolidays(true);
        $this->removeForOccupied();
    }

    // Removes workers that are excluded by default ("admin admin" and "Tout le monde")
    public function removeExcluded() {
        $this->removeById(1); // Removes "admin admin"
        $this->removeById(2); // Removes "Tout le monde"
    }

    // Removes workers that are unavailable during this period
    public function removeForTimes($start, $end) {
        $db=new db();
        $config = $GLOBALS['config'];
        $d=new datePl($this->date);
        if ($config['PlanningHebdo']) {
            $p=new planningHebdo();
            $p->debut=$this->date;
            $p->fin=$this->date;
            $p->valide=true;
            $p->fetch();

            $tempsPlanningHebdo=array();

            if (!empty($p->elements)) {
                foreach ($p->elements as $elem) {
                    $tempsPlanningHebdo[$elem["perso_id"]]=$elem["temps"];
                }
            }
        }

        foreach ($this->availables as $agent) {
            if ($config['PlanningHebdo']) {
                if (array_key_exists($agent->id(), $tempsPlanningHebdo)) {
                    $temps=$tempsPlanningHebdo[$agent->id()];
                }
            } else {
                // Emploi du temps récupéré à partir de la table personnel
                $temps=json_decode(html_entity_decode($agent->temps(), ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            }
            $day = $d->planning_day_index_for($agent->id());
            if (!calculSiPresent($start, $end, $temps, $day)) {
                $this->removeById($agent->id());
            }
        }
    }


    // Removes workers that are already at work during this period
    public function removeForOccupied() {
        $db=new db();
        $dbprefix = $GLOBALS['config']['dbprefix'];
        $req="SELECT `{$dbprefix}pl_poste`.`perso_id` AS `perso_id` FROM `{$dbprefix}pl_poste` "
        ."INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` "
        ."WHERE `{$dbprefix}pl_poste`.`debut`<'$this->end' AND `{$dbprefix}pl_poste`.`fin`>'$this->start' "
            ."AND `{$dbprefix}pl_poste`.`date`='$this->date' AND `{$dbprefix}postes`.`bloquant`='1'";

        $db->query($req);
        if ($db->result) {
            foreach ($db->result as $elem) {
                $this->removeById($elem['perso_id']);
            }
        }

    }

/*
    // Removes workers that don't have the necessary skills
    public function removeForSkills($poste) {
        $db=new db();
        foreach ($availables as $agent) {
            $db->select2("postes", null, array("id"=>$poste)); 
            $activites = json_decode(html_entity_decode($db->result[0]['activites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
             if (is_array($activites)) {
                $postes = json_decode(html_entity_decode($elem['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                if (!is_array($postes)) {
                    $exclusion[$elem['id']][] = 'activites';
                } else {
                    foreach ($activites as $a) {
                        if (!in_array($a, $postes)) {
                            $exclusion[$elem['id']][] = 'activites';
                            break;
                        }
                    }
                }
            }
        }
    }
*/

    public function removeForAbsences($valid = false) {
        $db=new db();
        $db->select('absences', 'perso_id,valide', "`debut`<'$this->date $this->end' AND `fin` >'$this->date $this->start'");

        if ($db->result) {
            error_log("result");
            foreach ($db->result as $elem) {
                error_log(print_r($elem, 1));
                if ($elem['valide'] == 0 || ($elem['valide'] > 0 && $valid == true)) {
                    $this->removeById($elem['perso_id']);
                }
            }
        }
    }


    public function removeForHolidays($valid = false) {
        $config = $GLOBALS['config'];
        if (!$config['Conges-Enable']) { return; }
        $c=new conges();
        $c->debut="$this->date $this->start";
        $c->fin="$this->date $this->end";
        $c->valide=$valid;
        $c->supprime = false;
        $c->information = false;
        $c->bornesExclues=true;
        $c->fetch();

        foreach ($c->elements as $elem) {
            if ($elem['valide'] == 0 || ($elem['valide'] > 0 && $valid == true)) {
                $this->removeById($elem['perso_id']);
            }
        }
    }

    // Removes a worker from the availables list
    private function removeById($id) {
        if (!$id) { return; }
        $this->availables = array_filter($this->availables, function($agent, $k) use($id) {
            return (!($agent->id() == $id));
        }, ARRAY_FILTER_USE_BOTH);
    }
}
