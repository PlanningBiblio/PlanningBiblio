<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\AbsenceReason;
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
     * @Route("/statistics/status", name="statistics.status", methods={"GET", "POST"})
     */
    public function status( Request $request, Session $session)
    {
        // Initialisation des variables :
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $statistiques_heures = $request->get("statistiques_heures");
        $statistiques_heures_defaut = $request->get("statistiques_heures_defaut");
        $post = $request->request->all();

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $post_statuts = isset($post['statuts']) ? $post['statuts'] : null;
        $post_sites = isset($post['selectedSites']) ? $post['selectedSites'] : null;

        $joursParSemaine = $this->config('Dimanche')? 7 : 6;
        $statuts_tab = null;
        $exists_JF = false;
        $exists_absences = false;

        // Statistiques-Heures
        $heures_tab_global = array();
        if ($statistiques_heures_defaut) {
            $statistiques_heures = $this->config('Statistiques-Heures');
        } else {
            if (!$statistiques_heures and !empty($_SESSION['oups']['statistiques_heures'])) {
                $statistiques_heures = $_SESSION['oups']['statistiques_heures'];
            } elseif (!$statistiques_heures and !empty($this->config('Statistiques-Heures'))) {
                $statistiques_heures = $this->config('Statistiques-Heures');
            }
        }

        $_SESSION['oups']['statistiques_heures'] = $statistiques_heures;

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

        // Filtre les statuts
        if (!array_key_exists('stat_statut_statuts', $_SESSION)) {
            $_SESSION['stat_statut_statuts'] = null;
        }

        $statuts=array();
        if ($post_statuts) {
            foreach ($post_statuts as $elem) {
                $statuts[] = $elem;
            }
        } else {
            $statuts = $_SESSION['stat_statut_statuts'];
        }
        $_SESSION['stat_statut_statuts'] = $statuts;


        // Filtre les sites
        if (!array_key_exists('stat_statut_sites', $_SESSION)) {
            $_SESSION['stat_statut_sites'] = array();
        }

        if ($post_sites) {
            $selectedSites=array();
            foreach ($post_sites as $elem) {
                $selectedSites[] = $elem;
            }
        } else {
            $selectedSites = $_SESSION['stat_statut_sites'];
        }

        $nbSites = $this->config('Multisites-nombre');

        if ($nbSites > 1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }
        $_SESSION['stat_statut_sites'] = $selectedSites;

        // Filtre les sites dans les requêtes SQL
        if ($nbSites > 1 and is_array($selectedSites)) {
            $sitesSQL="0,".join(",", $selectedSites);
        } else {
            $sitesSQL="0,1";
        }

        // Teleworking
        $teleworking_absence_reasons = array();
        $absences_reasons = $this->entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
        foreach ($absences_reasons as $elem) {
            $teleworking_absence_reasons[] = $elem->valeur();
        }

        $tab = array();

        //		--------------		Récupération de la liste des statuts pour le menu déroulant		------------------------
        $db = new \db();
        $db->select2("select_statuts");
        $statuts_list = $db->result;

        if (!empty($statuts)) {
            //	Recherche du nombre de jours concernés
            $db = new \db();
            $db->select2(
                "pl_poste", 
                "date", 
                array(
                    "date"=>"BETWEEN{$debutSQL}AND{$finSQL}", 
                    "site"=>"IN{$sitesSQL}"
                ), 
                "GROUP BY `date`;"
            );
            $nbJours = $db->nb;

            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB = $a->elements;

            // Recherche des statuts de chaque agent
            $db = new \db();
            $db->select2("personnel", array("id","statut"));
            foreach ($db->result as $elem) {
                $statutId = null;
                foreach ($statuts_list as $stat) {
                    if ($stat['valeur'] == $elem['statut']) {
                        $statutId = $stat['id'];
                        continue;
                    }
                }
                $agents[$elem['id']] = array(
                    "id"       =>$elem['id'],
                    "statut"   =>$elem['statut'],
                    "statut_id"=>$statutId
                );
            }

            //	Recherche des infos dans pl_poste et postes pour tous les statuts sélectionnés
            //	On stock le tout dans le tableau $resultat

            $db = new \db();
            $db->selectInnerJoin(
                array("pl_poste","poste"),
                array("postes","id"),
                array("debut","fin","date","perso_id","poste","absent"),
                array(
                    array(
                        "name"=>"nom",
                        "as"=>"poste_nom",
                    ),
                    "etage",
                    "site",
                    "teleworking",
                ),

                array(
                    "date"    => "BETWEEN{$debutSQL}AND{$finSQL}", 
                    "supprime"=> "<>1", 
                    "site"    => "IN{$sitesSQL}"
                ),
                array("statistiques"=>"1"),
                "ORDER BY `poste_nom`,`etage`"
        );
            $resultat = $db->result;

            // Ajoute le statut pour chaque agents dans le tableau resultat
            for ($i = 0; $i < count($resultat); $i++) {

                if ($resultat[$i]['perso_id'] == 0) {
                    continue;
                }

                $resultat[$i]['statut'] = $agents[$resultat[$i]['perso_id']]['statut'];
                $resultat[$i]['statut_id'] = $agents[$resultat[$i]['perso_id']]['statut_id'];
            }

            //	Recherche des infos dans le tableau $resultat (issu de pl_poste et postes)
            //	pour chaque statut sélectionné

            foreach ($statuts as $statut) {
                if (array_key_exists($statut, $tab)) {
                    $heures = $tab[$statut][2];
                    $total_absences = $tab[$statut][5];
                    $samedi = $tab[$statut][3];
                    $dimanche = $tab[$statut][6];
                    $absences = $tab[$statut][4];
                    $heures_tab = $tab[$statut][7];
                    $feries = $tab[$statut][8];
                    $sites = $tab[$service]["sites"];
                } else {
                    $heures = 0;
                    $total_absences = 0;
                    $samedi = array();
                    $dimanche = array();
                    $absences = array();
                    $heures_tab = array();
                    $feries = array();
                    for ($i = 1; $i <= $nbSites; $i++) {
                        $sites[$i] = 0;
                    }
                }
                $postes = array();
                if (is_array($resultat)) {
                    foreach ($resultat as $elem) {

                        if (!isset($elem['statut_id'])) {
                            continue;
                        }

                        if ($statut == $elem['statut_id']) {
                            // Vérifie à partir de la table absences si l'agent est absent
                            // S'il est absent, on met à 1 la variable $elem['absent']
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {

                                foreach ($absencesDB[$elem['perso_id']] as $a) {

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $elem['teleworking']) {
                                        continue;
                                    }

                                    if ($a['debut'] < $elem['date'].' '.$elem['fin'] and $a['fin'] > $elem['date']." ".$elem['debut']) {
                                        $elem['absent'] = "1";
                                    }
                                }
                            }

                            if ($elem['absent'] != "1") {		// on compte les heures et les samedis pour lesquels l'agent n'est pas absent
                                // on créé un tableau par poste avec son nom, étage et la somme des heures faites par statut
                                if (!array_key_exists($elem['poste'], $postes)) {
                                    $postes[$elem['poste']] = array($elem['poste'],$elem['poste_nom'],$elem['etage'],0,"site"=>$elem['site']);
                                }
                                $postes[$elem['poste']][3] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                // On compte les heures de chaque site
                                if ($nbSites > 1) {
                                    $sites[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                }
                                // On compte toutes les heures (globales)
                                $heures += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $d = new \datePl($elem['date']);
                                if ($d->sam == "samedi") {	// tableau des samedis
                                    if (!array_key_exists($elem['date'], $samedi)) { // on stock les dates et la somme des heures faites par date
                                        $samedi[$elem['date']][0] = $elem['date'];
                                        $samedi[$elem['date']][1] = 0;
                                    }
                                    $samedi[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                }
                                if ($d->position==0) {		// tableau des dimanches
                                    if (!array_key_exists($elem['date'], $dimanche)) { 	// on stock les dates et la somme des heures faites par date
                                        $dimanche[$elem['date']][0] = $elem['date'];
                                        $dimanche[$elem['date']][1] = 0;
                                    }
                                    $dimanche[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                }
                                if (jour_ferie($elem['date'])) {
                                    if (!array_key_exists($elem['date'], $feries)) {
                                        $feries[$elem['date']][0] = $elem['date'];
                                        $feries[$elem['date']][1] = 0;
                                    }
                                    $feries[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                    $exists_JF = true;
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
                                            $tmp[2] = heure3($tmp[0])."-".heure3($tmp[1]);
                                            $heures_tab[$tmp[2]][] = $elem['date'];
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
                            // On met dans tab tous les éléments (infos postes + statuts + heures)
                            $tab[$statut] = array($elem['statut'],$postes,$heures,$samedi,$absences,$total_absences,$dimanche,$heures_tab,$feries,"sites"=>$sites);
                        }
                    }
                }
            }
        }

        sort($heures_tab_global);
        // Heures et jours d'ouverture au public
        $s = new \statistiques();
        $s->debut = $debutSQL;
        $s->fin = $finSQL;
        $s->joursParSemaine = $joursParSemaine;
        $s->selectedSites = $selectedSites;
        $s->ouverture();
        $ouverture = $s->ouvertureTexte;
        
        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;

        $selectedStatus = array();
        if (is_array($statuts_list)) {
            foreach ($statuts_list as $elem) {
                if (!empty($statuts)) {
                    $selectedStatus[] = in_array($elem['id'], $statuts) ?? $elem['id'];
                }
            }
        }
        
        $multisites = array();
        if ($nbSites > 1) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $multisites[] = $this->config("Multisites-site{$i}");
            }
        }

        if($tab){
            foreach ($tab as &$elem) {
                $jour = $elem[2]/$nbJours;
                $hebdo = $jour*$joursParSemaine;
                $elem[2] = heure4($elem[2]);
                $elem["jour"] = $jour;
                $elem["hebdo"] = heure4($hebdo);
                
                if ($nbSites > 1) {
                    for ($i = 1;$i <= $nbSites; $i++) {
                        if ($elem["sites"][$i]) {
                            // Calcul des moyennes
                            $jour = $elem["sites"][$i]/$nbJours;
                            $hebdo = $jour*$joursParSemaine;
                        }
                        $elem["sites"][$i] = heure4($elem["sites"][$i]);
                        $elem["site_hebdo"][$i] = heure4($hebdo);
                    }
                }
                //	Affichage du noms des postes et des heures dans la 2eme colonne
                foreach ($elem[1] as &$poste) {
                    $site = null;
                    if ($poste["site"]>0 and $nbSites > 1) {
                        $site = $this->config("Multisites-site{$poste['site']}")." ";
                    }
                    $etage = $poste[2]?$poste[2] : null;
                    $siteEtage = ($site or $etage)?"($site{$etage})" : null;
                    $poste["siteEtage"] = $siteEtage;
                    $poste[3] = heure4($poste[3]);
                }

                sort($elem[3]);				//	tri les samedis par dates croissantes
                foreach ($elem[3] as &$samedi) {			//	Affiche les dates et heures des samedis
                    $samedi[0] = dateFr($samedi[0]);			//	date
                    $samedi[1] = heure4($samedi[1]);	        // heures
                }
                
                if ($this->config('Dimanche')) {
                    sort($elem[6]);				//	tri les dimanches par dates croissantes
                    foreach ($elem[6] as &$dimanche) {		//	Affiche les dates et heures des dimanches
                        $dimanche[0] = dateFr($dimanche[0]);		//	date
                        $dimanche[1] = heure4($dimanche[1]);	//	heures
                    }
                }
        
                if ($exists_JF) {
                    sort($elem[8]);				//	tri les jours fériés par dates croissantes
                    foreach ($elem[8] as &$ferie) {		// 	Affiche les dates et heures des jours fériés
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
                        $absences[0] = dateFr($absences[0]);		//	date
                        $absences[1] = heure4($absences[1]);	// heures
                    }
                }
        
                // Statistiques-Heures
                foreach ($heures_tab_global as $v) {
                    if (array_key_exists($v[2], $elem[7]) and !empty($elem[7][$v[2]])) {
                        $count = array();     
                        foreach ($elem[7][$v[2]] as $h) {
                            if (empty($count[$h])) {
                                $count[$h] = 1;
                            } else {
                                $count[$h]++;
                            }
                        }
                        $elem[7][$v[2]]["count"] = $count;
                        ksort($elem[7][$v[2]]["count"]);
                        foreach ($elem[7][$v[2]]["count"] as $k => $v2) {
                            $nk = dateFr($k);
                            $elem[7][$v[2]]["count"][$nk] = $elem[7][$v[2]]["count"][$k];
                            unset($elem[7][$v[2]]["count"][$k]);
                        }
                    }
                }
            }
        }
        $this->templateParams(
            array(
                "debut"               => $debut,
                "exists_absences"     => $exists_absences,
                "exists_dimanche"     => $this->config('Dimanche'),
                "exists_JF"           => $exists_JF,
                "fin"                 => $fin,
                "heures_tab_global"   => $heures_tab_global,
                "multisites"          => $multisites,
                "nbSites"             => $nbSites,
                "ouverture"           => $ouverture,
                "selectedSites"       => $selectedSites,
                "statistiques_heures" => $statistiques_heures,
                "statuts"             => $statuts,
                "statuts_list"        => $statuts_list,
                "tab"                 => $tab   
            )
        );
        return $this->output('/statistics/status.html.twig');
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
