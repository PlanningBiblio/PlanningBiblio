<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__.'/../../public/joursFeries/class.joursFeries.php');
require_once(__DIR__.'/../../public/personnel/class.personnel.php');

class CalendarController extends BaseController
{
    /**
     * @Route("calendar", name = "calendar.index", methods={"GET"})
     */
    public function index(Request $request, Session $session){
        $debut = $request->get('debut');
        $fin = $request->get('fin');

        if (!array_key_exists('agenda_debut', $_SESSION)){
            $_SESSION['agenda_debut'] = null;
            $_SESSION['agenda_fin'] = null;
            $_SESSION['agenda_perso_id'] = $_SESSION['login_id'];
        }

        $debut = $debut ? $debut : $_SESSION['agenda_debut'];
        $fin = $fin ? $fin : $_SESSION['agenda_fin'];

        $admin = in_array(3, $GLOBALS['droits'])?true:false;
        if($admin){
            $perso_id = $request->get('perso_id');
            $perso_id = $perso_id?$perso_id:$_SESSION['agenda_perso_id'];
        } else {
            $perso_id = $_SESSION['login_id'];
        }

        $d= new \datePl(date("Y-m-d"));
        $debutSQL = $debut ? dateSQL($debut) : $d->dates[0]; //lundi de la semaine courante
        $debut = dateFr3($debutSQL);
        $finSQL = $fin ? dateSQL($fin) : $d->dates[6]; //lundi de la semaine courante
        $fin = dateFr3($finSQL);
        $_SESSION['agenda_debut'] = $debut;
        $_SESSION['agenda_fin'] = $fin;
        $_SESSION['agenda_perso_id'] = $perso_id;
        $class = null;
        $nonValides = $this->config('Agenda-Plannings-Non-Valides');

        //PlanningHebdo et EDTSamedi étant incompatibles, EDTSamedi est désactivé si PlanningHebdo est activé
        if($this->config('PlanningHebdo')){
            $this->config('EDTSamedi',0);
        }

        //Sélection du personnel pour le menu déroulant

        $agent = null;
        $db = new \db();
        $db->query("SELECT * FROM `{$GLOBALS['dbprefix']}personnel` WHERE actif='Actif' $toutlemonde ORDER by `nom`,`prenom`;");
        $agents = $db->result;

        if(is_array($agents)){
            foreach ($agents as $elem){
                if($elem['id'] == $perso_id){
                    $agent = $elem['nom']." ".$elem['prenom'];
                    break;
                }
            }
        }

        // Jours fériés
        $j = new \joursFeries();
        $j->debut=$debutSQL;
        $j->fin=$finSQL;
        $j->index= "date";
        $j->fetch();
        $joursFeries=$j->elements;

        //Sélection des horaires de travail
        $db = new \db();
        $db->select2("personnel","temps", array("id"=> $perso_id));
        $temps=json_decode(html_entity_decode($db->result[0]['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true); //$temps = emploi du temps

        // Sélection des absences
        $filter = $this->config('Absences-validation')?"AND `valide`>0":null;
        $db = new \db();
        $db->select("absences",null, "`perso_id`='$perso_id' $filter");
        $absences = $db->result; //absences = tableau d'absences

        //Plannings verrouillés
        $verrou = array();
        $nbSites = $this->config('Multisites-nombre');
        for ($i = 1; $i <= $nbSites; $i++){
            $verrou[$i]=array();
        }

        $db = new \db();
        $db->select2("absences", null, "`perso_id`=$perso_id' $filter");
        if ($db->result){
            foreach ($db->result as $elem){
                $verrou[$elem('site')][] = $elem['date'];
            }
        }

        //Sélection des postes occupés
        $db = new \db();
        $db->selectInnerJoin(
            array("pl_poste", "poste"),
            array("postes", "id"),
            array("date","debut","fin","absent","site"),
            array(array("name"=>"nom", "as"=>"poste")),
            array("perso_id"=>$perso_id, "date"=>"BETWEEN $debutSQL AND $finSQL"),
            array(),
            "ORDER BY date, debut, fin, site, poste"
        );
        $postes = $db->result;

        // Affiche des cellules vides devant le premier jour demandé de façon à avoir les lundis dans la première colonne
        $d = new \datePl($debutSQL);
        $cellsBefore = $d->position>0?$d->position-1:6;

        $nb = $cellsBefore;
        $current = $debutSQL;
        $days = array();
        while ($current <= $finSQL) {
            $current_postes = array();
            $date_tab = explode("-", $current);
            $date_aff = dateAlpha($current, false, false);
            $jour = date("w", strtotime($current))-1;
            $d = new \datePl($current);
            $semaine = $d->semaine;
            $j1 = $d->dates[0];

            if ($jour < 0) {
                $jour = 6;
            }

            // Si utilisation de 2 ou 3 plannings hebdo, hors EDTSamedi
            if (!$this->config('EDTSamedi')) {
                if ($d->semaine3 == 2) {
                    $jour = $jour+7;
                } elseif ($d->semaine3 == 3) {
                    $jour = $jour+14;
                }
            }

            // Si utilisation d'un planning pour les semaines sans samedi et un planning pour les semaines avec samedi travaillé
            if ($this->config('EDTSamedi')) {
                // Pour chaque agent, recherche si la semaine courante est avec samedi travaillé ou non
                $p = new \personnel();
                $p->fetchEDTSamedi($perso_id, $j1, $j1);
                $jour += $p->offset;
            }

            // Horaires de travail si le module PlanningHebdo est activé
            if ($this->config('PlanningHebdo')) {
                include_once __DIR__."/../../public/planningHebdo/class.planningHebdo.php";
                $p = new \planningHebdo();
                $p->perso_id = $perso_id;
                $p->debut = $current;
                $p->fin = $current;
                $p->valide = true;
                $p->fetch();
                if (empty($p->elements)) {
                    $temps = array();
                } else {
                    $temps = $p->elements[0]['temps'];
                }
            }
            $horaires = null;
            if (is_array($temps) and array_key_exists($jour, $temps)) {
                $horaires = $temps[$jour];
            }

            $current_date = ucfirst($d->jour_complet);
            if (is_array($postes)) {
                foreach ($postes as $elem) {

                    if ($elem['date'] == $current and (in_array($current, $verrou[$elem['site']]) or $nonValides)) {
                        // Contrôle des absences depuis la table absence
                        if (is_array($absences)) {
                            foreach ($absences as $a) {
                                if ($a['debut'] < $elem['date'].' '.$elem['fin'] and $a['fin'] > $elem['date'].' '.$elem['debut']) {
                                    $elem['absent'] = 1;
                                    break;
                                }
                            }
                        }
                        $current_postes[] = $elem;
                    }
                }
            }

            $current_abs = array();
            if (is_array($absences)) {
                foreach ($absences as $elem) {
                    $abs_deb = substr($elem['debut'], 0, 10);
                    $abs_fin = substr($elem['fin'], 0, 10);
                          if (($abs_deb < $current and $abs_fin>$current) or $abs_deb==$current or $abs_fin==$current) {
                            $current_abs[] = $elem;
                         }
                    }
            }
            // Jours fériés : affiche Bibliothèque fermée et passe au jour suivant
            if (array_key_exists($current, $joursFeries) and $joursFeries[$current]['fermeture']) {
                $current = date("Y-m-d", mktime(0, 0, 0, $date_tab[1], $date_tab[2]+1, $date_tab[0]));
                $closed = true;
                $nom = $joursFeries[$current]['nom'];
            }

            // Si l'agent est absent : affiche s'il est absent toute la journée ou ses heures d'absence
            $absent = false;
            $absences_affichage = array();

            foreach ($current_abs as $elem) {
                if ($elem['debut'] <= $current." 00:00:00" and $elem['fin'] >= $current." 23:59:59") {
                    $absent = true;
                    $absences_affichage[] = "Toute la journée : ".$elem['motif'];
                } elseif (substr($elem['debut'], 0, 10) == $current and substr($elem['fin'], 0, 10)==$current) {
                    $deb = heure2(substr($elem['debut'], -8));
                    $fi = heure2(substr($elem['fin'], -8));
                    $absences_affichage[] = "De $deb &agrave; $fi : ".$elem['motif'];
                } elseif (substr($elem['debut'], 0, 10) == $current and $elem['fin'] >= $current." 23:59:59") {
                    $deb = heure2(substr($elem['debut'], -8));
                    $absences_affichage[]="&Agrave; partir de $deb : ".$elem['motif'];
                } elseif ($elem['debut'] <= $current." 00:00:00" and substr($elem['fin'], 0, 10)==$current) {
                    $fi = heure2(substr($elem['fin'], -8));
                    $absences_affichage[] = "Jusqu'&agrave; $fi : ".$elem['motif'];
                } else {
                    $absences_affichage[] = "{$elem['debut']} &rarr; {$elem['fin']} : {$elem['motif']}";
                }
            }
             // Intégration des congés
            if ($this->config('Conges-Enable')) {
                include_once __DIR__."/../../public/conges/class.conges.php";
                $c=new \conges();
                $c->perso_id = $perso_id;
                $c->debut = $current." 00:00:00";
                $c->fin = $current." 23:59:59";
                $c->valide = true;
                $c->fetch();
                $conges_affichage = array();

                if (!empty($c->elements)) {
                    for ($i = 0;$i < count($c->elements); $i++) {
                        $conge = $c->elements[$i];
                        // Si en congé toute la journée, n'affiche pas les horaires de présence habituels et les absences enregistrées
                        // (remplace le message d'absence)
                        if ($conge['debut'] <= $current." 00:00:00" and $conge['fin'] >= $current." 23:59:59") {
                            $absent = true;
                            $conges_affichage[] = "Toute la journ&eacute;e : Cong&eacute;";
                        } elseif (substr($conge['debut'], 0, 10) == $current and substr($conge['fin'], 0, 10)==$current) {
                            $deb = heure2(substr($conge['debut'], -8));
                            $fi = heure2(substr($conge['fin'], -8));
                            $conges_affichage[] = "De $deb &agrave; $fi : Cong&eacute;";
                        } elseif (substr($conge['debut'], 0, 10) == $current and $conge['fin'] >= $current." 23:59:59") {
                            $deb = heure2(substr($conge['debut'], -8));
                            $conges_affichage[] = "&Agrave; partir de $deb : Cong&eacute;";
                        } elseif ($conge['debut'] <= $current." 00:00:00" and substr($conge['fin'], 0, 10) == $current) {
                            $fi = heure2(substr($conge['fin'], -8));
                            $conges_affichage[] = "Jusqu'&agrave; $fi : Cong&eacute;";
                        } else {
                            $conges_affichage[] = "{$conge['debut']} &rarr; {$conge['fin']} : Cong&eacute;";
                        }
                        // Modifie l'index "absent" du tableau $current_postes pour barrer les postes concernés par le congé
                        for ($j = 0; $j < count($current_postes); $j++) {
                            if ($current." ".$current_postes[$j]['debut'] < $conge['fin'] and $current." ".$current_postes[$j]['fin'] > $conge['debut']) {
                                $current_postes[$j]['absent'] = 1;
                            }
                        }
                    }
                }
                // Si congé sur une partie de la journée seulement, complète le message d'absence
                if (!empty($conges_affichage)) {
                    $absences_affichage = array_merge($absences_affichage, $conges_affichage);
                }
            }
            // Si l'agent n'est pas absent toute la journée : affiche ses heures de présences
            $presence = array();
            if (!$absent) {
                $site = $this->config('Multisites-site1');
                if ($nbSites > 1 and isset($horaires[4])) {
                    if ($horaires[4]) {
                        $site = $this->config('Multisites-site'.$horaires[4]);
                    }
                }
                $schedule = array();
                if ($horaires[0] and $horaires[1]){
                    $schedule[] = array(
                        'begin' => heure2($horaires[0]),
                        'end'=> heure2($horaires[1])
                    );
                } elseif ($horaires[0] and $horaires[5]){
                    $schedule[] = array(
                        'begin' => heure2($horaires[0]),
                        'end'=> heure2($horaires[5])
                    );
                } elseif ($horaires[0] and $horaires[3]){
                    $schedule[] = array(
                        'begin' => heure2($horaires[0]),
                        'end'=> heure2($horaires[3])
                    );
                }

                if ($horaires[2] and $horaires[5]){
                    $schedule[] = array(
                        'begin' => heure2($horaires[2]),
                        'end'=> heure2($horaires[5])
                    );
                } elseif ($horaires[2] and $horaires[3]){
                    $schedule[] = array(
                        'begin' => heure2($horaires[3]),
                        'end'=> heure2($horaires[3])
                    );
                }

                if ($horaires[6] and $horaires[3]){
                    $schedule[] = array(
                        'begin' => heure2($horaires[6]),
                        'end'=> heure2($horaires[3])
                    );
                }


                if (!empty($schedule)){
                    $presence = array(
                    "site"=> $site,
                    "schedule" => $schedule
                    );
                }
            }
            // Affichage des absences
            if (!empty($absences_affichage)) {
                $nbAbs = count($absences_affichage);
            }

            $positions = array();
            if (!empty($current_postes)) {
            //Regroupe les horaires des mêmes postes
                $tmp = array();
                $j = 0;
                for ($i = 0; $i < count($current_postes); $i++) {
                    $current_postes[$i]['absent'] == true ? true : false;
                    if ($i == 0) {
                        $tmp[$j] = $current_postes[$i];
                    } else {
                        if ($current_postes[$i]['site'] == $tmp[$j]['site'] and $current_postes[$i]['poste'] == $tmp[$j]['poste'] and $current_postes[$i]['absent'] == $tmp[$j]['absent'] and $current_postes[$i]['debut'] == $tmp[$j]['fin']){
                            $tmp[$j]['fin'] = $current_postes[$i]['fin'];
                        } else {
                            $j++;
                            $tmp[$j] = $current_postes[$i];
                        }
                    }

                }

                $current_postes = $tmp;
                foreach ($current_postes as $elem) {
                    $pos_name = html_entity_decode($elem['poste'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
                    $pos_absent = $elem['absent'];
                    $heure = heure2($elem['debut'])." - ".heure2($elem['fin']);
                    $positions[] = array(
                        "name" => $pos_name,
                        "absent" => $pos_absent,
                        "hour" => $heure
                    );
                }
            }

            $days[] = array(
                "aff" => $date_aff,
                "closed" => $closed,
                "name" => html_entity_decode($nom, ENT_QUOTES|RNT_IGNORE, 'UTF-8'),
                "presence" => $presence,
                "absence" => $absences_affichage,
                "position" => $positions,
                "nb" => $nb
            );
            $nb++;
            $current=date("Y-m-d", mktime(0, 0, 0, $date_tab[1], $date_tab[2]+1, $date_tab[0]));

        }
        //Cellules vides à la fin pour aller jusqu'au dimanche
        $d=new \datePl($finSQL);
        $cellsAfter = $d->position>0?7-$d->position:0;

        $this->templateParams(array(
            "admin" => $admin,
            "agent" => $agent,
            "agents" => $agents,
            "begin" => $debut,
            "beginSQL" => $debutSQL,
            "cellsAfter" => $cellsAfter,
            "cellsBefore" => $cellsBefore,
            "days" => $days,
            "end" => $fin,
            "endSQL" => $finSQL,
            "nbSites" => $nbSites,
            "perso_id" => $perso_id
        ));

        return $this->output('calendar/index.html.twig');
    }
}

?>
