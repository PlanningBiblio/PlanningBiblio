<?php

namespace App\PlanningBiblio;

class PresentSet
{
    public $date;
    public $date_planning;
    public $absents = array();
    private $db;

    // Fix me. You shoud find another way to get db object.
    function __construct($date, $date_planning, $absents, $db)
    {
        $this->date = $date;
        $this->date_planning = $date_planning;
        $this->absents = $absents;
        $this->db = $db;
    }

    public function getBySite($site = null)
    {
        return $this->getPresentSet($site);
    }

    public function all()
    {
        return $this->getPresentSet();
    }

    private function getPresentSet($site = null)
    {
        $config = $GLOBALS['config'];
        $version = $GLOBALS['version'];
        $date = $this->date;
        $date_planning = $this->date_planning;
        $semaine = $date_planning->semaine;
        $semaine3 = $date_planning->semaine3;
        $absents = $this->absents;

        $this->db->select("personnel", "*", "`actif` LIKE 'Actif' AND (`depart` > $date OR `depart` = '0000-00-00')", "ORDER BY `nom`,`prenom`");

        // if module PlanningHebdo: search related plannings.
        if ($config['PlanningHebdo']) {
            include "planningHebdo/planning.php";
        }

        $presents = array();
        foreach ($this->db->result as $elem) {
            if ($site != null) {
                $sites = json_decode(html_entity_decode($elem['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                if (!is_array($sites) || !in_array($site, $sites)) { continue; }
            }

            $heures = null;

            $temps = array();

            if ($config['PlanningHebdo']) {
                if (array_key_exists($elem['id'], $tempsPlanningHebdo)) {
                    $temps = $tempsPlanningHebdo[$elem['id']];
                }
            } else {
                $temps = json_decode(html_entity_decode($elem['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            }

            $jour = $date_planning->position - 1;
            if ($jour == -1) {
                $jour = 6;
            }

            if ($config['nb_semaine'] == "2" and !($semaine%2)) {
                $jour+=7;
            }
            elseif ($config['nb_semaine']=="3") {
                if ($semaine3==2) {
                    $jour+=7;
                } elseif ($semaine3==3) {
                    $jour+=14;
                }
            }

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
                    if (isset($heures[4])) {
                        $siteAgent=$config['Multisites-site'.$heures[4]];
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
                    $horaires=heure2($heures[0])." - ".heure2($heures[1])." &amp; ".heure2($heures[2])." - ".heure2($heures[3]);
                }
                $presents[]=array("id"=>$elem['id'],"nom"=>$elem['nom']." ".$elem['prenom'],"site"=>$siteAgent,"heures"=>$horaires);
            }
        }

        return $presents;
    }
}

