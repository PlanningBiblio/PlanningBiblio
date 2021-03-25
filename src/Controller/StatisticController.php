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
include_once __DIR__ . '/../../public/postes/class.postes.php';

class StatisticController extends BaseController
{

    /**
     * @Route("/statistics", name="statistics.index", methods={"GET"})
     */
    public function index(Request $request, Session $session)
    {
        return $this->output('statistics/index.html.twig');
    }

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

        // Teleworking
        $teleworking_absence_reasons = array();
        $absences_reasons = $this->entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
        foreach ($absences_reasons as $elem) {
            $teleworking_absence_reasons[] = $elem->valeur();
        }

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
                array(array("name"=>"nom","as"=>"poste_nom"),"etage","site","teleworking"),
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

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $elem['teleworking']) {
                                        continue;
                                    }

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
                                        $tmp[2] = heure3($tmp[0]).'-'.heure3($tmp[1]);
                                        if ($elem['debut'] == $tmp[0] and $elem['fin'] == $tmp[1]) {
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
     * @Route("/statistics/service", name="statistics.service", methods={"GET", "POST"})
     */
    public function service(Request $request, Session $session)
    {
        // Initialisation des variables :
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $statistiques_heures = $request->get("statistiques_heures");

        $statistiques_heures_defaut = $request->get("statistiques_heures_defaut");
        $post = $request->request->all();
        $nbSites = $this->config("Multisites-nombre");
        $dbprefix = $GLOBALS["dbprefix"];
        $multisites = array();

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $post_services = isset($post['services']) ? $post['services'] : null;
        $post_sites = isset($post['selectedSites']) ? $post['selectedSites'] : null;

        $joursParSemaine = $this->config('Dimanche') ? 7 : 6;
        $services_tab = null;
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

        // Filtre les services
        if (!array_key_exists('stat_service_services', $_SESSION)) {
            $_SESSION['stat_service_services'] = null;
        }

        $services = array();
        if ($post_services) {
            foreach ($post_services as $elem) {
                $services[] = $elem;
            }
        } else {
            $services = $_SESSION['stat_service_services'];
        }
        $_SESSION['stat_service_services'] = $services;


        // Filtre les sites
        if (!array_key_exists('stat_services_sites', $_SESSION)) {
            $_SESSION['stat_services_sites'] = array();
        }

        $selectedSites = array();
        if ($post_sites) {
            foreach ($post_sites as $elem) {
                $selectedSites[] = $elem;
            }
        } else {
            $selectedSites = $_SESSION['stat_services_sites'];
        }

        if ($nbSites > 1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }
        $_SESSION['stat_services_sites'] = $selectedSites;


        // Filtre les sites dans les requêtes SQL
        if ($nbSites > 1 and is_array($selectedSites)) {
            $sitesSQL = "0,".join(",", $selectedSites);
        } else {
            $sitesSQL = "0,1";
        }

        // Teleworking
        $teleworking_absence_reasons = array();
        $absences_reasons = $this->entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
        foreach ($absences_reasons as $elem) {
            $teleworking_absence_reasons[] = $elem->valeur();
        }

        $tab = array();

        //		--------------		Récupération de la liste des services pour le menu déroulant		------------------------
        $db = new \db();
        $db->select2("select_services");
        $services_list = $db->result;

        if (!empty($services)) {
            //	Recherche du nombre de jours concernés
            $db = new \db();
            $db->select2("pl_poste", "date", array("date"=>"BETWEEN{$debutSQL}AND{$finSQL}", "site"=>"IN{$sitesSQL}"), "GROUP BY `date`;");
            $nbJours = $db->nb;

            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB = $a->elements;

            // Recherche des services de chaque agent
            $db = new \db();
            $db->select2("personnel", array("id","service"));
            foreach ($db->result as $elem) {
                $servId = null;
                foreach ($services_list as $serv) {
                    if ($serv['valeur'] == $elem['service']) {
                        $servId = $serv['id'];
                        continue;
                    }
                }
                $agents[$elem['id']] = array("id"=>$elem['id'],"service"=>html_entity_decode($elem['service']),"service_id"=>$servId);
            }

            //	Recherche des infos dans pl_poste et postes pour tous les services sélectionnés
            //	On stock le tout dans le tableau $resultat

            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);

            $req = "SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
            `{$dbprefix}pl_poste`.`date` as `date`, `{$dbprefix}pl_poste`.`perso_id` as `perso_id`, 
            `{$dbprefix}pl_poste`.`poste` as `poste`, `{$dbprefix}pl_poste`.`absent` as `absent`, 
            `{$dbprefix}pl_poste`.`site` as `site`,
            `{$dbprefix}postes`.`nom` as `poste_nom`, `{$dbprefix}postes`.`etage` as `etage`,
            `{$dbprefix}postes`.`teleworking` as `teleworking` 
            FROM `{$dbprefix}pl_poste` 
            INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id` 
            WHERE `{$dbprefix}pl_poste`.`date`>='$debutREQ' AND `{$dbprefix}pl_poste`.`date`<='$finREQ' 
            AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}postes`.`statistiques`='1' 
            AND `{$dbprefix}pl_poste`.`site` IN ($sitesREQ)
            ORDER BY `poste_nom`,`etage`;";
            $db->query($req);
            $resultat = $db->result;
            // Ajoute le service pour chaque agents dans le tableau resultat
            for ($i = 0; $i<count($resultat); $i++) {

                if ($resultat[$i]['perso_id'] == 0) {
                    continue;
                }

                $resultat[$i]['service'] = $agents[$resultat[$i]['perso_id']]['service'];
                $resultat[$i]['service_id'] = $agents[$resultat[$i]['perso_id']]['service_id'];
            }

            //	Recherche des infos dans le tableau $resultat (issu de pl_poste et postes)
            //	pour chaque service sélectionné

            foreach ($services as $service) {
                if (array_key_exists($service, $tab)) {
                    $heures = $tab[$service][2];
                    $total_absences = $tab[$service][5];
                    $samedi = $tab[$service][3];
                    $dimanche = $tab[$service][6];
                    $heures_tab = $tab[$service][7];
                    $absences = $tab[$service][4];
                    $feries = $tab[$service][8];
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
                        if (!isset($elem['service_id'])) {
                            continue;
                        }

                        if ($service == $elem['service_id']) {
                            // Vérifie à partir de la table absences si l'agent est absent
                            // S'il est absent, on met à 1 la variable $elem['absent']
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {

                                foreach ($absencesDB[$elem['perso_id']] as $a) {

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $elem['teleworking']) {
                                        continue;
                                    }

                                    if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                        $elem['absent'] = "1";
                                    }
                                }
                            }

                            if ($elem['absent']!="1") {		// on compte les heures et les samedis pour lesquels l'agent n'est pas absent
                                // on créé un tableau par poste avec son nom, étage et la somme des heures faites par service
                                if (!array_key_exists($elem['poste'], $postes)) {
                                    $postes[$elem['poste']] = array($elem['poste'],$elem['poste_nom'],$elem['etage'],0,"site"=>$elem['site']);
                                }
                                $postes[$elem['poste']][3] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                // On compte les heures de chaque site
                                if ($nbSites>1) {
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
                                if ($d->position == 0) {		// tableau des dimanches 
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
                            // On met dans tab tous les éléments (infos postes + services + heures)
                            $tab[$service] = array(
                                html_entity_decode($elem['service']), 
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

        if ($nbSites > 1){
            for ($i = 1; $i <= $nbSites; $i++) {
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }

        foreach ($tab as &$elem) {
            $jour = $elem[2]/$nbJours;
            $hebdo = $jour*$joursParSemaine;
            $elem[2] = heure4($elem[2]);
            $elem["hebdo"] = heure4($hebdo);

            if ($nbSites > 1) {
                for ($i = 1; $i <= $nbSites; $i++) {
                    if ($elem["sites"][$i]) {
                        // Calcul des moyennes
                        $jour = floatval($elem["sites"][$i])/$nbJours;
                        $hebdo = $jour*$joursParSemaine;
                        $elem["sites"][$i] = heure4($elem["sites"][$i]);
                    }
                }
            }
            foreach ($elem[1] as &$poste) {
                $site = null;
                if ($poste["site"] > 0 and $nbSites > 1) {
                    $site = $this->config("Multisites-site{$poste['site']}")." ";
                }
                $etage = $poste[2] ? $poste[2] : null;
                $siteEtage = ($site or $etage) ? "(".trim($site.$etage).")" : null;
                $poste[3] = heure4($poste[3]);
                $poste["siteEtage"] = $siteEtage;
            }
            sort($elem[3]);
            foreach ($elem[3] as &$samedi) {			//	Affiche les dates et heures des samedis
                $samedi[0] = dateFr($samedi[0]);			//	date
                $samedi[1] = heure4($samedi[1]);	// heures
            }

            if ($this->config('Dimanche')) {
                sort($elem[6]);
                foreach ($elem[6] as &$dimanche) {		//	Affiche les dates et heures des dimanches
                    $dimanche[0] = dateFr($dimanche[0]);		//	date
                    $dimanche[1] = heure4($dimanche[1]);	//	heures
                }
            }

            if ($exists_JF) {
                sort($elem[8]);
                foreach ($elem[8] as &$ferie) {		// 	Affiche les dates et heures des jours fériés
                    $ferie[0] = dateFr($ferie[0]);			//	date
                    $ferie[1] = heure4($ferie[1]);	//	heures
                }
            }

            if ($exists_absences) {
                if ($elem[5]) {				//	Affichage du total d'heures d'absences
                    $elem[5] = heure4($elem[5]);
                }
                sort($elem[4]);
                foreach ($elem[4] as &$absences) {		//	Affiche les dates et heures des absences
                    $absences[0] = dateFr($absences[0]);	//	date
                    $absences[1] = heure4($absences[1]);	// heures
                }
            }

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

        $this->templateParams(
            array(
                "debut" => $debut,
                "exists_absences" => $exists_absences,
                "exists_dimanche" => $this->config('Dimanche'),
                "exists_JF" => $exists_JF,
                "fin" => $fin,
                "heures_tab_global" => $heures_tab_global,
                "multisites" => $multisites,
                "nbSites" => $nbSites,
                "ouverture" => $ouverture,
                "selectedSites" => $selectedSites,
                "services" => $services,
                "services_list" => $services_list,
                "statistiques_heures" => $statistiques_heures,
                "tab" => $tab,
            )
        );
        return $this->output("statistics/service.html.twig");
    }
      
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

    /**
     * @Route("/statistics/absence", name="statistics.absence", methods={"GET", "POST"})
     */
    public function absence( Request $request, Session $session )
    {
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $site = $request->get('site');

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $afficheHeures = $this->config('PlanningHebdo') ? true : false;

        if ($debut) {
            $fin = $fin ? $fin : $debut;
            $site = $site ? $site : 0;
        } elseif (array_key_exists("stat_absences_debut", $_SESSION['oups'])) {
            $debut = $_SESSION['oups']['stat_absences_debut'];
            $fin = $_SESSION['oups']['stat_absences_fin'];
            $site = isset($_SESSION['oups']['stat_absences_site']) ? $_SESSION['oups']['stat_absences_site'] : 0;
        } else {
            $date = $_SESSION['PLdate'];
            $d = new \datePl($date);
            $debut = dateFr($d->dates[0]);
            $fin = $this->config('Dimanche') ? dateFr($d->dates[6]) : dateFr($d->dates[5]);
            $site = 0;
        }
        $_SESSION['oups']['stat_absences_debut'] = $debut;
        $_SESSION['oups']['stat_absences_fin'] = $fin;

        $debutSQL = dateSQL($debut);
        $finSQL = dateSQL($fin);

        $sites = null;
        $nbSites = $this->config('Multisites-nombre');;
        if ($nbSites > 1) {
            $sites = array();
            if ($site == 0) {
                for ($i = 1;$i <= $nbSites; $i++) {
                    $sites[] = $i;
                }
            } else {
                $sites = array($site);
            }
        }

        // Recherche des absences
        $a = new \absences();
        $a->valide = true;
        $a->fetch("`debut`,`fin`,`nom`,`prenom`", null, $debutSQL, $finSQL, $sites);
        $absences = $a->elements;

        // Recherche des motifs d'absences
        $motifs = array();
        if (is_array($absences) and !empty($absences)) {
            foreach ($absences as $elem) {
                if (!in_array($elem['motif'], $motifs)) {
                    $motifs[] = $elem['motif'];
                }
            }
        }
        sort($motifs);

        // Regroupe les absences par agent et par motif
        // Et ajoute les heures correspondantes
        $tab = array();
        $totaux = array("_general"=>0,"_generalHeures"=>0);
        foreach ($absences as $elem) {
            if (!array_key_exists($elem['perso_id'], $tab)) {
                $tab[$elem['perso_id']] = array(
                    "nom"         => $elem['nom'],
                    "prenom"      => $elem['prenom'],
                    "total"       => 0,
                    "totalHeures" => 0
                );
            }
            if (!array_key_exists($elem['motif'], $tab[$elem['perso_id']])) {
                $tab[$elem['perso_id']][$elem['motif']] = array(
                    "total"  => 0,
                    "heures" => 0
                );
            }
            if (!array_key_exists($elem['motif'], $totaux)) {
                $totaux[$elem['motif']] = array(
                    "frequence" => 0,
                    "heures"    => 0
                );
            }

            // Total agent
            $tab[$elem['perso_id']]['total']++;
            // Totaux généraux
            $totaux['_general']++;
            // Total agent pour le motif courant
            $tab[$elem['perso_id']][$elem['motif']]['total']++;
            // Total pour ce motif
            $totaux[$elem['motif']]['frequence']++;

            // Ajout des heures d'absences
            if ($afficheHeures) {
                $a = new \absences();
                $a->calculTemps($elem['debut'], $elem['fin'], $elem['perso_id']);
                $heures = $a->heures;

                // heures agent pour le motif courant
                if ($a->error) {
                    $tab[$elem['perso_id']][$elem['motif']]['heures'] = "Erreur";
                } elseif (is_numeric($tab[$elem['perso_id']][$elem['motif']]['heures'])) {
                    $tab[$elem['perso_id']][$elem['motif']]['heures'] += $heures;
                }
                // Total heures pour ce motif
                if ($a->error) {
                    $totaux[$elem['motif']]['heures'] = "Erreur";
                } elseif (is_numeric($totaux[$elem['motif']]['heures'])) {
                    $totaux[$elem['motif']]['heures'] += $heures;
                }

                if ($a->error) {
                    // Total heures agent
                    $tab[$elem['perso_id']]['totalHeures'] = "Erreur";
                    // Totaux heures généraux
                    $totaux['_generalHeures'] = "Erreur";
                } else {
                    // Total heures agent
                    if (is_numeric($tab[$elem['perso_id']]['totalHeures'])) {
                        $tab[$elem['perso_id']]['totalHeures'] += $heures;
                    }
                    // Totaux heures généraux
                    if (is_numeric($totaux['_generalHeures'])) {
                        $totaux['_generalHeures'] += $heures;
                    }
                }
            }
        }

        // Pour les exports
        $_SESSION['oups']['stat_absences_motifs'] = $motifs;
        $_SESSION['oups']['stat_absences_totaux'] = $totaux;
        $_SESSION['stat_tab']=$tab;

        // Affichage du tableau
        $multisites = array();
        $selectedSites = array();
        if ($nbSites >1) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i == $site ?? $i;
                $multisites[] = $this->config("Multisites-site{$i}");
            }
        }

        foreach ($tab as &$elem) {
            foreach ($motifs as $motif) {
                if (in_array($motif, $elem)){
                    $elem[$motif]['heures'] = is_numeric($elem[$motif]['heures']) ? heure4($elem[$motif]['heures']) : "Erreur";
                }
            }
        }

        $totaux['_generalHeures'] = is_numeric($totaux['_generalHeures']) ? heure4($totaux['_generalHeures']) : "Erreur";

        foreach ($motifs as $motif) {
            $totaux[$motif]['heures'] = is_numeric($totaux[$motif]['heures']) ? heure4($totaux[$motif]['heures']) : "Erreur";
        }

        $this->templateParams(array(
          "afficheHeures"   => $afficheHeures,
          "debut"           => $debut,
          "fin"             => $fin,
          "motifs"          => $motifs,
          "multisites"      => $multisites,
          "nbSites"         => $nbSites,
          "selectedSites"   => $selectedSites,
          "tab"             => $tab,
          "totaux"          => $totaux
        ));
        return $this->output('statistics/absence.html.twig');
    }

    /**
     * @Route("/statistics/positionsummary", name="statistics.positionsummary", methods={"GET", "POST"})
     */
    public function positionsummary(Request $request, Session $session)
    {
        //	Variables :
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $tri = $request->get("tri");
        $post = $request->request->all();
        $nbSites = $this->config('Multisites-nombre');
        $dbprefix = $GLOBALS['dbprefix'];

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $post_postes = isset($post['postes'])?$post['postes']:null;
        $post_sites = isset($post['selectedSites'])?$post['selectedSites']:null;

        $joursParSemaine = $this->config('Dimanche') ? 7 : 6;

        if (!$debut and array_key_exists('stat_debut', $_SESSION)) {
            $debut = $_SESSION['stat_debut'];
        }
        if (!$fin and array_key_exists('stat_fin', $_SESSION)) {
            $fin = $_SESSION['stat_fin'];
        }
        if (!$tri and array_key_exists('stat_poste_tri', $_SESSION)) {
            $tri = $_SESSION['stat_poste_tri'];
        }

        if (!$debut) {
            $debut = "01/01/".date("Y");
        }
        if (!$fin) {
            $fin = date("d/m/Y");
        }
        if (!$tri) {
            $tri = "cmp_01";
        }

        $_SESSION['stat_debut'] = $debut;
        $_SESSION['stat_fin'] = $fin;
        $_SESSION['stat_poste_tri'] = $tri;

        $debutSQL = dateFr($debut);
        $finSQL = dateFr($fin);

        // Postes
        if (!array_key_exists('stat_poste_postes', $_SESSION)) {
            $_SESSION['stat_poste_postes'] = null;
        }

        $postes=array();
        if ($post_postes) {
            foreach ($post_postes as $elem) {
                $postes[] = $elem;
            }
        } else {
            $postes = $_SESSION['stat_poste_postes'];
        }
        $_SESSION['stat_poste_postes']=$postes;

        // Filtre les sites
        if (!array_key_exists('stat_poste_sites', $_SESSION)) {
            $_SESSION['stat_poste_sites'] = array();
        }

        if ($post_sites) {
            $selectedSites = array();
            foreach ($post_sites as $elem) {
                $selectedSites[] = $elem;
            }
        } else {
            $selectedSites = $_SESSION['stat_poste_sites'];
        }

        $_SESSION['stat_poste_postes'] = $postes;

        // Filtre les sites
        if (!array_key_exists('stat_poste_sites', $_SESSION)) {
            $_SESSION['stat_poste_sites'] = null;
        }

        if ($nbSites>1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }
        $_SESSION['stat_poste_sites'] = $selectedSites;

        // Filtre les sites dans les requêtes SQL
        if ($nbSites>1 and is_array($selectedSites)) {
            $sitesSQL =" 0,".join(",", $selectedSites);
        } else {
            $sitesSQL = "0,1";
        }

        // Teleworking
        $teleworking_absence_reasons = array();
        $absences_reasons = $this->entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
        foreach ($absences_reasons as $elem) {
            $teleworking_absence_reasons[] = $elem->valeur();
        }

        $tab = array();

        $total_heures = 0;
        $total_jour = 0;
        $total_hebdo = 0;
        $selected = null;

        //		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
        $db = new \db();
        $db->query("SELECT * FROM `{$dbprefix}postes` WHERE `statistiques`='1' ORDER BY `etage`,`nom`;");
        $postes_list = $db->result;

        if (!empty($postes)) {
            //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
            //	On stock le tout dans le tableau $resultat
            $postes_select = join(",", $postes);
            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);
            $postesREQ = $db->escapeString($postes_select);

            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB=$a->elements;

            $req = "SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
            `{$dbprefix}pl_poste`.`date` as `date`,  `{$dbprefix}pl_poste`.`poste` as `poste`, 
            `{$dbprefix}personnel`.`nom` as `nom`, `{$dbprefix}personnel`.`prenom` as `prenom`, 
            `{$dbprefix}personnel`.`id` as `perso_id`, `{$dbprefix}pl_poste`.site as `site` 
            FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` 
            ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` 
            WHERE `{$dbprefix}pl_poste`.`date`>='$debutREQ' AND `{$dbprefix}pl_poste`.`date`<='$finREQ' 
            AND `{$dbprefix}pl_poste`.`poste` IN ($postesREQ) AND `{$dbprefix}pl_poste`.`absent`<>'1' 
            AND `{$dbprefix}pl_poste`.`supprime`<>'1'  AND `{$dbprefix}pl_poste`.`site` IN ($sitesREQ) 
            ORDER BY `poste`,`nom`,`prenom`;";
            $db->query($req);
            $resultat = $db->result;

            //	Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel) pour chaque poste sélectionné
            foreach ($postes as $poste) {
                if (array_key_exists($poste, $tab)) {
                    $heures = $tab[$poste][2];
                    $sites = $tab[$poste]["sites"];
                } else {
                    $heures = 0;
                    for ($i = 1; $i <= $nbSites; $i++) {
                        $sites[$i] = 0;
                    }
                }

                // $poste_tab : table of positions with id, name, area, mandatory/reinforcement, teleworking
                foreach ($postes_list as $elem) {
                    if ($elem['id'] == $poste) {
                        $poste_tab = array($poste, $elem['nom'], $elem['etage'], $elem['obligatoire'], $elem['teleworking']);
                        break;
                    }
                }

                $agents = array();
                if (is_array($resultat)) {
                    foreach ($resultat as $elem) {
                        if ($poste == $elem['poste']) {
                            // Vérifie à partir de la table absences si l'agent est absent
                            // S'il est absent : continue
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {
                                foreach ($absencesDB[$elem['perso_id']] as $a) {

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $poste_tab[4]) {
                                        continue;
                                    }

                                    if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                        continue 2;
                                    }
                                }
                            }
                            //	On créé un tableau par agent avec son nom, prénom et la somme des heures faites par poste
                            if (!array_key_exists($elem['perso_id'], $agents)) {
                                $agents[$elem['perso_id']] = array($elem['perso_id'], $elem['nom'], $elem['prenom'], 0, "site"=>$elem['site']);
                            }
                            $agents[$elem['perso_id']][3] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                            // On compte les heures de chaque site
                            if ($nbSites>1) {
                                $sites[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                            }
                            // On compte toutes les heures (globales)
                            $heures += diff_heures($elem['debut'], $elem['fin'], "decimal");

                            //	On met dans tab tous les éléments (infos postes + agents + heures du poste)
                            $tab[$poste] = array($poste_tab,$agents,$heures,"sites"=>$sites);
                        }
                    }
                }
            }
        }

        // Heures et jours d'ouverture au public
        $s = new \statistiques();
        $s->debut = $debutSQL;
        $s->fin = $finSQL;
        $s->joursParSemaine = $joursParSemaine;
        $s->selectedSites = $selectedSites;
        $s->ouverture();
        $ouverture = $s->ouvertureTexte;

        //		-------------		Tri du tableau		------------------------------
        usort($tab, $tri);

        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;
        $multisites = array();
        if ($nbSites > 1) {
            for ($i=1;$i<=$nbSites;$i++) {
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }

        if ($tab) {
            //	Recherche du nombre de jours concernés
            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);

            $db->select("pl_poste", "`date`", "`date` BETWEEN '$debutREQ' AND '$finREQ' AND `site` IN ($sitesREQ)", "GROUP BY `date`;");
            $nbJours = $db->nb;

            foreach ($tab as &$elem) {
                $jour=$elem[2]/$nbJours;
                $hebdo=$jour*$joursParSemaine;
                $total_heures+=$elem[2];
                $total_jour+=$jour;
                $total_hebdo+=$hebdo;

                // Sites
                $siteEtage=array();
                if ($nbSites > 1) {
                    for ($i = 1; $i <= $nbSites; $i++) {
                        if ($elem["sites"][$i]==$elem[2]) {
                            $siteEtage[]=$multisites[$i];
                            continue;
                        }
                    }
                }
                // Etages
                if ($elem[0][2]) {
                    $siteEtage[]=$elem[0][2];
                }
                if (!empty($siteEtage)) {
                    $siteEtage="(".join(" ", $siteEtage).")";
                } else {
                    $siteEtage=null;
                }

                $elem["siteEtage"] = $siteEtage;
                $elem[2] = heure4($elem[2]);
                $elem["jour"] = heure4($jour);
                $elem["hebdo"] = heure4($hebdo);

            }

            $total_heures = heure4($total_heures);
            $total_jour = heure4($total_jour);
            $total_hebdo = heure4($total_hebdo);
        }

        $this->templateParams(
            array(
                "debut" => $debut,
                "fin" => $fin,
                "multisites" => $multisites,
                "nbSites" => $nbSites,
                "ouverture" => $ouverture,
                "postes" => $postes,
                "postes_list" => $postes_list,
                "selectedSites" => $selectedSites,
                "tab" => $tab,
                "total_hebdo" => $total_hebdo,
                "total_heures" => $total_heures,
                "total_jour"  => $total_jour,
                "tri" => $tri
            )
        );
        return $this->output('statistics/positionsummary.html.twig');
    }

    /**
     * @Route("/statistics/agent", name="statistics.agent", methods={"GET", "POST"})
     */
    public function agent(Request $request, Session $session){
        // Initialisation des variables :
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $statistiques_heures = $request->get('statistiques_heures');
        $statistiques_heures_defaut = $request->get('statistiques_heures_defaut');
        $post = $request->request->all();
        $dbprefix = $GLOBALS['dbprefix'];

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $post_agents = isset($post['agents']) ? $post['agents'] : null;
        $post_sites = isset($post['selectedSites']) ? $post['selectedSites'] : null;

        $joursParSemaine = $this->config('Dimanche') ? 7 : 6;
        $agent_tab = null;
        $exists_JF = false;
        $exists_absences = false;
        $exists_samedi = false;
        $exists_dimanche = false;

        $nbSites = $this->config('Multisites-nombre');

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
            $debut=$_SESSION['stat_debut'];
        }
        if (!$fin and array_key_exists('stat_fin', $_SESSION)) {
            $fin=$_SESSION['stat_fin'];
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

        // Filtre les agents
        if (!array_key_exists('stat_agents_agents', $_SESSION)) {
            $_SESSION['stat_agents_agents'] = null;
        }
        $agents = array();
        if ($post_agents) {
            foreach ($post_agents as $elem) {
                $agents[] = $elem;
            }
        } else {
            $agents = $_SESSION['stat_agents_agents'];
        }

        $_SESSION['stat_agents_agents'] = $agents;

        // Filtre les sites
        if (!array_key_exists('stat_agents_sites', $_SESSION)) {
            $_SESSION['stat_agents_sites'] = array();
        }

        $selectedSites = array();
        if ($post_sites) {
            foreach ($post_sites as $elem) {
                $selectedSites[] = $elem;
            }
        } else {
            $selectedSites = $_SESSION['stat_agents_sites'];
        }

        if ($nbSites > 1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }

        $_SESSION['stat_agents_sites'] = $selectedSites;

        // Filtre les sites dans les requêtes SQL
        if ($nbSites>1 and is_array($selectedSites)) {
            $sitesSQL = "0,".join(",", $selectedSites);
        } else {
            $sitesSQL = "0,1";
        }

        // Teleworking
        $teleworking_absence_reasons = array();
        $absences_reasons = $this->entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
        foreach ($absences_reasons as $elem) {
            $teleworking_absence_reasons[] = $elem->valeur();
        }

        $tab = array();

        $db = new \db();
        $db->select2("personnel", "*", array("actif"=>"Actif"), "ORDER BY `nom`,`prenom`");
        $agents_list=$db->result;

        if (!empty($agents)) {

            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB = $a->elements;

            //    Recherche du nombre de jours concernés
            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);
            $db->select("pl_poste", "`date`", "`date` BETWEEN '$debutREQ' AND '$finREQ' AND `site` IN ($sitesREQ)", "GROUP BY `date`;");
            $nbJours = $db->nb;

            //    Recherche des infos dans pl_poste et postes pour tous les agents sélectionnés
            //    On stock le tout dans le tableau $resultat
            $agents_select = join($agents, ",");
            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);
            $agentsREQ = $db->escapeString($agents_select);

            $req = "SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`,
                `{$dbprefix}pl_poste`.`date` as `date`, `{$dbprefix}pl_poste`.`perso_id` as `perso_id`,
                `{$dbprefix}pl_poste`.`poste` as `poste`, `{$dbprefix}pl_poste`.`absent` as `absent`,
                `{$dbprefix}pl_poste`.`site` as `site`,
                `{$dbprefix}postes`.`nom` as `poste_nom`, `{$dbprefix}postes`.`etage` as `etage`,
                `{$dbprefix}postes`.`teleworking` as `teleworking`
                FROM `{$dbprefix}pl_poste`
                INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}pl_poste`.`poste`=`{$dbprefix}postes`.`id`
                WHERE `{$dbprefix}pl_poste`.`date`>='$debutREQ' AND `{$dbprefix}pl_poste`.`date`<='$finREQ'
                AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}postes`.`statistiques`='1'
                AND `{$dbprefix}pl_poste`.`perso_id` IN ($agentsREQ) AND `{$dbprefix}pl_poste`.`site` IN ($sitesREQ)
                ORDER BY `poste_nom`,`etage`;";
            $db->query($req);
            $resultat = $db->result;

            //    Recherche des infos dans le tableau $resultat (issu de pl_poste et postes)
            //    pour chaques agents sélectionnés
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
                    foreach ($resultat as &$elem) {
                        if ($agent == $elem['perso_id']) {
                            // Vérifie à partir de la table absences si l'agent est absent
                            // S'il est absent, on met à 1 la variable $elem['absent']
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {
                                foreach ($absencesDB[$elem['perso_id']] as $a) {

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $elem['teleworking']) {
                                        continue;
                                    }

                                    if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                        $elem['absent'] = "1";
                                    }
                                }
                            }

                            if ($elem['absent'] != "1") {
                                // on compte les heures et les samedis pour lesquels l'agent n'est pas absent
                                // on créé un tableau par poste avec son nom, étage et la somme des heures faites par agent
                                if (!array_key_exists($elem['poste'], $postes)) {
                                    $postes[$elem['poste']] = array(
                                        $elem['poste'],
                                        $elem['poste_nom'],
                                        $elem['etage'],
                                        0,
                                        "site"=>$elem['site']
                                    );
                                }

                                // On compte toutes les heures pour ce poste (index 3)
                                $postes[$elem['poste']][3] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                // On compte les heures de chaque site
                                if ($nbSites>1) {
                                    $sites[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                }

                                // On compte toutes les heures (globales)
                                $heures += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $d = new \datePl($elem['date']);
                                if ($d->sam =="samedi") {    // tableau des samedis
                                    if (!array_key_exists($elem['date'], $samedi)) { // on stock les dates et la somme des heures faites par date
                                        $samedi[$elem['date']][0] = $elem['date'];
                                        $samedi[$elem['date']][1] = 0;
                                    }
                                    $samedi[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                    $exists_samedi = true;
                                }
                                if ($d->position == 0) {        // tableau des dimanches
                                    if (!array_key_exists($elem['date'], $dimanche)) {     // on stock les dates et la somme des heures faites par date
                                        $dimanche[$elem['date']][0] = $elem['date'];
                                        $dimanche[$elem['date']][1] = 0;
                                    }
                                    $dimanche[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                    $exists_dimanche = true;
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
                                    foreach ($statistiques_heures_tab as $key=>$h) {
                                        $tmp = heures($statistiques_heures_tab[$key]);
                                        if (!$tmp) {
                                            continue;
                                        }
                                        $tmp[]= heure3($tmp[0]).'-'.heure3($tmp[1]);
                                        if ($elem['debut'] == $tmp[0] and $elem['fin'] == $tmp[1]) {
                                            $heures_tab[$tmp[0].'-'.$tmp[1]][] = $elem['date'];
                                            if (!in_array($tmp, $heures_tab_global)) {
                                                $heures_tab_global[] = $tmp;
                                            }
                                        }
                                    }
                                }
                            } else {
                                // On compte les absences
                                if (!array_key_exists($elem['date'], $absences)) {
                                    $absences[$elem['date']][0] = $elem['date'];
                                    $absences[$elem['date']][1] = 0;
                                }
                                $absences[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $total_absences += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $exists_absences = true;
                            }

                            foreach ($agents_list as $elem2) {
                                if ($elem2['id'] == $agent) {    // on créé un tableau avec le nom et le prénom de l'agent.
                                    $agent_tab = array($agent, $elem2['nom'], $elem2['prenom']);
                                    break;
                                }
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
                                "sites"=>$sites);
                        }
                    }
                }
            }
        }

        // Heures et jours d'ouverture au public
        $s = new \statistiques();
        $s->debut = $debutSQL;
        $s->fin = $finSQL;
        $s->joursParSemaine = $joursParSemaine;
        $s->selectedSites = $selectedSites;
        $s->ouverture();
        $ouverture = $s->ouvertureTexte;

        sort($heures_tab_global);

        //passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;

        $selectedAgents = array();
        $multisites = array();

        if (is_array($agents_list)) {
            foreach ($agents_list as $elem) {
                $selected = null;
                if ($agents) {
                    $selectedAgents[] = in_array($elem['id'], $agents) ?? $elem;
                }
            }
        }

        if ($nbSites > 1){
            for ($i = 1 ; $i <= $nbSites; $i++) {
                $multisites[] = $this->config("Multisites-site$i");
            }
        }

        foreach ($tab as $key => $elem) {
            // Calcul des moyennes
            $jour = $tab[$key][2]/$nbJours;
            $hebdo = $jour*$joursParSemaine;

            $tab[$key][2] = heure4($tab[$key][2]);
            $tab[$key]['hebdo'] = heure4($hebdo);

            foreach ($tab[$key][1] as &$poste) {
                $site=null;
                if ($poste["site"]>0 and $nbSites>1) {
                    $site = $this->config("Multisites-site{$poste['site']}")." ";
                }
                $etage = $poste[2] ? $poste[2] : null;

                $siteEtage = ($site or $etage) ? "(".trim($site.$etage).")" : null;
                $tab[$key]["siteEtage"][$poste[0]]=$siteEtage;
                $poste[3] = heure4( $poste[3] );
            }

            if ($exists_samedi) {
                foreach ($tab[$key][3] as &$samedi) {
                    $samedi[0] = dateFr($samedi[0]);
                    $samedi[1] = heure4($samedi[1]);
                }
            }

            if ($exists_dimanche) {
                foreach ($tab[$key][6] as &$dimanche) {
                    $dimanche[0] = dateFr($dimanche[0]);
                    $dimanche[1] = heure4($dimanche[1]);
                }
            }


            if ($exists_JF) {
                sort($tab[$key][8]);
                foreach ($tab[$key][8] as &$ferie) {
                    $ferie[0] = dateFr($ferie[0]);
                    $ferie[1] = heure4($ferie[1]);
                }
            }

            if ($exists_absences) {
                $tab[$key][5] = heure4($tab[$key][5]);
                sort($tab[$key][4]);
                foreach ($tab[$key][4] as &$absence) {
                    $absence[0] = dateFr($absence[0]);
                    $absence[1] = heure4($absence[1]);
                }
            }

            foreach ($heures_tab_global as $v) {
                $tmp = $v[0].'-'.$v[1];
                if (!empty($tab[$key][7][$tmp])) {
                    sort($tab[$key][7][$tmp]);
                    foreach ($tab[$key][7][$tmp] as &$h) {
                        $h = dateFr($h);
                    }
                }
            }
            for ( $i = 1; $i <= $nbSites; $i++ ){
                if ($tab[$key]['sites'][$i]){
                    $tab[$key]['sites'][$i] = heure4($tab[$key]['sites'][$i]);
                }
            }
        }

        $this->templateParams(array(
            "debut" => $debut,
            "fin" => $fin,
            "agents_list" => $agents_list,
            "agents" => $agents,
            "statistiques_heures" => $statistiques_heures,
            "nbSites" => $nbSites,
            "selectedSites" => $selectedSites,
            "multisites" => $multisites,
            "ouverture" => $ouverture,
            "tab" => $tab,
            "exists_samedi" => $exists_samedi,
            "exists_dimanche" => $exists_dimanche,
            "exists_JF" => $exists_JF,
            "exists_absences" => $exists_absences,
            "heures_tab_global" => $heures_tab_global
        )
        );

        return $this->output('statistics/agent.html.twig');
    }

    /**
     * @Route("/statistics/time", name="statistics.time", methods={"GET", "POST"})
     */
    public function bytime(Request $request, Session $session)
    {
        //    Initialisation des variables
        $CSRFToken = trim($request->get("CSRFToken"));
        if (!$CSRFToken) {
            $CSRFToken = $GLOBALS['CSRFSession'];
        }

        $debut = $request->get("debut");
        if ($debut) {
            $fin = $request->get("fin");
            $selection_groupe = $request->get('selection_groupe');
            $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
            $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
            $selection_groupe = filter_var($selection_groupe, FILTER_CALLBACK, array("options"=>"sanitize_on"));

            $debut = dateSQL($debut);
            $fin = $fin ? dateFr($fin) : $debut;
        } elseif (array_key_exists("stat_temps_debut", $_SESSION['oups'])) {
            $debut = $_SESSION['oups']['stat_temps_debut'];
            $fin = $_SESSION['oups']['stat_temps_fin'];
            $selection_groupe = $_SESSION['oups']['stat_temps_selection_groupe'];
        } else {
           $date=$_SESSION['PLdate'];
           $d = new \datePl($date);
           $debut = $d->dates[0];
           $fin = $this->config('Dimanche') ? $d->dates[6] : $d->dates[5];
           $selection_groupe = false;
        }

        $_SESSION['oups']['stat_temps_debut'] = $debut;
        $_SESSION['oups']['stat_temps_fin'] = $fin;
        $_SESSION['oups']['stat_temps_selection_groupe'] = $selection_groupe;
        $current = $debut;

        while ($current<=$fin) {
            if (date("w", strtotime($current)) == 0 and !$this->config('Dimanche')) {

            } else {
                $dates[] = array($current,dateAlpha2($current));
            }

            $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
        }

        $debutFr = dateFr($debut);
        $finFr = dateFr($fin);
        $heures = array();  // Nombre total d'heures pour chaque jour
        $agents = array();  // Même chose avec le nombre d'agents
        $agents_id = array();   // Utilisé pour compter les agents présents chaque jour
        $nbAgents = array();  // Nombre d'agents pour chaque jour
        $tab = array();
        $nb = count($dates);  // Nombre de dates
        $nbSemaines = $nb/($this->config('Dimanche')? 7 : 6);   // Nombre de semaines
        $totalAgents = 0;        // Les totaux
        $totalHeures = 0;
        $siteHeures = array(0,0);   // Heures par site
        $siteAgents = array(0,0);   // Agents par site
        $multisites = [];

        // Affichage des statistiques par groupe de postes
        $groupes = array();
        $groupes_keys = array();
        $affichage_groupe = null;
        $totauxGroupesHeures = null;
        $totauxGroupesPerso = null;

        $p = new \postes();
        $p->fetch();
        // Rassemble les postes dans un tableau en fonction de leur groupe (ex: $groupe['pret'] = array(1,2,3))

        foreach ($p->elements as $poste) {
            $groupes[$poste['groupe']][] = $poste['id'];
        }

        $checked = null;
        if (!empty($groupes) and count($groupes)>1) {
            $checked = $selection_groupe ? "checked='checked'" : null;
            $affichage_groupe = "<span id='stat-temps-aff-grp'><input type='checkbox' value='on' id='selection_groupe' name='selection_groupe' $checked /><label for='selection_groupe'>Afficher les heures par groupe de postes</label></span>";
        }

        if ($affichage_groupe and $selection_groupe) {
            // $groupes_keys : nom des groupes
            $keys = array_keys($groupes);

            // Affichage des groupes selon l'ordre du menu déroulant
            $db = new \db();
            $db->select2('select_groupes', 'valeur', null, 'order by rang');
            if ($db->result) {
                foreach ($db->result as $elem) {
                    if (in_array($elem['valeur'], $keys)) {
                        $groupes_keys[] = $elem['valeur'];
                    }
                }
            }
            // Autres (les postes qui ne sont pas affectés à des groupes)
            if (in_array('', $keys)) {
                $groupes_keys[] = '';
            }

            // Initialisation des totaux (footer)
            foreach ($groupes_keys as $g) {
                $totauxGroupesHeures[$g] = 0;
                $totauxGroupesPerso[$g] = array();
            }
        }

        // Recherche des heures de SP à effectuer pour tous les agents pour toutes les semaines demandées
        $d = new \datePl($debut);
        $d1 = $d->dates[0];
        // Pour chaque semaine
        for ($d = $d1; $d <= $fin; $d = date("Y-m-d", strtotime($d."+1 week"))) {
            $heuresSP[$d] = calculHeuresSP($d, $CSRFToken);
            // déduction des absences
            if ($this->config('Planning-Absences-Heures-Hebdo')) {
                $a=new \absences();
                $a->CSRFToken = $CSRFToken;
                $heuresAbsences[$d] = $a->calculHeuresAbsences($d);
                foreach ($heuresAbsences[$d] as $key => $value) {
                    if (array_key_exists($key, $heuresSP[$d])) {
                        $heuresSP[$d][$key] = $heuresSP[$d][$key]-$value;
                        if ($heuresSP[$d][$key] < 0) {
                            $heuresSP[$d][$key] = 0;
                        }
                    }
                }
            }
        }
        // Calcul des totaux d'heures de SP à effectuer sur la période
        $totalSP = array();
        foreach ($heuresSP as $key => $value) {        // $key=date, $value=array
            foreach ($value as $key2 => $value2) {        // $key2=perso_id, $value2=heures
                if (!array_key_exists($key2, $totalSP)) {
                    $totalSP[$key2]=(float) $value2;
                } else {
                    $totalSP[$key2] += (float) $value2;
                }
            }
        }
        // Calcul des moyennes hebdomadaires de SP à effectuer
        $moyennesSP = array();
        foreach ($totalSP as $key => $value) {
            $moyennesSP[$key] = $value/(count($heuresSP));
        }


        // Recherche des absences dans la table absences
        $a = new \absences();
        $a->valide = true;
        $a->agents_supprimes = array(0,1,2);
        $a->fetch("`nom`,`prenom`,`debut`,`fin`", null, $debut." 00:00:00", $fin." 23:59:59");
        $absencesDB = $a->elements;

        $db = new \db();
        $debutREQ = $db->escapeString($debut);
        $finREQ = $db->escapeString($fin);
        $dbprefix = $GLOBALS['dbprefix'];

        $req = "SELECT `{$dbprefix}pl_poste`.`date` AS `date`, `{$dbprefix}pl_poste`.`debut` AS `debut`, ";
        $req.="`{$dbprefix}pl_poste`.`fin` AS `fin`, `{$dbprefix}personnel`.`id` AS `perso_id`, ";
        $req.="`{$dbprefix}pl_poste`.`site` AS `site`, `{$dbprefix}pl_poste`.`poste` AS `poste`, ";
        $req.="`{$dbprefix}personnel`.`nom` AS `nom`,`{$dbprefix}personnel`.`prenom` AS `prenom`, ";
        $req.="`{$dbprefix}personnel`.`statut` AS `statut` ";
        $req.="FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` ";
        $req.="INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}postes`.`id`=`{$dbprefix}pl_poste`.`poste` ";
        $req.="WHERE `date`>='$debutREQ' AND `date`<='$finREQ' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}postes`.`statistiques`='1' ";
        $req.="ORDER BY `nom`,`prenom`;";

        $db->query($req);
        
        if ($db->result) {
            foreach ($db->result as $elem) {
                // Vérifie à partir de la table absences si l'agent est absent
                // S'il est absent, on met à 1 la variable $elem['absent']
                foreach ($absencesDB as $a) {
                    if ($elem['perso_id'] == $a['perso_id'] and $a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                        continue 2;
                    }
                }
                if (!array_key_exists($elem['perso_id'], $tab)) {        // création d'un tableau de données par agent (id, nom, heures de chaque jour ...)
                    $tab[$elem['perso_id']] = array(
                        "perso_id"     => $elem['perso_id'],
                        "nom"          => $elem['nom'],
                        "prenom"       => $elem['prenom'],
                        "statut"       => $elem['statut'],
                        "site1"        => 0,
                        "site2"        => 0,
                        "total"        => 0,
                        "semaine"      => 0,
                        "groupe"       => array(),
                        "sites"        => array(),
                        "sitesSemaine" => array()
                    );
                    foreach ($dates as $d) {
                        $tab[$elem['perso_id']][$d[0]] = array('total'=>0);
                        if (!empty($groupes_keys)) {
                            foreach ($groupes_keys as $g) {
                                $tab[$elem['perso_id']][$d[0]]['groupe'][$g] = 0;
                            }
                        }
                    }

                    // Totaux par groupe de postes
                    foreach ($groupes_keys as $g){
                        $tab[$elem['perso_id']]['groupe'][$g] = 0;
                    }
                }

                $d = new \datePl($elem['date']);
                $position = $d->position != 0 ? $d->position-1 : 6;
                $tab[$elem['perso_id']][$elem['date']]['total']+=diff_heures($elem['debut'], $elem['fin'], "decimal");    // ajout des heures par jour
                $tab[$elem['perso_id']]['total']+=diff_heures($elem['debut'], $elem['fin'], "decimal");    // ajout des heures sur toutes la période
                if ($elem["site"]) {
                    if (!array_key_exists("site{$elem['site']}", $tab[$elem['perso_id']])) {
                        $tab[$elem['perso_id']]["site{$elem['site']}"] = 0;
                    }
                    $tab[$elem['perso_id']]["site{$elem['site']}"] += diff_heures($elem['debut'], $elem['fin'], "decimal");    // ajout des heures sur toutes la période par site
                    $tab[$elem['perso_id']]["site{$elem['site']}"] = $tab[$elem['perso_id']]["site{$elem['site']}"];
                }

                $totalHeures+=diff_heures($elem['debut'], $elem['fin'], "decimal");        // compte la somme des heures sur la période

                if (!array_key_exists($elem['site'], $siteHeures)) {
                    $siteHeures[$elem['site']] = 0;
                }
                $siteHeures[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                // Totaux par groupe de postes
                foreach ($groupes_keys as $g) {
                    if (in_array($elem['poste'], $groupes[$g])) {
                        $tab[$elem['perso_id']]['groupe'][$g] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                        $tab[$elem['perso_id']][$elem['date']]['groupe'][$g] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                        $totauxGroupesHeures[$g] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                        if (!in_array($elem['perso_id'], $totauxGroupesPerso[$g])) {
                            $totauxGroupesPerso[$g][] = $elem['perso_id'];
                        }
                    }

                }
            }
        }

        $nbSites = $this->config('Multisites-nombre');
        if ($nbSites >1){
            for($i = 1; $i <= $nbSites; $i++){
                $multisites[] = $this->config("Multisites-site$i");
            }
        }

        // Totaux par groupe de postes
        foreach ($groupes_keys as $g) {
            $totauxGroupesPerso[$g] = count($totauxGroupesPerso[$g]);
        }

        // pour chaque jour, on compte les heures et les agents
        foreach ($dates as $d) {
            $agents_id = array();
            if (is_array($tab)) {
                foreach ($tab as $elem) {
                    // on compte les heures de chaque agent
                    if (!array_key_exists($d[0], $agents)) {
                        $agents[$d[0]] = 0;
                    }
                    if (array_key_exists($d[0], $elem)) {
                        $agents[$d[0]]++;
                    }

                    // on compte le total d'heures par jour
                    if (!array_key_exists($d[0], $heures)) {
                        $heures[$d[0]] = 0;
                    }
                    if (array_key_exists($d[0], $elem)) {
                        $heures[$d[0]] += $elem[$d[0]]['total'];
                    }

                    // on compte les agents par jour + le total sur la période
                    if (!in_array($elem['perso_id'], $agents_id) and $elem[$d[0]]['total']){
                        $agents_id[] = $elem['perso_id'];
                        $totalAgents++;

                        for ($i = 1; $i <= $nbSites; $i++) {
                            if (array_key_exists("site$i", $elem)) {
                                if (!array_key_exists($i, $siteAgents)) {
                                    $siteAgents[$i] = 0;
                                }
                                $siteAgents[$i]++;
                            }
                        }
                    }
                }
            }
            // on compte les agents par jour (2ème partie)
            $nbAgents[$d[0]] = count($agents_id);
        }

        // Formatage des données pour affichage
        $keys = array_keys($tab);

        foreach ($keys as $key) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $tab[$key]["sitesSemaine"][] = array_key_exists("site{$i}", $tab[$key]) ? heure4(number_format($tab[$key]["site{$i}"] / $nbSemaines, 2, '.', ' ')) : "-";
                $tab[$key]["sites"][] = array_key_exists("site{$i}", $tab[$key]) ? heure4(number_format($tab[$key]["site{$i}"], 2, '.', ' ')) : "-";
            }

            foreach ($dates as $d) {
                if (!empty($groupes_keys)) {
                    foreach ($groupes_keys as $g) {
                        if ($tab[$key][$d[0]]["group_$g"]) {
                            $tab[$key]['groupe'][$g] = $elem[$d[0]]["groupe"][$g];
                        } else {
                            $tab[$key]['groupe'][$g] = null ;
                        }
                        $tab[$key][$d[0]]['groupe'][$g] = heure4($tab[$key][$d[0]]["groupe"][$g]);
                    }
                }
            }

            $tab[$key]['total'] = number_format($tab[$key]['total'], 2, '.', ' ');
            $tab[$key]['semaine'] = number_format($tab[$key]['total'] / $nbSemaines, 2, '.', ' ');      // ajout la moyenne par semaine

            if (!array_key_exists($key, $moyennesSP) or !is_numeric($moyennesSP[$key])) {
                $tab[$key]['heuresHebdo'] = "Erreur";
            } elseif ($moyennesSP[$key] > 0) {
                $tab[$key]['heuresHebdo'] = number_format($moyennesSP[$key], 2, '.', ' ');
            } else {
                $tab[$key]['heuresHebdo'] = 0;
            }

            if (!array_key_exists($key, $totalSP) or !is_numeric($totalSP[$key])) {
                $tab[$key]['max'] = "Erreur";
            } elseif ($totalSP[$key] > 0) {
                $tab[$key]['max'] = number_format($totalSP[$key], 2, '.', ' ');
            } else {
                $tab[$key]['max'] = 0;
            }

            $tab[$key]['diff1'] = floatval($tab[$key]['semaine'])- floatval($tab[$key]['heuresHebdo']);
            $tab[$key]['diff2'] = floatval($tab[$key]['heuresHebdo']) - floatval($tab[$key]['semaine']);

            $tab[$key]['heuresHebdo'] = heure4($tab[$key]['heuresHebdo']);
            $tab[$key]['max'] = heure4($tab[$key]['max']);
            $tab[$key]['semaine'] = heure4($tab[$key]['semaine']);
            $tab[$key]['total'] = heure4($tab[$key]['total']);

            foreach ($dates as $d) {
                $tab[$key][$d[0]]['total'] = $tab[$key][$d[0]]['total'] != 0 ? heure4(number_format($tab[$key][$d[0]]['total'], 2, '.', ' ')) : '-';
            }
        }

        foreach ($dates as $d) {
            if (array_key_exists($d[0], $heures)) {
                $heures[$d[0]] = $heures[$d[0]] != 0 ? heure4(number_format($heures[$d[0]], 2, '.', ' ')) : "-";
            } else {
                $heures[$d[0]] = "-";
            }

            if (array_key_exists($d[0], $nbAgents)) {
                $nbAgents[$d[0]] = $nbAgents[$d[0]] != 0 ? $nbAgents[$d[0]] : "-";
            } else {
                $nbAgents[$d[0]] = "-";
            }
        }
        $totalHeures = $totalHeures != 0 ? number_format($totalHeures, 2, '.', ' ') : "-";

        for ($i = 1; $i <= $nbSites; $i++) {
            if (array_key_exists($i, $siteHeures) and $siteHeures[$i] != 0) {
                $siteHeures[$i] = heure4(number_format($siteHeures[$i], 2, '.', ' '));
            } else {
                $siteHeures[$i] = "-";
            }
            if (array_key_exists($i, $siteAgents) and $siteAgents[$i]!=0) {
                $siteAgents[$i] = $siteAgents[$i];
            } else {
                $siteAgents[$i] = "-";
            } 
        }
        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;
        $_SESSION['stat_heures'] = $heures;
        $_SESSION['stat_agents'] = $agents;
        $_SESSION['stat_dates'] = $dates;
        $_SESSION['oups']['stat_totalHeures'] = $totalHeures;
        $_SESSION['oups']['stat_nbAgents'] = $nbAgents;
        $_SESSION['oups']['stat_groupes'] = $groupes_keys;
        $_SESSION['oups']['stat_groupesHeures'] = $totauxGroupesHeures;
        $_SESSION['oups']['stat_groupesPerso'] = $totauxGroupesPerso;

        $this->templateParams(array(
            'debutFr'             => $debutFr,
            'finFr'               => $finFr,
            'CSRFToken'           => $CSRFToken,
            'dates'               => $dates,
            'heures'              => $heures,
            'groupes'             => $groupes,
            'groupes_keys'        => $groupes_keys,
            'nbGroupes'           => count($groupes),
            'checked'             => $checked,
            'nbAgents'            => $nbAgents,
            'nbSites'             => $nbSites,
            'multisites'          => $multisites,
            'totauxGroupesHeures' => $totauxGroupesHeures,
            'totauxGroupesPerso'  => $totauxGroupesPerso,
            'emptystr'            => '',
            'nbSemaines'          => $nbSemaines,
            'tab'                 => $tab,
            'siteAgents'          => $siteAgents,
            'sitesHeures'         => $siteHeures,
            'totalAgents'         => $totalAgents,
            'totalHeures'         => heure4($totalHeures)
        ));
        return $this->output('statistics/time.html.twig');
    }
}
