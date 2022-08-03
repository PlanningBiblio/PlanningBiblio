<?php

namespace App\PlanningBiblio;

require_once(__DIR__ . "/../../public/include/feries.php");

class ClosingDay
{
    public $annee=null;
    public $debut=null;
    public $fin=null;
    public $auto=null;
    public $elements=array();
    public $error=false;
    public $index=null;
    public $CSRFToken = null;

    public function __construct()
    {
    }

    public function fetch()
    {
        $tab=array();
        $annees=array();

        if ($this->annee) {
            // Initilisation des dates de début et de fin
            $this->debut=substr($this->annee, 0, 4)."-09-01";
            $this->fin=(substr($this->annee, 0, 4)+1)."-08-31";
            $annees[]=$this->annee;
        } else {
            $first=date("m", strtotime($this->debut))<9?date("Y", strtotime($this->debut))-1:date("Y", strtotime($this->debut));
            $last=date("m", strtotime($this->fin))<9?date("Y", strtotime($this->fin))-1:date("Y", strtotime($this->fin));
            for ($year=$first;$year<=$last;$year++) {
                $annees[]=$year."-".($year+1);
            }
        }

        // Recherche des jours fériés enregistrés dans la base de données
        $annees=implode("','", $annees);
        $db=new \db();
        $db->sanitize_string = false;
        $db->select("jours_feries", "*", "annee in ('$annees')", "ORDER BY `jour`");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $tab[$elem['jour']]=$elem;
            }
        }

        if (empty($tab) or $this->auto) {
            $tmp=array();
            foreach ($tab as $elem) {
                $tmp[]=$elem['jour'];
            }

            // Recherche des jours fériés avec la fonction "jour_ferie"
            for ($date=$this->debut;$date<$this->fin;$date=date("Y-m-d", strtotime("+1 day", strtotime($date)))) {
                if (jour_ferie($date)) {
                    if (!in_array($date, $tmp)) {
                        $line = array(
                            "jour" => $date,
                            "ferie" => 1,
                            "fermeture" => 0,
                            "nom" => jour_ferie($date),
                            "commentaire" => "Ajouté automatiquement"
                        );
                        if ($this->index and $this->index=="date") {
                            $tab[$date]=$line;
                        } else {
                            $tab[]=$line;
                        }
                    }
                }
            }
        }
        uasort($tab, "cmp_jour");
        $this->elements=$tab;
    }

    public function fetchByDate($date)
    {
        // Recherche du jour férié correspondant à la date $date
        $tab=array();
        $db=new \db();
        $db->select("jours_feries", "*", "jour='$date'");
        if ($db->result) {
            $tab=$db->result;
        }
        $this->elements=$tab;
    }

    public function fetchYears()
    {
        $db=new \db();
        $db->select("jours_feries", "annee", null, "GROUP BY `annee` desc");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $this->elements[]=$elem['annee'];
            }
        }
    }

    public function update($p)
    {
        $error=false;
        $data=array();
        $keys=array_keys($p['jour']);
        foreach ($keys as $elem) {
            if ($p['jour'][$elem] and $p['jour'][$elem]!="0000-00-00") {
                $ferie=isset($p['ferie'][$elem])?1:0;
                $fermeture=isset($p['fermeture'][$elem])?1:0;
                $data[]=array("annee"=>$p['annee'],"jour"=>dateSQL($p['jour'][$elem]),"ferie"=>$ferie,"fermeture"=>$fermeture,"nom"=>$p['nom'][$elem],"commentaire"=>$p['commentaire'][$elem]);
            }
        }
        $db=new \db();
        $db->CSRFToken = $this->CSRFToken;
        $db->delete("jours_feries", array('annee' => $p['annee']));
        $error=$db->error?true:$error;

        if (!empty($data)) {
            $db=new \db();
            $db->CSRFToken = $this->CSRFToken;
            $db->insert("jours_feries", $data);
            $error=$db->error?true:$error;
        }
        $this->error=$error;
    }
}
