<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\PlanningBiblio\PresentSet;

$version = 'symfony';

include_once __DIR__ . "/../../public/conges/class.conges.php";
include_once __DIR__ . "/../../public/include/function.php";
require_once __DIR__ . "/../../public/include/db.php";
require_once __DIR__ . "/../../public/include/horaires.php";
include_once __DIR__ . '/../../public/statistiques/class.statistiques.php';
include_once __DIR__ . '/../../public/absences/class.absences.php';

class StatisticController extends BaseController
{
    /**
     * @Route("/statistics/saturday", name="statistics.saturday", methods={"GET", "POST"})
     */
    public function saturday (Request $request, Session $session)
    {
        // Initialisation des variables :
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $statistiques_heures = $request->get('statistiques_heures');
        $statistiques_heures_defaut = $request->get('statistiques_heures_defaut');
        $post = $request->request->all();

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $post_agents = isset($post['agents']) ? $post['agents'] : null;
        $post_sites = isset($post['selectedSites']) ? $post['selectedSites'] : null;

        $agent_tab = null;
        $exists_JF = false;
        $exists_absences = false;
        $exists_samedi = false;

        $nbSites = $this->config('Multisites-nombre');

        // Statistiques-Heures
        $heures_tab_global = array();
        if ($statistiques_heures_defaut) {
            $statistiques_heures = $this->config['Statistiques-Heures'];
        } else {
            if (!$statistiques_heures and !empty($_SESSION['oups']['statistiques_heures'])) {
                $statistiques_heures = $_SESSION['oups']['statistiques_heures'];
            } elseif (!$statistiques_heures and !empty($this->config['Statistiques-Heures'])) {
                $statistiques_heures = $this->config['Statistiques-Heures'];
            }
        }

        $_SESSION['oups']['statistiques_heures'] = $statistiques_heures;

        //		--------------		Initialisation  des variables 'debut','fin' et 'agents'		-------------------
        if (!$debut and array_key_exists('stat_debut', $_SESSION)) {
            $debut = $_SESSION['stat_debut'];
        }
        if (!$fin and array_key_exists('stat_fin', $_SESSION)) {
            $fin = $_SESSION['stat_fin'];
        }

        if (!$debut) {
            $debut = "01/01/".date("Y");
        }
        if (!$fin) {
            $fin = date("d/m/Y");
        }

        $_SESSION['stat_debut'] = $debut;
        $_SESSION['stat_fin'] = $fin;

        $debutSQL = dateFr($debut);
        $finSQL = dateFr($fin);

        // Sélection des samedis entre le début et la fin
        $dates = array();
        $d = new \datePl($debutSQL);
        $current = $debutSQL <= $d->dates[5] ? $d->dates[5] : date("Y-m-d", strtotime("+1 week", strtotime($d->dates[5])));
        while ($current <= $finSQL){
            $dates[] = $current;
            $current = date("Y-m-d", strtotime("+1 week", strtotime($current)));
        }
        $dates = join(",", $dates);

        // Les agents
        if (!array_key_exists('stat_samedis_agents', $_SESSION)) {
            $_SESSION['stat_samedis_agents'] = null;
        }

        $agents = array();
        if ($post_agents) {
            foreach ($post_agents as $elem) {
                $agents[] = $elem;
            }
        } else {
            $agents = $_SESSION['stat_samedis_agents'];
        }
        $_SESSION['stat_samedis_agents'] = $agents;

        // Filtre les sites
        if (!array_key_exists('stat_samedis_sites', $_SESSION)) {
            $_SESSION['stat_samedis_sites'] = array();
        }

        $selectedSites = array();
        if ($post_sites) {
            foreach ($post_sites as $elem) {
                $selectedSites[] = $elem;
            }
        } else {
            $selectedSites = $_SESSION['stat_samedis_sites'];
        }

        if ($nbSites > 1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }

        $_SESSION['stat_samedis_sites'] = $selectedSites;

        // Filtre les sites dans les requêtes SQL
        if ($nbSites>1) {
            $sitesSQL = "0,".join(",", $selectedSites);
        } else {
            $sitesSQL = "0,1";
        }

        //		--------------		Récupération de la liste des agents pour le menu déroulant		------------------------
        $db=new \db();
        $db->select2("personnel", "*", array("actif"=>"Actif"), "ORDER BY `nom`,`prenom`");
        $agents_list = $db->result;

        $tab = array();
        $nbJours = 0;
        if (!empty($agents) and $dates) {
            //	Recherche du nombre de jours concernés
            $db = new \db();
            $db->select2("pl_poste", "date", array("date"=>"IN{$dates}", "site"=>"IN{$sitesSQL}"), "GROUP BY `date`;");
            $nbJours = $db->nb;
            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB = $a->elements;

            //	Recherche des infos dans pl_poste et postes pour tous les agents sélectionnés
            //	On stock le tout dans le tableau $resultat
            $agents_select = join(",", $agents);

            $db = new \db();
            $db->selectInnerJoin(
                array("pl_poste","poste"),
                array("postes","id"),
                array("debut","fin","date","perso_id","poste","absent"),
                array(array("name"=>"nom","as"=>"poste_nom"),"etage","site"),
                array("date"=>"IN{$dates}", "supprime"=>"<>1", "perso_id"=>"IN{$agents_select}", "site"=>"IN{$sitesSQL}"),
                array("statistiques"=>"1"),
                "ORDER BY `poste_nom`,`etage`"
            );

            $resultat = $db->result;
  
            //	Recherche des infos dans le tableau $resultat (issu de pl_poste et postes) pour chaque agent sélectionné
            foreach ($agents as $agent) {
                if (array_key_exists($agent, $tab)) {
                    $heures = $tab[$agent][2];
                    $total_absences = $tab[$agent][5];
                    $samedi = $tab[$agent][3];
                    $dimanche = $tab[$agent][6];
                    $heures_tab = $tab[$agent][7];
                    $absences = $tab[$agent][4];
                    $feries = $tab[$agent][8];
                    $sites = $tab[$agent]["sites"];
                } else {
                    $heures = 0;
                    $total_absences = 0;
                    $samedi = array();
                    $dimanche = array();
                    $absences = array();
                    $heures_tab = array();
                    $feries = array();
                    $sites = array();
                    for ($i = 1; $i <= $nbSites; $i++) {
                        $sites[$i] = 0;
                    }
                }
                $postes = array();
                if (is_array($resultat)) {
                    foreach ($resultat as $elem) {
                        if ($agent == $elem['perso_id']) {
                            // Vérifie à partir de la table absences si l'agent est absent
                            // S'il est absent : continue
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {
                                foreach ($absencesDB[$elem['perso_id']] as $a) {
                                    if ($a['debut'] < $elem['date'].' '.$elem['fin'] and $a['fin'] > $elem['date']." ".$elem['debut']) {
                                        continue 2;
                                    }
                                }
                            }
                            if ($elem['absent'] != "1") { // on compte les heures et les samedis pour lesquels l'agent n'est pas absent
                                if (!array_key_exists($elem['date'], $samedi)) { // on stock les dates et la somme des heures faites par date
                                    $samedi[$elem['date']][0] = $elem['date'];
                                    $samedi[$elem['date']][1] = 0;
                                }
                                $samedi[$elem['date']][1]+=diff_heures($elem['debut'], $elem['fin'], "decimal");

                                if (jour_ferie($elem['date'])) {
                                    if (!array_key_exists($elem['date'], $feries)) {
                                        $feries[$elem['date']][0] = $elem['date'];
                                        $feries[$elem['date']][1] = 0;
                                    }
                                    $feries[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                    $exists_JF = true;
                                }

                                foreach ($agents_list as $elem2) {
                                    if ($elem2['id'] == $agent) {	// on créé un tableau avec le nom et le prénom de l'agent.
                                        $agent_tab = array($agent,$elem2['nom'],$elem2['prenom'],$elem2['recup']);
                                        break;
                                    }
                                }

                                // Statistiques-Heures
                                if ($statistiques_heures) {
                                    $statistiques_heures_tab = explode(';', $statistiques_heures);
                                    foreach ($statistiques_heures_tab as $h) {
                                        $tmp = heures($h);
                                        if (!$tmp) {
                                            continue;
                                        }
                        
                                        if ($elem['debut'] == $tmp[0] and $elem['fin'] == $tmp[1]) {
                                            $heures_tab[$tmp[0].'-'.$tmp[1]][] = $elem['date'];
                                            if (!in_array($tmp, $heures_tab_global)) {
                                                $heures_tab_global[] = $tmp;
                                            }
                                        }
                                    }
                                }
                            } else {				// On compte les absences
                                if (!array_key_exists($elem['date'], $absences)) {
                                    $absences[$elem['date']][0] = $elem['date'];
                                    $absences[$elem['date']][1] = 0;
                                }
                                $absences[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $total_absences += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $exists_absences = true;
                            }
                            // On met dans tab tous les éléments (infos postes + agents + heures)
                            $tab[$agent] = array(
                                $agent_tab,
                                $postes,
                                $heures,
                                $samedi,
                                $absences,
                                $total_absences,
                                $dimanche,
                                $heures_tab,
                                $feries,
                                "sites"=>$sites
                            );
                        }
                    }
                }
            }
        }

        sort($heures_tab_global);

        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;
        
        $multisites = array();
        if ($nbSites>1) {
            for ($i=1;$i<=$nbSites;$i++) {
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }
        // 		--------------------------		Affichage du tableau de résultat		--------------------
        if ($tab) {
           
            foreach ($heures_tab_global as &$v) {
                $v[2] = heure3($v[0]).'-'.heure3($v[1]);
            }

            foreach ($tab as &$elem) {
                // Calcul des moyennes
                $jour = $elem[2]/$nbJours;

                $heures = 0;
                foreach ($elem[3] as &$samedi) {
                    $heures += $samedi[1];
                }
                sort($elem[3]);				//	tri les samedis par dates croissantes

                $elem["heures"] = heure4($heures);
                
                foreach ($elem[3] as &$samedi) {			//	Affiche les dates et heures des samedis
                    $samedi[0] = dateFr($samedi[0]);			//	date
                    $samedi[1] = heure4($samedi[1]);	// heures
                }
                
                // Jours feriés
                if ($exists_JF) {
                    sort($elem[8]);				//	tri les dimanches par dates croissantes
                    foreach ($elem[8] as &$ferie) {		// 	Affiche les dates et heures des dimanches
                        $ferie[0] = dateFr($ferie[0]);			//	date
                        $ferie[1] = heure4($ferie[1]);	//	heures
                    }
                }

                // Absences
                if ($exists_absences) {
                    if ($elem[5]) {				//	Affichage du total d'heures d'absences
                        $elem[5] = heure4($elem[5]);
                    }
                    sort($elem[4]);				//	tri les absences par dates croissantes
                    foreach ($elem[4] as &$absences) {		//	Affiche les dates et heures des absences
                        $absences[0] = dateFr($absences[0]);			//	date
                        $absences[1] = heure4($absences[1]);
                    }
                }
            
                // Statistiques-Heures
                foreach ($heures_tab_global as &$v) {
                    if (!empty($elem[7][$v])) {
                        sort($elem[7][$v]);
                        foreach ($elem[7][$v] as &$h) {
                            $h = dateFr($h);
                        }
                    }
                }
            }
        }

        $this->templateParams(
            array(
                "agents"              => $agents,
                "agents_list"         => $agents_list,
                "debut"               => $debut,
                "exists_absences"     => $exists_absences,
                "exists_JF"           => $exists_JF,
                "fin"                 => $fin,
                "heures_tab_global"   => $heures_tab_global,
                "multisites"          => $multisites,
                "nbJours"             => $nbJours,
                "nbSites"             => $nbSites,
                "selectedSites"       => $selectedSites,
                "statistiques_heures" => $statistiques_heures,
                "tab"                 => $tab
            )
        );
        return $this->output('statistics/saturday.html.twig');
    }

    /**
     * @Route("/statistics/attendeesmissing", name="statistics.attendeesmissing", methods={"GET", "POST"})
     */
    public function attendeesmissing( Request $request, Session $session )
    {
        $params = $request->request->all();
        if ($request->get('reset')) {
            $params['reset'] = 1;
        }

        if (empty($params) and !empty($_SESSION['present_absent_from'])) {
            $params['from'] = $_SESSION['present_absent_from'];
            $params['to'] = $_SESSION['present_absent_to'];
        }

        if (empty($params) || isset($params['reset'])) {
            $params['from'] = date('d/m/Y');
            $params['to'] = date('d/m/Y');
        }

        $_SESSION['present_absent_from'] = $params['from'];
        $_SESSION['present_absent_to'] = $params['to'];

        $startTime = strtotime(dateSQL($params['from']));
        $endTime = strtotime(dateSQL($params['to']));

        $by_date = array();
        for ( $i = $startTime; $i <= $endTime; $i = $i + 86400 ) {
            $date = date('Y-m-d', $i);

            $conges = array();
            if ($this->config('Conges-Enable')) {
                $c = new \conges();
                $conges = $c->all($date.' 00:00:00', $date.' 23:59:59');
            }

            $absences = new \absences();
            $absences->valide = false;
            $absent_ids = array(2);
            $absences->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date, array(1));
            $absents = $absences->elements;

            foreach ($conges as $elem) {
                $elem['motif']="Congé payé";
                $absents[] = $elem;
            }

            foreach ($absents as $key => $absent) {
                $absents[$key]['motif'] = html_entity_decode($absent['motif'], ENT_QUOTES|ENT_HTML5);
                preg_match('/00:00:00$/', $absent['debut'], $matche_start, PREG_OFFSET_CAPTURE);
                preg_match('/23:59:59$/', $absent['fin'], $matche_end, PREG_OFFSET_CAPTURE);
                if ($matche_start && $matche_end) {
                    $absents[$key]['all_the_day'] = 1;
                } else {
                    $absents[$key]['from'] = substr($absent['debutAff'], -5);
                    $absents[$key]['to'] = substr($absent['finAff'], -5);
                    $absents[$key]['all_the_day'] = 0;
                }

                if ($absent['debut'] <= $date . " 00:00:00"
                    and $absent['fin'] >= $date . " 23:59:59"
                    and $absent['valide'] > 0) {
                    $absent_ids[] = $absent['perso_id'];
                }
            }

            $d = new \datePL($date);
            $presentset = new PresentSet($date, $d, $absent_ids, new \db());
            $presents = $presentset->all();
            foreach ($presents as $key => $present) {
                $presents[$key]['heures'] = html_entity_decode($present['heures'], ENT_QUOTES|ENT_HTML5);
            }

            // Gather attendance and absences in a single table
            $tab = array();
            foreach ($presents as $present) {
                $tab[$present['id']] = array('nom' => $present['nom'], 'prenom' => null, 'presence' => $present);
            }

            foreach ($absents as $absent) {
                if ($absent['valide'] < 0 ) {
                    continue;
                }
                if (empty($tab[$absent['perso_id']])) {
                    $tab[$absent['perso_id']] = array('nom' => $absent['nom'], 'prenom' => $absent['prenom']);
                }
                $tab[$absent['perso_id']]['absences'][] = $absent;
            }

            $by_date[] = array(
                'date' => date('d/m/Y', $i),
                'tab' => $tab,
            );
        }

        $this->templateParams(array(
            'by_date'   => $by_date,
            'from'      => $params['from'],
            'to'        => $params['to']
        ));

        return $this->output('statistics/attendeesmissing.html.twig');
    }
}
