<?php

namespace App\PlanningBiblio;

class PresentSet
{
    public $date;
    public $date_planning;
    public $absents = array();
    private $db;

    // Fix me. You shoud find another way to get db object.
    function __construct($date, $date_planning, $absents, $db, $site = 0)
    {
        $this->date = $date;
        $this->date_planning = $date_planning;
        $this->absents = $absents;
        $this->db = $db;
        $this->site = $site;
    }

    public function all()
    {
        $config = $GLOBALS['config'];
        $version = $GLOBALS['version'];
        $date = $this->date;
        $date_planning = $this->date_planning;
        $semaine = $date_planning->semaine;
        $semaine3 = $date_planning->semaine3;
        $absents = $this->absents;

        $this->db->select("personnel", "*", "`actif` LIKE 'Actif' AND (`depart` >= $date OR `depart` = '0000-00-00')", "ORDER BY `nom`,`prenom`");

        if ($config['PlanningHebdo']) {
            $tempsPlanningHebdo = self::getPlanningHebdo($date);
        }

        $presents = array();
        foreach ($this->db->result as $elem) {
            // Exclude agents who are not working on the request site
            if ($config['Multisites-nombre'] > 1 and $this->site != 0 ) {
                $agentSites = json_decode($elem['sites']);
                if (!is_array($agentSites) or !in_array($this->site, $agentSites)) {
                    continue;
                }
            }

            $heures = null;
            $temps = array();
            $week_number = 0;

            if ($config['PlanningHebdo']) {
                if (array_key_exists($elem['id'], $tempsPlanningHebdo)) {
                    $temps = $tempsPlanningHebdo[$elem['id']]['temps'];
                    $week_number = $tempsPlanningHebdo[$elem['id']]['nb_semaine'];
                }
            } else {
                $temps = json_decode(html_entity_decode($elem['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            }

            $jour = $date_planning->planning_day_index_for($elem['id'], $week_number);

            // Si l'emploi du temps est renseigné
            if (!empty($temps) and array_key_exists($jour, $temps)) {
                // S'il y a une heure de début (matin ou midi)
                if ($temps[$jour][0] or $temps[$jour][2]) {
                    $heures=$temps[$jour];
                }
            }

            // S'il y a des horaires correctement renseignés
            $siteAgent=null;
            if ($heures and !in_array($elem['id'], $absents)) {
                if ($config['Multisites-nombre']>1) {
                    if (!empty($heures[4])) {
                        if ($heures[4] == -1) {
                            $siteAgent = "Tout site";
                        } else {
                            $siteAgent=$config['Multisites-site'.$heures[4]];
                        }
                    }
                }
                $siteAgent=$siteAgent?$siteAgent.", ":null;

                $horaires=null;
                if (!$heures[1] and !$heures[2]) {		// Pas de pause le midi
                    $horaires=heure2($heures[0])." - ".heure2($heures[3]);
                } elseif (!$heures[2] and !$heures[3]) {	// matin seulement
                    $horaires=heure2($heures[0])." - ".heure2($heures[1]);
                } elseif (!$heures[0] and !$heures[1]) {	// après midi seulement
                    $horaires=heure2($heures[2])." - ".heure2($heures[3]);
                } else {		// matin et après midi avec pause
                    $horaires=heure2($heures[0])." - ".heure2($heures[1])." & ".heure2($heures[2])." - ".heure2($heures[3]);
                }
                $presents[]=array("id"=>$elem['id'],"nom"=>$elem['nom']." ".$elem['prenom'],"site"=>$siteAgent,"heures"=>$horaires);
            }
        }

        return $presents;
    }

    private static function getPlanningHebdo($date)
    {
        // if module PlanningHebdo: search related plannings.
        require_once __DIR__ . '/../../public/planningHebdo/class.planningHebdo.php';

        $p = new \planningHebdo();
        $p->debut = $date;
        $p->fin = $date;
        $p->valide = true;
        $p->fetch();

        $tempsPlanningHebdo = array();

        if (!empty($p->elements)) {
            foreach ($p->elements as $elem) {
                $tempsPlanningHebdo[$elem["perso_id"]] = $elem;
            }
        }

        return $tempsPlanningHebdo;
    }
}

