<?php

namespace App\Controller;

use App\Controller\BaseController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

use App\Model\AbsenceReason;
use App\Model\Holiday;
use App\Model\PlanningPosition;
use App\Model\Position;
use App\Model\SelectFloor;
use App\Model\SelectGroup;
use App\PlanningBiblio\PresentSet;

$version = 'symfony';

include_once __DIR__ . "/../../public/conges/class.conges.php";
include_once __DIR__ . "/../../public/include/function.php";
require_once __DIR__ . "/../../public/include/db.php";
require_once __DIR__ . "/../../public/include/horaires.php";
include_once __DIR__ . '/../../public/statistiques/class.statistiques.php';
include_once __DIR__ . '/../../public/absences/class.absences.php';
include_once __DIR__ . '/../../public/planningHebdo/class.planningHebdo.php';
include_once __DIR__ . '/../../public/postes/class.postes.php';
include_once __DIR__ . '/../../public/personnel/class.personnel.php';

class StatisticController extends BaseController
{

    #[Route(path: '/statistics', name: 'statistics.index', methods: ['GET'])]
    public function index(Request $request, Session $session)
    {
        return $this->output('statistics/index.html.twig');
    }


    #[Route(path: '/statistics/agent', name: 'statistics.agent', methods: ['GET', 'POST'])]
    #[Route(path: '/statistics/service', name: 'statistics.service', methods: ['GET', 'POST'])]
    #[Route(path: '/statistics/status', name: 'statistics.status', methods: ['GET', 'POST'])]
    public function common(Request $request, Session $session)
    {
        // Initialization of variables
        $route = $request->attributes->get('_route');
        $type = str_replace('statistics.', '', $route);

        $data = array();
        $data_tab = null;
        $exists_absences = false;
        $exists_dimanche = false;
        $exists_JF = false;
        $exists_samedi = false;
        $heures_tab_global = array();
        $multisites = array();
        $statisticsHours = self::getHours($request);
        $tab = array();

        $dbprefix = $GLOBALS['dbprefix'];
        $nbSites = $this->config('Multisites-nombre');

        if ($nbSites > 1) {
            for ($i = 1 ; $i <= $nbSites; $i++) {
                $multisites[$i] = $this->config("Multisites-site$i");
            }
        }

        $debut = $request->get('debut');
        $fin = $request->get('fin');

        $debut = filter_var($debut, FILTER_CALLBACK, array('options' => 'sanitize_dateFr'));
        $fin = filter_var($fin, FILTER_CALLBACK, array('options' => 'sanitize_dateFr'));

        if (!$debut and array_key_exists('stat_debut', $_SESSION)) {
            $debut = $_SESSION['stat_debut'];
        }

        if (!$fin and array_key_exists('stat_fin', $_SESSION)) {
            $fin = $_SESSION['stat_fin'];
        }

        if (!$debut) {
            $debut = '01/01/' . date('Y');
        }

        if (!$fin) {
            $fin = date('d/m/Y');
        }

        $_SESSION['stat_debut'] = $debut;
        $_SESSION['stat_fin'] = $fin;

        $debutSQL = dateFr($debut);
        $finSQL = dateFr($fin);

        $post = $request->request->all();

        // Filter selected objects
        if (!array_key_exists("stat_{$type}_{$type}", $_SESSION)) {
            $_SESSION["stat_{$type}_{$type}"] = null;
        }

        $post_data = isset($post[$type]) ? $post[$type] : null;

        if ($post_data) {
            foreach ($post_data as $elem) {
                $data[] = $elem;
            }
        } else {
            $data = $_SESSION["stat_{$type}_{$type}"];
        }

        $_SESSION["stat_{$type}_{$type}"] = $data;

        // Filter sites
        if (!array_key_exists("stat_{$type}_sites", $_SESSION)) {
            $_SESSION["stat_{$type}_sites"] = array();
        }

        $selectedSites = array();
        $post_sites = isset($post['selectedSites']) ? $post['selectedSites'] : null;

        if ($post_sites) {
            foreach ($post_sites as $elem) {
                $selectedSites[] = $elem;
            }
        } else {
            $selectedSites = $_SESSION["stat_{$type}_sites"];
        }

        if ($nbSites > 1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }

        $_SESSION["stat_{$type}_sites"] = $selectedSites;

        // Filter sites for SQL queries
        if ($nbSites > 1 and is_array($selectedSites)) {
            $sitesSQL = '0,' . implode(',', $selectedSites);
        } else {
            $sitesSQL = '0,1';
        }

        // Teleworking
        $teleworking_absence_reasons = array();
        $absences_reasons = $this->entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
        foreach ($absences_reasons as $elem) {
            $teleworking_absence_reasons[] = $elem->valeur();
        }

        // Agents available
        $db = new \db();
        $db->select2('personnel', '*', array('id' => '<>2', 'actif' => 'Actif'), 'ORDER BY `nom`,`prenom`');
        $agents_list = $db->result;

        // Service and Status data
        if (in_array($type, ['service', 'status'])) {

            $table = match($type) { 
                'service' => 'select_services',
                'status' => 'select_statuts',
            };

            $field = match($type) { 
                'service' => 'service',
                'status' => 'statut',
            };

            $db = new \db();
            $db->select2($table);
            $objects = $db->result;

            foreach ($agents_list as $elem) {
                $id = null;
                foreach ($objects as $o) {
                    if ($o['valeur'] == html_entity_decode($elem[$field])) {
                        $id = $o['id'];
                        continue;
                    }
                }
                $agents[$elem['id']] = array(
                    'id'         => $elem['id'],
                    'object'    => html_entity_decode($elem[$field]),
                    'object_id' => $id
                );
            }
        }


        if (!empty($data)) {

            // Look for absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB = $a->elements;

            // Look for holidays
            $holidays = array();
            if ($this->config('Conges-Enable')) {
                $holidays = $this->entityManager->getRepository(Holiday::class)->get("$debutSQL 00:00:00", "$finSQL 23:59:59");
            }

            //  Get information from planning (tables pl_poste and postes)
            //  The result is store in $resultat
            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);

            if ($type == 'agent') {
                $agents_select = implode(",", $data);
                $agentsREQ = $db->escapeString($agents_select);
            }

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
                AND `{$dbprefix}pl_poste`.`site` IN ($sitesREQ)";

            if ($type == 'agent') {
                $req .= "AND `{$dbprefix}pl_poste`.`perso_id` IN ($agentsREQ)";
            }

            $req .= "ORDER BY `poste_nom`,`etage`;";

            $db->query($req);
            $resultat = $db->result;

            // Add service and status to $resultat for each agent
            if (in_array($type, ['service', 'status'])) {
                for ($i = 0; $i < count($resultat); $i++) {

                    if (!array_key_exists($resultat[$i]['perso_id'], $agents)) {
                        continue;
                    }

                    if (in_array($type, ['service', 'status'])) {
                        $resultat[$i]['object'] = $agents[$resultat[$i]['perso_id']]['object'];
                        $resultat[$i]['object_id'] = $agents[$resultat[$i]['perso_id']]['object_id'];
                    }
                }
            }

            $floors = array();
            $floorsRepository = $this->entityManager->getRepository(SelectFloor::class)->findAll();
            foreach ($floorsRepository as $f) {
                $floors[$f->id()] = $f->valeur();
            }

            // Get information from $resultat for each agent
            foreach ($data as $d) {

                // If $tab already contains information for the current agent, we retrieve this information to complete it.
                if (array_key_exists($d, $tab)) {
                    $heures = $tab[$d][2];
                    $total_absences = $tab[$d][5];
                    $samedi = $tab[$d][3];
                    $dimanche = $tab[$d][6];
                    $heures_tab = $tab[$d][7];
                    $absences = $tab[$d][4];
                    $feries = $tab[$d][8];
                    $sites = $tab[$d]["sites"];

                // If $tab does not contains information for the current agent, we create an entry with default values.
                // These entry is now created even if the agent is not found on schedules.
                } else {

                    if ($type == 'agent') {
                        // Create an array with agent information
                        foreach ($agents_list as $elem) {
                            if ($elem['id'] == $d) {

                                $assignedSites = json_decode($elem['sites']);
                                if (!is_array($assignedSites)) {
                                    $assignedSites = array();
                                }

                                $agent_tab = array(
                                    $d,
                                    $elem['nom'],
                                    $elem['prenom'],
                                    'assignedSites' => $assignedSites,
                                );
                                break;
                            }
                        }

                        $tab[$d][0] = $agent_tab;
                    }

                    $tab[$d][1] = array();
                    $heures = $tab[$d][2] = 0;
                    $total_absences = $tab[$d][5] = 0;
                    $samedi = $tab[$d][3] = array();
                    $dimanche = $tab[$d][6] = array();
                    $heures_tab = $tab[$d][7] = array();
                    $absences = $tab[$d][4] = array();
                    $feries = $tab[$d][8] = array();
                    $sites = array();
                    for ($i = 1; $i <= $nbSites; $i++) {
                        $sites[$i] = 0;
                    }
                    $tab[$d]["sites"] = $sites;
                }

                $postes = array();


                if (is_array($resultat)) {

                    foreach ($resultat as $elem) {

                        if (in_array($type, ['service', 'status'])) {
                            if (!isset($elem['object_id']) or $d != $elem['object_id']) {
                                continue;
                            }
                        }

                        if (($type =='agent' and !empty($elem['perso_id']) and $d == $elem['perso_id'])
                            or (in_array($type, ['service', 'status']) and $d == $elem['object_id'])) {

                            // Look for absences (from the absence table). Set $elem['absent'] to 1 if an absence is found.
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {
                                foreach ($absencesDB[$elem['perso_id']] as $a) {

                                    if (($this->config('Absences-Exclusion') == 1 and $a['valide'] == 99999)
                                        or $this->config('Absences-Exclusion') == 2)
                                    {
                                        continue;
                                    }

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $elem['teleworking']) {
                                        continue;
                                    }

                                    if ($a['debut'] < $elem['date'].' '.$elem['fin'] and $a['fin'] > $elem['date'].' '.$elem['debut']) {
                                        $elem['absent'] = '1';
                                    }
                                }
                            }

                            // Count holidays as absences
                            $elem = self::countHolidays($elem, $holidays, false);

                            if ($elem['absent'] != '1') {
                                // Count saturdays worked
                                // We store information in the $postes array with position name, floors and total hours
                                if (!array_key_exists($elem['poste'], $postes)) {
                                    $postes[$elem['poste']] = array(
                                        $elem['poste'],
                                        $elem['poste_nom'],
                                        $floors[$elem['etage']] ?? null,
                                        0,
                                        "site"=>$elem['site']
                                    );
                                }

                                // Count total hours of the position (index 3)
                                $postes[$elem['poste']][3] += diff_heures($elem['debut'], $elem['fin'], 'decimal');

                                // Count total hours of the site
                                if ($nbSites > 1) {
                                    $sites[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], 'decimal');
                                }

                                // Count total hours
                                $heures += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $date = new \datePl($elem['date']);

                                // Saturdays. We store dates and total hours
                                if ($date->sam == 'samedi') {
                                    if (!array_key_exists($elem['date'], $samedi)) {
                                        $samedi[$elem['date']][0] = $elem['date'];
                                        $samedi[$elem['date']][1] = 0;
                                    }
                                    $samedi[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                    $exists_samedi = true;
                                }

                                // Sundays. We store dates and total hours
                                if ($date->position == 0) {
                                    if (!array_key_exists($elem['date'], $dimanche)) {
                                        $dimanche[$elem['date']][0] = $elem['date'];
                                        $dimanche[$elem['date']][1] = 0;
                                    }
                                    $dimanche[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                    $exists_dimanche = true;
                                }

                                // Public holidays. We store dates and total hours
                                if (jour_ferie($elem['date'])) {
                                    if (!array_key_exists($elem['date'], $feries)) {
                                        $feries[$elem['date']][0] = $elem['date'];
                                        $feries[$elem['date']][1] = 0;
                                    }
                                    $feries[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                    $exists_JF = true;
                                }

                                // Statistics on specifics time slots
                                list($heures_tab, $heures_tab_global) = self::getHoursTables($heures_tab_global, $heures_tab, $elem, $statisticsHours);

                            } else {
                                // Absences
                                if (!array_key_exists($elem['date'], $absences)) {
                                    $absences[$elem['date']][0] = $elem['date'];
                                    $absences[$elem['date']][1] = 0;
                                }
                                $absences[$elem['date']][1] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $total_absences += diff_heures($elem['debut'], $elem['fin'], "decimal");
                                $exists_absences = true;
                            }

                            // We store all information (positions, agents, hours, absences, etc.) in $tab
                            if ($type == 'agent') {
                                $tab[$d] = array($agent_tab);
                            }

                            if (in_array($type, ['service', 'status'])) {
                                $tab[$d] = array(html_entity_decode($elem['object']));
                            }

                            $tab[$d] = array_merge($tab[$d], array(
                                $postes,
                                $heures,
                                $samedi,
                                $absences,
                                $total_absences,
                                $dimanche,
                                $heures_tab,
                                $feries,
                                "sites"=>$sites
                            ));
                        }
                    }
                }
            }

            // Remove the blanks 
            foreach ($tab as $key => $value) {
                if (empty($value[0])) {
                    unset($tab[$key]);
                }
            }

            sort($heures_tab_global);
    
            if ($type == 'agent') {
                // Remove agents who are never selected and not in selected sites
                if ($this->config('Multisites-nombre') > 1) {
                    foreach($tab as $key => $value) {
                        if (empty($value[1])
                            and empty(array_intersect($selectedSites, $value[0]['assignedSites']))) {
                            unset($tab[$key]);
                        }
                    }
                }
    
                // Agents who are never selected (for export)
                $neverSelected = array();
                foreach ($tab as $elem) {
                    if (empty($elem[1])) {
                        $neverSelected[] = $elem[0];
                    }
                }
    
                // passage en session du tableau pour le fichier export.php
                $_SESSION['stat_tab'] = array_merge($tab, array('neverSelected' => $neverSelected));
    
            } else {
                // passage en session du tableau pour le fichier export.php
                $_SESSION['stat_tab'] = $tab;
            }
    
            foreach ($tab as $key => $value) {
                // Calcul des moyennes
                $hebdo = \statistiques::average($value[2], $debut, $fin);
    
                $tab[$key][2] = heure4($value[2]);
                $tab[$key]['hebdo'] = heure4($hebdo);
    
                if ($nbSites > 1) {
                    for ($i = 1; $i <= $nbSites; $i++) {
                        if ($value["sites"][$i]) {
                            // Calcul des moyennes
                            $hebdo = \statistiques::average($value['sites'][$i], $debut, $fin);
                        }
                        $tab[$key]["sites"][$i] = heure4($value["sites"][$i]);
                        $tab[$key]["site_hebdo"][$i] = heure4($hebdo);
                    }
                }
    
                foreach ($tab[$key][1] as &$poste) {
                    $site = null;
    
                    if ($poste["site"] > 0 and $nbSites > 1) {
                        $site = $multisites[$poste['site']];
                    }
                    $etage = $poste[2] ? $poste[2] : null;
    
                    $siteEtage = ($site or $etage) ? '('.trim($site . ' ' . $etage).')' : null;
                    $poste['siteEtage'] = $siteEtage;
                    $poste[3] = heure4($poste[3]);
                }
    
    
                if ($exists_samedi) {
                    sort($tab[$key][3]);
    
                    foreach ($tab[$key][3] as &$samedi) {
                        $samedi[0] = dateFr($samedi[0]);
                        $samedi[1] = heure4($samedi[1]);
                    }
                }
    
                if ($exists_dimanche) {
                    sort($tab[$key][6]);
    
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
                    if (!empty($tab[$key][5])) {
                        $tab[$key][5] = heure4($tab[$key][5]);
                    }
    
                    sort($tab[$key][4]);
                    foreach ($tab[$key][4] as &$absence) {
                        $absence[0] = dateFr($absence[0]);
                        $absence[1] = heure4($absence[1]);
                    }
                }
    
                foreach ($heures_tab_global as $v) {
                    if (array_key_exists($v[2], $value[7]) and !empty($value[7][$v[2]])) {
                        sort($tab[$key][7][$v[2]]);
                        $count = array();
    
                        foreach ($tab[$key][7][$v[2]] as &$h) {
                            if (empty($count[$h])) {
                                $count[$h] = 1;
                            } else {
                                $count[$h]++;
                            }
                        }
                        $tab[$key][7][$v[2]]['count'] = $count;
                        ksort($tab[$key][7][$v[2]]['count']);
    
                        foreach ($tab[$key][7][$v[2]]['count'] as $k => $v2) {
                            $nk = dateFr($k);
                            $tab[$key][7][$v[2]]['count'][$nk] = $tab[$key][7][$v[2]]['count'][$k];
                            unset($tab[$key][7][$v[2]]['count'][$k]);
                        }
                    }
                }
            }
        }

        // Heures et jours d'ouverture au public
        $s = new \statistiques();
        $s->debut = $debutSQL;
        $s->fin = $finSQL;
        $s->selectedSites = $selectedSites;
        $s->ouverture();
        $ouverture = $s->ouvertureTexte;

        $this->templateParams(array(
            'debut' => $debut,
            'fin' => $fin,
            'statisticsHours' => $statisticsHours,
            'nbSites' => $nbSites,
            'selectedSites' => $selectedSites,
            'multisites' => $multisites,
            'ouverture' => $ouverture,
            'tab' => $tab,
            'exists_samedi' => $exists_samedi,
            'exists_dimanche' => $exists_dimanche,
            'exists_JF' => $exists_JF,
            'exists_absences' => $exists_absences,
            'heures_tab_global' => $heures_tab_global,
        ));
        
        if ($type == 'agent') {
            $this->templateParams(array(
                'agents' => $data,
                'agents_list' => $agents_list,
            ));
        }

        if (in_array($type, ['service', 'status'])) {
            $this->templateParams(array(
                'data' => $data,
                'objects' => $objects,
            ));
        }

        return $this->output("statistics/$type.html.twig");
    }


