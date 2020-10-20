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
            `{$dbprefix}postes`.`nom` as `poste_nom`, `{$dbprefix}postes`.`etage` as `etage`,
            `{$dbprefix}pl_poste`.`site` as `site` 
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
            foreach ($elem[3] as &$samedi) {			//	Affiche les dates et heures des samedis
                $samedi[0] = dateFr($samedi[0]);			//	date
                $samedi[1] = heure4($samedi[1]);	// heures
            }

            if ($this->config('Dimanche')) {
                foreach ($elem[6] as &$dimanche) {		//	Affiche les dates et heures des dimanches
                    $dimanche[0] = dateFr($dimanche[0]);		//	date
                    $dimanche[1] = heure4($dimanche[1]);	//	heures
                }
            }

            if ($exists_JF) {
                foreach ($elem[8] as &$ferie) {		// 	Affiche les dates et heures des jours fériés
                    $ferie[0] = dateFr($ferie[0]);			//	date
                    $ferie[1] = heure4($ferie[1]);	//	heures
                }
            }

            if ($exists_absences) {
                if ($elem[5]) {				//	Affichage du total d'heures d'absences
                    $elem[5] = heure4($elem[5]);
                }
                foreach ($elem[4] as &$absences) {		//	Affiche les dates et heures des absences
                    $absences[0] = dateFr($absences[0]);	//	date
                    $absences[1] = heure4($absences[1]);	// heures
                }
            }
            
            foreach ($heures_tab_global as $v) {
                if ($elem[7][$v[2]] and !empty($elem[7][$v[2]])) {
                    $count = array();
                    foreach ($elem[7][$v[2]] as $h) {
                        if (empty($count[$h])) {
                            $count[$h] = 1;
                        } else {
                            $count[$h]++;
                        }
                    }
                    $elem[7][$v[2]]["count"] = $count;
                    
                    foreach ($elem[7][$v[2]]["count"] as $k => $v2) {
                        $nk = dateFr($k);
                        $elem[7][$v[2]]["count"][$nk] = $elem[7][$v[2]]["count"][$k];
                        unset($elem[7][$v[2]]["count"][$k]);
                    }
                    ksort($elem[7][$v[2]]["count"]);
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