    #[Route(path: '/statistics/saturday', name: 'statistics.saturday', methods: ['GET', 'POST'])]
    public function saturday (Request $request, Session $session)
    {
        // Initialisation des variables :
        $debut = $request->get('debut');
        $fin = $request->get('fin');
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
        $statisticsHours = self::getHours($request);

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
        $dates = implode(",", $dates);

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
            $sitesSQL = "0,".implode(",", $selectedSites);
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

            // Look for holidays
            $holidays = array();
            if ($this->config('Conges-Enable')) {
                $holidays = $this->entityManager->getRepository(Holiday::class)->get("$debutSQL 00:00:00", "$finSQL 23:59:59");
            }

            //	Recherche des infos dans pl_poste et postes pour tous les agents sélectionnés
            //	On stock le tout dans le tableau $resultat
            $agents_select = implode(",", $agents);

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

                                    if (($this->config('Absences-Exclusion') == 1 and $a['valide'] == 99999)
                                        or $this->config('Absences-Exclusion') == 2)
                                    {
                                        continue;
                                    }

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $elem['teleworking']) {
                                        continue;
                                    }

                                    if ($a['debut'] < $elem['date'].' '.$elem['fin'] and $a['fin'] > $elem['date']." ".$elem['debut']) {
                                        continue 2;
                                    }
                                }
                            }

                            // Count holidays as absences
                            if (self::countHolidays($elem, $holidays)) {
                                continue;
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
                                list($heures_tab, $heures_tab_global) = self::getHoursTables($heures_tab_global, $heures_tab, $elem, $statisticsHours);

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
           
            foreach ($tab as &$elem) {
                // Calcul des moyennes
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
                foreach ($heures_tab_global as $v) {
                    $tmp = $v[0].'-'.$v[1];
                    if (!empty($elem[7][$tmp])) {
                        sort($elem[7][$tmp]);
                        foreach ($elem[7][$tmp] as &$h) {
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
                "statisticsHours"     => $statisticsHours,
                "tab"                 => $tab
            )
        );
        return $this->output('statistics/saturday.html.twig');
    }


    #[Route(path: '/statistics/attendeesmissing', name: 'statistics.attendeesmissing', methods: ['GET', 'POST'])]
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
            $absences->fetch("`nom`,`prenom`,`debut`,`fin`", null, $date, $date);
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

    #[Route(path: '/statistics/absence', name: 'statistics.absence', methods: ['GET', 'POST'])]
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
            $fin = dateFr($d->dates[6]);
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

                if (($this->config('Absences-Exclusion') == 1 and $elem['valide'] == 99999)
                    or $this->config('Absences-Exclusion') == 2)
                {
                    $formatted_start_date = date('Y-m-d', strtotime($elem['debut']));
                    $formatted_start_hour = date('H:i:s', strtotime($elem['debut']));
                    $formatted_end_date = date('Y-m-d', strtotime($elem['fin']));
                    $formatted_end_hour = date('H:i:s', strtotime($elem['fin']));

                    $db = new \db();
                    $db->select2('pl_poste', '*', array(
                            'perso_id' => $elem['perso_id'],
                            'date' => ">=$formatted_start_date AND date<=$formatted_end_date",
                            'debut' => "<$formatted_end_hour",
                            'fin' => ">$formatted_start_hour",
                        ),
                        'ORDER BY debut,fin'
                    );

                    if ($db->result) {
                        continue;
                    }
                }

                if (!in_array($elem['motif'], $motifs)) {
                    $motifs[] = $elem['motif'];
                }
            }
        }
        sort($motifs);

        // Recherche de tous les plannings de présence
        $edt = array();
        $ph = new \planningHebdo();
        $ph->debut = $debutSQL;
        $ph->fin = $finSQL;
        $ph->valide = true;
        $ph->fetch();
        if ($ph->elements and !empty($ph->elements)) {
            $edt = $ph->elements;
        }

        // Regroupe les absences par agent et par motif
        // Et ajoute les heures correspondantes
        $tab = array();
        $totaux = array("_general"=>0,"_generalHeures"=>0);
        foreach ($absences as $elem) {

            if (($this->config('Absences-Exclusion') == 1 and $elem['valide'] == 99999)
                or $this->config('Absences-Exclusion') == 2)
            {
                $formatted_start_date = date('Y-m-d', strtotime($elem['debut']));
                $formatted_start_hour = date('H:i:s', strtotime($elem['debut']));
                $formatted_end_date = date('Y-m-d', strtotime($elem['fin']));
                $formatted_end_hour = date('H:i:s', strtotime($elem['fin']));

                $db = new \db();
                $db->select2('pl_poste', '*', array(
                        'perso_id' => $elem['perso_id'],
                        'date' => ">=$formatted_start_date AND date<=$formatted_end_date",
                        'debut' => "<$formatted_end_hour",
                        'fin' => ">$formatted_start_hour",
                    ),
                    'ORDER BY debut,fin');

                if ($db->result) {
                    continue;
                }
            }

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
                $a->debut = $elem['debut'];
                $a->fin = $elem['fin'];
                $a->perso_id = $elem['perso_id'];
                $a->edt = $edt;
                $a->ignoreFermeture = true;
                $a->calculTemps2();
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
            $elem['totalHeures'] = is_numeric($elem['totalHeures']) ? heure4($elem['totalHeures']) : "Erreur";
            foreach ($motifs as $motif) {
                if (!empty($elem[$motif])) {
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
          "site"            => $site,
          "tab"             => $tab,
          "totaux"          => $totaux
        ));
        return $this->output('statistics/absence.html.twig');
    }

    #[Route(path: '/statistics/positionsummary', name: 'statistics.positionsummary', methods: ['GET', 'POST'])]
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
            $sitesSQL =" 0,".implode(",", $selectedSites);
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
        $postes_list = self::getPositions();

        if (!empty($postes)) {
            //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
            //	On stock le tout dans le tableau $resultat
            $postes_select = implode(",", $postes);
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

            // Look for holidays
            $holidays = array();
            if ($this->config('Conges-Enable')) {
                $holidays = $this->entityManager->getRepository(Holiday::class)->get("$debutSQL 00:00:00", "$finSQL 23:59:59");
            }

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
                    if ($elem->id() == $poste) {
                        $poste_tab = array($poste, $elem->nom(), $elem->etage(), $elem->obligatoire(), $elem->teleworking());
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

                                    if (($this->config('Absences-Exclusion') == 1 and $a['valide'] == 99999)
                                        or $this->config('Absences-Exclusion') == 2)
                                    {
                                        continue;
                                    }

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $poste_tab[4]) {
                                        continue;
                                    }

                                    if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                        continue 2;
                                    }
                                }
                            }

                            // Count holidays as absences
                            if (self::countHolidays($elem, $holidays)) {
                                continue;
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
                $jour = ($nbJours > 0) ? $elem[2] / $nbJours : 0;
                $hebdo = \statistiques::average($elem[2], $debut, $fin);
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
                    $siteEtage="(".implode(" ", $siteEtage).")";
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


    #[Route(path: '/statistics/time', name: 'statistics.time', methods: ['GET', 'POST'])]
    public function bytime(Request $request, Session $session)
    {
        //    Initialisation des variables
        $CSRFToken = trim($request->get("CSRFToken") ?? '');
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
           $fin = $d->dates[6];
           $selection_groupe = false;
        }

        $_SESSION['oups']['stat_temps_debut'] = $debut;
        $_SESSION['oups']['stat_temps_fin'] = $fin;
        $_SESSION['oups']['stat_temps_selection_groupe'] = $selection_groupe;
        $current = $debut;

        while ($current<=$fin) {
            $dates[] = array($current,dateAlpha2($current));
            $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
        }

        $debutFr = dateFr($debut);
        $finFr = dateFr($fin);
        $heures = array();  // Nombre total d'heures pour chaque jour
        $agents = array();  // Même chose avec le nombre d'agents
        $agents_id = array();   // Utilisé pour compter les agents présents chaque jour
        $nbAgents = array();  // Nombre d'agents pour chaque jour
        $tab = array();

        // Number of weeks
        $origin = new \DateTimeImmutable($debut);
        $target = new \DateTimeImmutable($fin);
        $interval = (int) $origin->diff($target)->format('%a') + 1;
        $nbSemaines = $interval / 7;

        $totalAgents = 0;        // Les totaux
        $totalHeures = 0;
        $siteHeures = array(0,0);   // Heures par site
        $siteAgents = array(0,0);   // Agents par site
        $multisites = [];

        // Affichage des statistiques par groupe de postes
        $groupes = array();
        $totauxGroupesHeures = array();
        $totauxGroupesPerso = array();

        $p = new \postes();
        $p->fetch();
        // Rassemble les postes dans un tableau en fonction de leur groupe (ex: $groupe['pret'] = array(1,2,3))

        foreach ($p->elements as $poste) {
            if (!empty($poste['groupe'])) {
                $groupes[$poste['groupe']][] = $poste['id'];
            } else {
                $groupes[-1][] = $poste['id'];
            }
        }

        $checked = $selection_groupe ? 'checked' : null;

        $keys = array_keys($groupes);

        // Affichage des groupes selon l'ordre du menu déroulant

        // Groups used on requested period
        $used_groups = array();

        // Groups assigned to at least one position
        $groups = $this->entityManager->getRepository(SelectGroup::class)->findBy(['id' => $keys], ['rang' => 'ASC']);
        if (!empty($groups)) {
            $other = new SelectGroup;
            $other->id(-1);
            $other->valeur('Autres');
            $groups[] = $other;
        }

        // Initialisation des totaux (footer)
        foreach ($groups as $g) {
            $totauxGroupesHeures[$g->id()] = 0;
            $totauxGroupesPerso[$g->id()] = array();
        }

        // Teleworking
        $teleworking_absence_reasons = array();
        $absences_reasons = $this->entityManager->getRepository(AbsenceReason::class)->findBy(array('teleworking' => 1));
        foreach ($absences_reasons as $elem) {
            $teleworking_absence_reasons[] = $elem->valeur();
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
                        $heuresSP[$d][$key] = (float) $heuresSP[$d][$key] - (float) $value;
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

        // Look for holidays
        $holidays = array();
        if ($this->config('Conges-Enable')) {
            $holidays = $this->entityManager->getRepository(Holiday::class)->get("$debut 00:00:00", "$fin 23:59:59");
        }

        $db = new \db();
        $debutREQ = $db->escapeString($debut);
        $finREQ = $db->escapeString($fin);
        $dbprefix = $GLOBALS['dbprefix'];

        $req = "SELECT `{$dbprefix}pl_poste`.`date` AS `date`, `{$dbprefix}pl_poste`.`debut` AS `debut`, ";
        $req.="`{$dbprefix}pl_poste`.`fin` AS `fin`, `{$dbprefix}personnel`.`id` AS `perso_id`, ";
        $req.="`{$dbprefix}pl_poste`.`site` AS `site`, `{$dbprefix}pl_poste`.`poste` AS `poste`, ";
        $req.="`{$dbprefix}personnel`.`nom` AS `nom`,`{$dbprefix}personnel`.`prenom` AS `prenom`, ";
        $req.="`{$dbprefix}personnel`.`statut` AS `statut`, ";
        $req.="`{$dbprefix}postes`.`teleworking` AS `teleworking` ";
        $req.="FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` ";
        $req.="INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}postes`.`id`=`{$dbprefix}pl_poste`.`poste` ";
        $req.="WHERE `date`>='$debutREQ' AND `date`<='$finREQ' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}postes`.`quota_sp`='1' ";
        $req.="ORDER BY `nom`,`prenom`;";

        $db->query($req);
        
        if ($db->result) {
            foreach ($db->result as $elem) {
                // Vérifie à partir de la table absences si l'agent est absent
                // S'il est absent, on met à 1 la variable $elem['absent']
                foreach ($absencesDB as $a) {

                    if (($this->config('Absences-Exclusion') == 1 and $a['valide'] == 99999)
                        or $this->config('Absences-Exclusion') == 2)
                    {
                        continue;
                    }


                    // Ignore teleworking absences for compatible positions
                    if (in_array($a['motif'], $teleworking_absence_reasons) and $elem['teleworking']) {
                        continue;
                    }

                    if ($elem['perso_id'] == $a['perso_id'] and $a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                        continue 2;
                    }
                }

                // Count holidays as absences
                if (self::countHolidays($elem, $holidays)) {
                    continue;
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
                        foreach ($groups as $g) {
                            $tab[$elem['perso_id']][$d[0]]['groupe'][$g->id()] = 0;
                        }
                    }

                    // Totaux par groupe de postes
                    foreach ($groups as $g){
                        $tab[$elem['perso_id']]['groupe'][$g->id()] = 0;
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
                foreach ($groups as $g) {
                    if (is_array($groupes[$g->id()]) and in_array($elem['poste'], $groupes[$g->id()])) {
                        $tab[$elem['perso_id']]['groupe'][$g->id()] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                        $tab[$elem['perso_id']][$elem['date']]['groupe'][$g->id()] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                        $totauxGroupesHeures[$g->id()] += diff_heures($elem['debut'], $elem['fin'], "decimal");

                        if (!in_array($g->id(), $used_groups)) {
                            $used_groups[] = $g->id();
                        }

                        if (!in_array($elem['perso_id'], $totauxGroupesPerso[$g->id()])) {
                            $totauxGroupesPerso[$g->id()][] = $elem['perso_id'];
                        }
                    }
                }
            }
        }

        // Delete groups which are not used on requested period
        foreach ($groups as $k => $v) {
            if (!in_array($v->id(), $used_groups)) {
                unset($groups[$k]);
            }
        }

        // No need to show groups if there is no more one item
        if (count($groups) < 2 ) {
            $groups = array();
        }

        $nbSites = $this->config('Multisites-nombre');
        if ($nbSites >1){
            for($i = 1; $i <= $nbSites; $i++){
                $multisites[] = $this->config("Multisites-site$i");
            }
        }

        // Totaux par groupe de postes
        foreach ($groups as $g) {
            $totauxGroupesPerso[$g->id()] = count($totauxGroupesPerso[$g->id()]);
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
                foreach ($groups as $g) {
                    $tab[$key][$d[0]]['groupe'][$g->id()] = heure4($tab[$key][$d[0]]["groupe"][$g->id()]);
                }
            }

            foreach ($tab[$key]['groupe'] as $k => $v) {
                $tab[$key]['groupe'][$k] = !empty($v) ? heure4($v) : '-';
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

        $totauxGroupesHeures = array_map('heure4', $totauxGroupesHeures);

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

        // Groups for export
        $group_keys = array();

        if ($selection_groupe and !empty($groups)) {
            foreach ($groups as $g) {
                $group_keys[] = array(
                    'id' => $g->id(), 
                    'name' => $g->valeur(),
                );
            }
        }

        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;
        $_SESSION['stat_heures'] = $heures;
        $_SESSION['stat_agents'] = $agents;
        $_SESSION['stat_dates'] = $dates;
        $_SESSION['oups']['stat_totalHeures'] = $totalHeures;
        $_SESSION['oups']['stat_nbAgents'] = $nbAgents;
        $_SESSION['oups']['stat_groupesHeures'] = $totauxGroupesHeures;
        $_SESSION['oups']['stat_groupesPerso'] = $totauxGroupesPerso;
        $_SESSION['oups']['stat_groupes'] = $group_keys;

        $this->templateParams(array(
            'debutFr'             => $debutFr,
            'finFr'               => $finFr,
            'CSRFToken'           => $CSRFToken,
            'dates'               => $dates,
            'heures'              => $heures,
            'groups'              => $checked ? $groups : [],
            'groups_exist'        => count($groups) > 1,
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

    #[Route(path: '/statistics/supportposition', name: 'statistics.supportposition', methods: ['GET', 'POST'])]
    public function supportposition(Request $request, Session $session)
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

        //		--------------		Initialisation  des variables 'debut','fin' et 'poste'		-------------------
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
        if (!array_key_exists('stat_postes_r', $_SESSION)) {
            $_SESSION['stat_postes_r'] = null;
        }

        $postes=array();
        if ($post_postes) {
            foreach ($post_postes as $elem) {
                $postes[] = $elem;
            }
        } else {
            $postes = $_SESSION['stat_postes_r'];
        }
        $_SESSION['stat_postes_r'] = $postes;

        // Filtre les sites
        if (!array_key_exists('stat_poste_sites', $_SESSION)) {
            $_SESSION['stat_poste_sites'] = array();
        }

        $selectedSites = array();
        if ($post_sites) {
            foreach ($post_sites as $elem) {
                $selectedSites[] = $elem;
            }
        } else {
            $selectedSites = $_SESSION['stat_poste_sites'];
        }

        $_SESSION['stat_postes_r'] = $postes;

        // Filtre les sites
        if (!array_key_exists('stat_poste_sites', $_SESSION)) {
            $_SESSION['stat_poste_sites'] = null;
        }

        if ($nbSites > 1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }

        $_SESSION['stat_poste_sites'] = $selectedSites;

        // Filtre les sites dans les requêtes SQL
        if ($nbSites > 1 and is_array($selectedSites)) {
            $sitesSQL = "0,".implode(",", $selectedSites);
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
        $selected = null;

        //		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
        $postes_list = self::getPositions(true);

        if (!empty($postes)) {
            //	Recherche du nombre de jours concernés
            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);
            $db->select("pl_poste", "`date`", "`date` BETWEEN '$debutREQ' AND '$finREQ' AND `site` IN ($sitesREQ)", "GROUP BY `date`;");
            $nbJours = $db->nb;

            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB = $a->elements;

            // Look for holidays
            $holidays = array();
            if ($this->config('Conges-Enable')) {
                $holidays = $this->entityManager->getRepository(Holiday::class)->get("$debutSQL 00:00:00", "$finSQL 23:59:59");
            }

            //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
            //	On stock le tout dans le tableau $resultat
            $postes_select = implode(",", $postes);

            $db = new \db();
            $debutREQ = $db->escapeString($debutSQL);
            $finREQ = $db->escapeString($finSQL);
            $sitesREQ = $db->escapeString($sitesSQL);
            $postesREQ = $db->escapeString($postes_select);

            $req = "SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
            `{$dbprefix}pl_poste`.`date` as `date`,  `{$dbprefix}pl_poste`.`poste` as `poste`, 
            `{$dbprefix}personnel`.`nom` as `nom`, `{$dbprefix}personnel`.`prenom` as `prenom`, 
            `{$dbprefix}personnel`.`id` as `perso_id`, `{$dbprefix}pl_poste`.site as `site` 
            FROM `{$dbprefix}pl_poste` 
            INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` 
            WHERE `{$dbprefix}pl_poste`.`date`>='$debutREQ' AND `{$dbprefix}pl_poste`.`date`<='$finREQ' 
            AND `{$dbprefix}pl_poste`.`poste` IN ($postesREQ) AND `{$dbprefix}pl_poste`.`absent`<>'1' 
            AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}pl_poste`.`site` IN ($sitesREQ) 
            ORDER BY `poste`,`date`,`debut`,`fin`;";
            $db->query($req);
            $resultat = $db->result;
        
            //	Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel)
            //	pour chaques postes sélectionnés
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
                    if ($elem->id() == $poste) {
                        $poste_tab = array($poste, $elem->nom(), $elem->etage(), $elem->obligatoire(), $elem->teleworking());
                        break;
                    }
                }

                $agents = array();
                $dates = array();
                if (is_array($resultat)) {
                    foreach ($resultat as $elem) {
                        // Vérifie à partir de la table absences si l'agent est absent
                        // S'il est absent : continue
                        if ( !empty($absencesDB[$elem['perso_id']]) ) {

                            foreach ($absencesDB[$elem['perso_id']] as $a) {

                                if (($this->config('Absences-Exclusion') == 1 and $a['valide'] == 99999)
                                    or $this->config('Absences-Exclusion') == 2)
                                {
                                    continue;
                                }

                                // Ignore teleworking absences for compatible positions
                                if (in_array($a['motif'], $teleworking_absence_reasons) and $poste_tab[4]) {
                                    continue;
                                }

                                if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                    continue 2;
                                }
                            }
                        }

                        // Count holidays as absences
                        if (self::countHolidays($elem, $holidays)) {
                            continue;
                        }

                        if ($poste == $elem['poste']) {
                            // on créé un tableau par date
                            if (!array_key_exists($elem['date'], $dates)) {
                                $dates[$elem['date']] = array($elem['date'],array(),0,"site"=>$elem['site']);
                            }
                            $dates[$elem['date']][1][] = array($elem['debut'],$elem['fin'],diff_heures($elem['debut'], $elem['fin'], "decimal"));
                            $dates[$elem['date']][2] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                            // On compte les heures de chaque site
                            if ($nbSites>1) {
                                $sites[$elem['site']] += diff_heures($elem['debut'], $elem['fin'], "decimal");
                            }
                            // On compte toutes les heures (globales)
                            $heures+=diff_heures($elem['debut'], $elem['fin'], "decimal");

                            //	On met dans tab tous les éléments (infos postes + agents + heures du poste)
                            $tab[$poste] = array($poste_tab, $dates, $heures, "sites"=>$sites);
                        }
                    }
                }
            }
        }

        // Heures et jours d'ouverture au public
        $s = new \statistiques();
        $s->debut = $debutSQL;
        $s->fin = $finSQL;
        $s->selectedSites=$selectedSites;
        $s->ouverture();
        $ouverture = $s->ouvertureTexte;

        //		-------------		Tri du tableau		------------------------------
        usort($tab, $tri);

        //	Passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;

        $siteEtage = array();
        $multisites = array();

        if ($nbSites>1) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }
        foreach ($tab as &$elem) {
            $siteEtage=array();
            if ($nbSites>1) {
                for ($i = 1; $i <= $nbSites; $i++) {
                    if ($elem["sites"][$i] == $elem[2]) {
                        $siteEtage[] = $multisites[$i];
                        continue;
                    }
                }
            }
            $elem[2] = heure4($elem[2]);
            // Etages
            if ($elem[0][2]) {
                $siteEtage[] = $elem[0][2];
            }

            if (!empty($siteEtage)) {
                $siteEtage = "(".implode(" ", $siteEtage).")";
            } else {
                $siteEtage = null;
            }
    
            $jour = ($nbJours > 0) ? floatval($elem[2]) / $nbJours : 0;
            $hebdo = \statistiques::average($elem[2], $debut, $fin);
            $elem["jour"] = heure4(round($jour, 2));
            $elem["hebdo"] = heure4(round($hebdo, 2));
            $elem["siteEtage"] = $siteEtage;
        
            if ($nbSites >1) {
                for ($i = 1; $i <= $nbSites; $i++) {
                    if ($elem["sites"][$i] and $elem["sites"][$i] != $elem[2]) {
                        // Calcul des moyennes
                        $hebdo = \statistiques::average($elem['sites'][$i], $debut, $fin);
                        $elem["sites"][$i] = heure4($elem["sites"][$i]);
                        $elem["site_hebdo"][$i] = heure4($hebdo);
                    }
                }
            }
            foreach ($elem[1] as &$date) {
                $date[3] = dateAlpha($date[0])." : ".heure4($date[2]);
                foreach ($date[1] as &$horaires) {
                    $horaires[3] = heure2($horaires[0])." - ".heure2($horaires[1])." : ".heure4($horaires[2]);
                }
            }
        }
        

        $this->templateParams(
            array(
                "debut"         => $debut,
                "fin"           => $fin,
                "nbSites"       => $nbSites,
                "ouverture"     => $ouverture,
                "postes_list"   => $postes_list,
                "postes"        => $postes,
                "multisites"    => $multisites,
                "selectedSites" => $selectedSites,
                "tab"           => $tab,
                "tri"           => $tri 
            )
        );    
        return $this->output('statistics/supportposition.html.twig');
    }

    #[Route(path: '/statistics/position', name: 'statistics.position', methods: ['GET', 'POST'])]
    public function position(Request $request, Session $session)
    {
        // Initialisation des variables :
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $tri = $request->get('tri');
        $post = $request->request->all();

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));

        $post_postes = isset($post['postes']) ? $post['postes'] : null;
        $post_sites = isset($post['selectedSites']) ? $post['selectedSites'] : null;

        $nbSites = $this->config('Multisites-nombre');

        if (!array_key_exists('stat_poste_postes', $_SESSION)) {
            $_SESSION['stat_poste_postes'] = null;
            $_SESSION['stat_poste_tri'] = null;
        }

        if (!$debut and array_key_exists("stat_debut", $_SESSION)) {
            $debut = $_SESSION['stat_debut'];
        }

        if (!$fin and array_key_exists("stat_fin", $_SESSION)) {
            $fin = $_SESSION['stat_fin'];
        }

        if (!$tri and array_key_exists("stat_poste_tri", $_SESSION)) {
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
        $postes = array();

        if ($post_postes) {
            foreach ($post_postes as $elem) {
                $postes[] = $elem;
            }
        } else {
            $postes = $_SESSION['stat_poste_postes'];
        }

        $_SESSION['stat_poste_postes'] = $postes;

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


        if ($nbSites > 1 and empty($selectedSites)) {
            for ($i = 1; $i <= $nbSites; $i++) {
                $selectedSites[] = $i;
            }
        }

        $multisites= [];
        if ($nbSites > 1){
            for ($i = 1; $i <= $nbSites; $i++){
                $multisites[$i] = $this->config("Multisites-site{$i}");
            }
        }

        $_SESSION['stat_poste_sites'] = $selectedSites;

        // Filtre les sites dans les requêtes SQL
        if ($nbSites > 1 and is_array($selectedSites)) {
            $sitesSQL = "0,".implode(",", $selectedSites);
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

        // Récupération des infos sur les agents
        $p = new \personnel();
        $p->supprime = array(0,1,2);
        $p->fetch();
        $agents_infos = $p->elements;

        //-------------- Récupération de la liste des postes pour le menu déroulant ------------------------
        $postes_list = self::getPositions();

        if (!empty($postes)) {
            //    Recherche du nombre de jours concernés
            $db = new \db();
            $db->select2("pl_poste", "date", array("date"=>"BETWEEN{$debutSQL}AND{$finSQL}", "site"=>"IN{$sitesSQL}"), "GROUP BY `date`;");
            $nbJours = $db->nb;

            // Recherche des absences dans la table absences
            $a = new \absences();
            $a->valide = true;
            $a->fetchForStatistics("$debutSQL 00:00:00", "$finSQL 23:59:59");
            $absencesDB = $a->elements;

            // Look for holidays
            $holidays = array();
            if ($this->config('Conges-Enable')) {
                $holidays = $this->entityManager->getRepository(Holiday::class)->get("$debutSQL 00:00:00", "$finSQL 23:59:59");
            }

            //    Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
            //    On stock le tout dans le tableau $resultat
            $postes_select = implode(",", $postes);
            $db = new \db();

            $db->selectInnerJoin(
                array("pl_poste","perso_id"),
                array("personnel","id"),
                array("debut","fin","date","poste","site"),
                array("nom","prenom",array("name"=>"id", "as"=>"perso_id")),
                array("date"=>"BETWEEN{$debutSQL}AND{$finSQL}", "poste"=>"IN{$postes_select}","absent"=>"<>1","supprime"=>"<>1", "site"=>"IN{$sitesSQL}"),
                array(),
                "ORDER BY `poste`,`nom`,`prenom`"
            );
            $resultat = $db->result;

            //    Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel)
            //    pour chaques postes sélectionnés
            foreach ($postes as $poste) {
                if (array_key_exists($poste, $tab)) {
                    $heures = $tab[$poste][2];
                    $sites = $tab[$poste]["sites"];
                } else {
                    $heures = 0;
                    for ($i = 1; $i<= $nbSites; $i++) {
                        $sites[$i] = 0;
                    }
                }
                $agents = array();
                $services = array();
                $statuts = array();

                // $poste_tab : table of positions with id, name, area, mandatory/reinforcement, teleworking
                foreach ($postes_list as $elem) {
                    if ($elem->id() == $poste) {
                        $poste_tab = array($poste, $elem->nom(), $elem->etage(), $elem->obligatoire(), $elem->teleworking());
                        break;
                    }
                }

                if (is_array($resultat)) {
                    foreach ($resultat as $elem) {
                        if ($poste == $elem['poste']) {
                            // Vérifie à partir de la table absences si l'agent est absent
                            // S'il est absent : continue
                            if ( !empty($absencesDB[$elem['perso_id']]) ) {
                                foreach ($absencesDB[$elem['perso_id']] as $a) {

                                    if (($this->config('Absences-Exclusion') == 1 and $a['valide'] == 99999)
                                        or $this->config('Absences-Exclusion') == 2)
                                    {
                                        continue;
                                    }

                                    // Ignore teleworking absences for compatible positions
                                    if (in_array($a['motif'], $teleworking_absence_reasons) and $poste_tab[4]) {
                                        continue;
                                    }

                                    if ($a['debut'] < $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date']." ".$elem['debut']) {
                                        continue 2;
                                    }
                                }
                            }

                            // Count holidays as absences
                            if (self::countHolidays($elem, $holidays)) {
                                continue;
                            }

                            // on créé un tableau par agent avec son nom, prénom et la somme des heures faites par poste
                            if (!array_key_exists($elem['perso_id'], $agents)) {
                                $agents[$elem['perso_id']] = array($elem['perso_id'], $elem['nom'], $elem['prenom'], 0, "site" => $elem['site']);
                            }
                            $agents[$elem['perso_id']][3] = floatval($agents[$elem['perso_id']][3]) + diff_heures($elem['debut'], $elem['fin'], "decimal");

                            // On compte les heures de chaque site
                            if ($nbSites > 1) {
                                $sites[$elem['site']] = floatval($sites[$elem['site']]) + diff_heures($elem['debut'], $elem['fin'], "decimal");
                            }

                            // On compte toutes les heures (globales)
                            $heures = floatval($heures) + diff_heures($elem['debut'], $elem['fin'], "decimal");

                            // On créé un tableau par service
                            if (array_key_exists($elem['perso_id'], $agents_infos)) {
                                $service = $agents_infos[$elem['perso_id']]['service'];
                            }
                            $service = isset($service) ? $service : "ZZZ_Autre";
                            if (!array_key_exists($service, $services)) {
                                $services[$service] = array("nom"=>$service,"heures"=>0);
                            }
                            $services[$service]["heures"] = floatval($services[$service]["heures"]) + diff_heures($elem['debut'], $elem['fin'], "decimal");

                            // On créé un tableau par statut
                            if (array_key_exists($elem['perso_id'], $agents_infos)) {
                                $statut = $agents_infos[$elem['perso_id']]['statut'];
                            }
                            $statut = isset($statut) ? $statut : "ZZZ_Autre";
                             if (!array_key_exists($statut, $statuts)) {
                                $statuts[$statut] = array("nom" => $statut, "heures" => 0);
                            }
                            $statuts[$statut]["heures"] = floatval($statuts[$statut]["heures"]) + diff_heures($elem['debut'], $elem['fin'], "decimal");

                            // On met dans tab tous les éléments (infos postes + agents + heures du poste)
                            $tab[$poste] = array($poste_tab, $agents, $heures, "services" => $services, "statuts" => $statuts, "sites" => $sites);
                        }
                    }
                }
            }
        }

        // Heures et jours d'ouverture au public
        $s = new \statistiques();
        $s->debut = $debutSQL;
        $s->fin = $finSQL;
        $s->selectedSites = $selectedSites;
        $s->ouverture();
        $ouverture = $s->ouvertureTexte;

        //        -------------        Tri du tableau        ------------------------------
        usort($tab, $tri);

        // passage en session du tableau pour le fichier export.php
        $_SESSION['stat_tab'] = $tab;

        if($tab){
            foreach($tab as $key => $elem){
                $siteEtage = array();
                if ($nbSites >1) {
                    for ($i = 1; $i <= $nbSites; $i++) {
                        if ($tab[$key]["sites"][$i] == $tab[$key][2]) {
                            $siteEtage[] = $this->config("Multisites-site{$i}");
                            continue;
                        }
                    }
                }
                if ($tab[$key][0][2]) {
                    $siteEtage[] = $tab[$key][0][2];
                }
                if (!empty($siteEtage)) {
                    $siteEtage="(".implode(" ", $siteEtage).")";
                } else {
                    $siteEtage=null;
                }
                $jour = ($nbJours > 0) ? $tab[$key][2] / $nbJours : 0;
                $hebdo = \statistiques::average($tab[$key][2], $debut, $fin);

                if ($nbSites>1) {
                    for ($i = 1 ; $i <= $nbSites; $i++) {
                        $total = $tab[$key]["sites"][$i];
                        $average = \statistiques::average($total, $debut, $fin);
                        $tab[$key]["sites"][$i] = array(
                            'total' => $total,
                            'average' => $average
                        );
                    }
                }

                $tab[$key]["jour"] = $jour;
                $tab[$key]["hebdo"] = $hebdo;
                $tab[$key]["siteEtage"] = $siteEtage;
            }

            foreach ($tab[$key][1] as $agent) {
                $agent[3] = heure4($agent[3]);
            }

            sort($tab[$key]['services']);
            sort($tab[$key]['statuts']);

            foreach ($tab[$key]['services'] as &$service) {
                $service['nom'] = str_replace("ZZZ_", "", $service['nom']);
                $service['heures'] = heure4($service['heures']);
            }
            foreach ($tab[$key]['statuts'] as &$statut) {
                $statut['nom'] = str_replace("ZZZ_", "", $statut['nom']);
                $statut['heures']= heure4($statut['heures']);
            }

        }

        $this->templateParams(array(
            "debut" => $debut,
            "fin" => $fin,
            "multisites" => $multisites,
            "nbSites" => $nbSites,
            "ouverture" => $ouverture,
            "postes" => $postes,
            "postes_list" => $postes_list,
            "selectedSites" => $selectedSites,
            "tab" => $tab,
            "tri" => $tri
        ));
        return $this->output('statistics/position.html.twig');
    }


    /**
     * Count holidays as absences
     */
    private static function countHolidays($elem, $holidays, $continue = true)
    {
        if (!empty($holidays)) {
            $start = \DateTime::createFromFormat('Y-m-d H:i:s', $elem['date'] . ' ' . $elem['debut']);
            $end = \DateTime::createFromFormat('Y-m-d H:i:s', $elem['date'] . ' ' . $elem['fin']);

            foreach ($holidays as $holiday) {
                if ($holiday->perso_id() == $elem['perso_id'] 
                    and $holiday->debut() < $end
                    and $holiday->fin() > $start) {

                    if ($continue) {
                        return true;
                    }

                    $elem['absent'] = "1";
                }
            }
        }

        if ($continue) {
            return false;
        }

        return $elem;
    }


    /**
     * Init and give Statistics Hours
     */
    private function getHours($request)
    {
        $session = $request->getSession();
        $hours = $request->get('statisticsHours');

        if ($request->isMethod('post')) {
            $session->set('statisticsInit', true);
            $session->set('statisticsHours', $hours);
        }

        if (!$hours) {
            if ($session->get('statisticsInit')) {
                $hours = $session->get('statisticsHours');
            } else { 
                $hours = $this->config('Statistiques-Heures');
            }
        }

        return $hours;
    }

    /**
     * Give Hours Table
     */
    private function getHoursTables($heures_tab_global, $heures_tab, $elem, $statisticsHours)
    {
        if (!$statisticsHours) {
            return array($heures_tab, $heures_tab_global);
        }

        $statisticsHoursTab = explode(';', $statisticsHours);

        foreach ($statisticsHoursTab as $key => $h) {
            $tmp = heures($statisticsHoursTab[$key]);
            if (!$tmp) {
                continue;
            }

            if ($elem['debut'] == $tmp[0] and $elem['fin'] == $tmp[1]) {
                $tmp[2] = heure3($tmp[0])."-".heure3($tmp[1]);
                $heures_tab[$tmp[2]][] = $elem['date'];
                $heures_tab[$tmp[0] . '-' . $tmp[1]][] = $elem['date'];
                if (!in_array($tmp, $heures_tab_global)) {
                    $heures_tab_global[] = $tmp;
                }
            }
        }
        return array($heures_tab, $heures_tab_global);
    }


    /**
     * Give used positions
     */
    private function getPositions($supportOnly = false)
    {

        $filter = array('statistiques' => 1);
        if ($supportOnly) {
            $filter['obligatoire'] = 'Renfort';
        }

        $positions = $this->entityManager->getRepository(Position::class)->findBy($filter);

        $floors = $this->entityManager->getRepository(SelectFloor::class);

        // Find used positions (we do not want to show positions that are never used)
        $usedPositions = array();
        $plannings = $this->entityManager->getRepository(PlanningPosition::class)->getPositions();

        foreach ($plannings as $elem) {
            if (!in_array($elem->poste(), $usedPositions)) {
                $usedPositions[] = $elem->poste();
            }
        }

        $result = array();

        foreach ($positions as $elem) {
            // Keep only used positions
            if (!in_array($elem->id(), $usedPositions)) {
                continue;
            }

            // Add floor information
            $elem->etage($floors->find($elem->etage()) ? $floors->find($elem->etage())->valeur() : null);
            $result[] = $elem;
        }

        return $result;
    }

}
