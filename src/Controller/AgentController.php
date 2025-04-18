<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Event\OnTransformLeaveDays;
use App\PlanningBiblio\Event\OnTransformLeaveHours;
use App\PlanningBiblio\Helper\HolidayHelper;
use App\PlanningBiblio\Helper\HourHelper;
use App\PlanningBiblio\Ldif2Array;

use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . "/../../public/personnel/class.personnel.php");
require_once(__DIR__ . "/../../public/activites/class.activites.php");
require_once(__DIR__ . "/../../public/planningHebdo/class.planningHebdo.php");
require_once(__DIR__ . "/../../public/conges/class.conges.php");
require_once(__DIR__ . "/../../public/ldap/class.ldap.php");

class AgentController extends BaseController
{

    #[Route(path: '/agent', name: 'agent.index', methods: ['GET'])]
    public function index(Request $request)
    {
        $session = $request->getSession();

        $actif = $request->get('actif');
        $lang = $GLOBALS['lang'];
        $droits = $GLOBALS['droits'];
        $login_id = $session->get('loginId');

        $ldapBouton = ($this->config('LDAP-Host') and $this->config('LDAP-Suffix'));
        $ldifBouton = ($this->config('LDIF-File'));

        if (!$actif) {
            $actif = isset($_SESSION['perso_actif']) ? $_SESSION['perso_actif'] : 'Actif';
        }

        $_SESSION['perso_actif'] = $actif;

        //        Suppression des agents dont la date de départ est passée        //
        $tab = array(0);
        $db = new \db();
        $db->CSRFToken = $GLOBALS['CSRFSession'];
        $db->update('personnel', array('supprime'=>'1', 'actif'=>'Supprim&eacute;'), "`depart`<CURDATE() AND `depart`<>'0000-00-00' and `actif` NOT LIKE 'Supprim%'");


        $p = new \personnel();
        $p->supprime = strstr($actif, "Supprim") ? array(1) : array(0);
        $p->fetch("nom,prenom", $actif);
        $agentsTab = $p->elements;

        $nbSites = $this->config('Multisites-nombre');

        $agents = array();
        foreach ($agentsTab as $agent) {
            $elem = [];
            $id = $agent['id'];

            $arrivee = dateFr($agent['arrivee']);
            $depart = dateFr($agent['depart']);
            $last_login = date_time($agent['last_login']);
            $heures = $agent['heures_hebdo'] ? $agent['heures_hebdo'] : null;
            $heures = heure4($heures);
            if (is_numeric($heures)) {
                $heures.= "h00";
            }
            $agent['service'] = str_replace("`", "'", $agent['service']);

            $sites = $agent['sites'];

            if ($nbSites > 1) {
                $tmp = array();
                if (!empty($agent['sites'])) {
                    foreach ($agent['sites'] as $site) {
                        if ($site) {
                            $tmp[] = $this->config("Multisites-site{$site}");
                        }
                    }
                }
                $sites = !empty($tmp)?implode(", ", $tmp):null;
            }

            $elem = array(
                'id' => $id,
                'name' => $agent['nom'],
                'surname' => $agent['prenom'],
                'departure' => $depart,
                'arrival' => $arrivee,
                'status' => $agent['statut'],
                'service' => $agent['service'],
                'hours' => $heures,
                'last_login' => $last_login,
                'sites' => $sites,
            );
            $agents[]= $elem;
        }

        $db = new \db();
        $db->select2("select_statuts", null, null, "order by rang");
        $statuts = $db->result;

        $contrats = array("Titulaire","Contractuel");

        // Liste des services
        $services = array();
        $db = new \db();
        $db->select2("select_services", null, null, "ORDER BY `rang`");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $services[]=$elem;
            }
        }

        $hours = array();
        for ($i = 1 ; $i < 40; $i++) {
            if ($this->config('Granularite') == 5) {
                $hours[] = array($i,$i."h00");
                $hours[] = array($i.".08",$i."h05");
                $hours[] = array($i.".17",$i."h10");
                $hours[] = array($i.".25",$i."h15");
                $hours[] = array($i.".33",$i."h20");
                $hours[] = array($i.".42",$i."h25");
                $hours[] = array($i.".5",$i."h30");
                $hours[] = array($i.".58",$i."h35");
                $hours[] = array($i.".67",$i."h40");
                $hours[] = array($i.".75",$i."h45");
                $hours[] = array($i.".83",$i."h50");
                $hours[] = array($i.".92",$i."h55");
            } elseif ($this->config('Granularite')==15) {
                $hours[] = array($i,$i."h00");
                $hours[] = array($i.".25",$i."h15");
                $hours[] = array($i.".5",$i."h30");
                $hours[] = array($i.".75",$i."h45");
            } elseif ($this->config('Granularite')==30) {
                $hours[] = array($i,$i."h00");
                $hours[] = array($i.".5",$i."h30");
            } else {
                $hours[] = array($i,$i."h00");
            }
        }

        // Toutes les activités
        $a = new \activites();
        $a->fetch();
        $activites = $a->elements;

        $postes_completNoms = array();
        foreach ($activites as $elem) {
            $postes_completNoms[] = array($elem['nom'],$elem['id']);
        }
        $postes_completNoms_json = json_encode($postes_completNoms);

        $this->templateParams(array(
            "agents"                 => $agents,
            "actif"                  => $actif,
            "contracts"              => $contrats,
            "hours"                  => $hours,
            "lang"                   => $lang,
            "ldapBouton"             => $ldapBouton,
            "ldifBouton"             => $ldifBouton,
            "login_id"               => $login_id,
            "nbSites"                => $nbSites,
            "positionsCompleteNames" => $postes_completNoms_json,
            "rights21"               => in_array(21, $droits),
            "services"               => $services,
            "skills"                 => $activites,
            "status"                 => $statuts

        ));
        return $this->output('/agents/index.html.twig');
    }

    #[Route(path: '/agent/password', name: 'agent.password', methods: ['GET'])]
    public function password(Request $request)
    {
        return $this->output('/agents/password.html.twig');
    }

    #[Route(path: '/agent/add', name: 'agent.add', methods: ['GET'])]
    #[Route(path: '/agent/{id<\d+>}', name: 'agent.edit', methods: ['GET'])]
    public function add(Request $request)
    {
        $id = $request->get('id');
        $CSRFSession = $GLOBALS['CSRFSession'];
        $lang = $GLOBALS['lang'];
        $currentTab = '';
        global $temps;
        global $breaktimes;

        $actif = null;
        $droits = $GLOBALS['droits'];
        $admin = in_array(21, $droits) ? true : false;

        $db_groupes = new \db();
        $db_groupes->select2("acces", array("groupe_id", "groupe", "categorie", "ordre"), "groupe_id not in (99,100)", "group by groupe");

        // Tous les droits d'accés
        $groupes = array();
        if ($db_groupes->result) {
            foreach ($db_groupes->result as $elem) {
                if (empty($elem['categorie'])) {
                    $elem['categorie'] = 'Divers';
                    $elem['ordre'] = '200';
                }
                $groupes[$elem['groupe_id']] = $elem;
            }
        }

        uasort($groupes, 'cmp_ordre');

        // PlanningHebdo et EDTSamedi étant incompatibles, EDTSamedi est désactivé
        // si PlanningHebdo est activé
        if ($this->config('PlanningHebdo')) {
            $this->config('EDTSamedi', 0);
        }

        // Si multisites, les droits de gestion des absences,
        // congés et modification planning dépendent des sites :
        // on les places dans un autre tableau pour simplifier l'affichage
        $groupes_sites = array();

        if ($this->config('Multisites-nombre') > 1) {
            for ($i = 2; $i <= 10; $i++) {

                // Exception, groupe 701 = pas de gestion multisites (pour le moment)
                if ($i == 7) {
                    continue;
                }

                $groupe = ($i * 100) + 1 ;
                if (array_key_exists($groupe, $groupes)) {
                    $groupes_sites[] = $groupes[$groupe];
                    unset($groupes[$groupe]);
                }
            }
        }

        uasort($groupes_sites, 'cmp_ordre');


        $db = new \db();
        $db->select2("select_statuts", null, null, "order by rang");
        $statuts = $db->result;
        $db = new \db();
        $db->select2("select_categories", null, null, "order by rang");
        $categories = $db->result;
        $db = new \db();
        $db->select2("personnel", "statut", null, "group by statut");
        $statuts_utilises = array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $statuts_utilises[] = $elem['statut'];
            }
        }

        // Liste des services
        $services = array();
        $db = new \db();
        $db->select2("select_services", null, null, "ORDER BY `rang`");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $services[] = $elem;
            }
        }

        // Liste des services utilisés
        $services_utilises = array();
        $db = new \db();
        $db->select2('personnel', 'service', null, "GROUP BY `service`");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $services_utilises[] = $elem['service'];
            }
        }

        $acces = array();
        $postes_attribues = array();
        $recupAgents = array("Prime","Temps");

        // récupération des infos de l'agent en cas de modif
        $ics = null;
        if ($id) {
            $db = new \db();
            $db->select2("personnel", "*", array("id"=>$id));
            $actif = $db->result[0]['actif'];
            $nom = $db->result[0]['nom'];
            $prenom = $db->result[0]['prenom'];
            $mail = $db->result[0]['mail'];
            $statut = $db->result[0]['statut'];
            $categorie = $db->result[0]['categorie'];
            $check_hamac = $db->result[0]['check_hamac'];
            $mSGraphCheck = $db->result[0]['check_ms_graph'];
            $check_ics = json_decode($db->result[0]['check_ics'], true);
            $service = $db->result[0]['service'];
            $heuresHebdo = $db->result[0]['heures_hebdo'];
            $heuresTravail = $db->result[0]['heures_travail'];
            $arrivee = dateFr($db->result[0]['arrivee']);
            $depart = dateFr($db->result[0]['depart']);
            $login = $db->result[0]['login'];
            $breaktimes = array();
            if ($this->config('PlanningHebdo')) {
                $p = new \planningHebdo();
                $p->perso_id = $id;
                $p->debut = date("Y-m-d");
                $p->fin = date("Y-m-d");
                $p->valide = true;
                $p->fetch();
                if (!empty($p->elements)) {
                    $temps = $p->elements[0]['temps'];
                    $breaktimes = $p->elements[0]['breaktime'] ?? array();
                } else {
                    $temps = array();
                }
            } else {
                $temps = json_decode(html_entity_decode($db->result[0]['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                if (!is_array($temps)) {
                    $temps = array();
                }
            }
            // Decimal breaktime to time (H:i).
            foreach ($breaktimes as $index => $time) {
                $breaktimes[$index] = $breaktimes[$index]
                    ? gmdate('H:i', floor($breaktimes[$index] * 3600)) : '';
            }

            $postes_attribues = json_decode(html_entity_decode($db->result[0]['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            if (is_array($postes_attribues)) {
                sort($postes_attribues);
            }
            $acces = json_decode(html_entity_decode($db->result[0]['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            $matricule = $db->result[0]['matricule'];
            $url_ics = $db->result[0]['url_ics'];
            $mailsResponsables = explode(";", html_entity_decode($db->result[0]['mails_responsables'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
            // $mailsResponsables : html_entity_decode necéssaire sinon ajoute des espaces après les accents ($mailsResponsables=implode("; ",$mailsResponsables);)
            $informations = stripslashes($db->result[0]['informations']);
            $recup = stripslashes($db->result[0]['recup']);
            $sites = html_entity_decode($db->result[0]['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $sites = $sites?json_decode($sites, true):array();
            $action = "modif";
            $titre = $nom." ".$prenom;

            // URL ICS
            if ($this->config('ICS-Export')) {
                $p = new \personnel();
                $p->CSRFToken = $CSRFSession;
                $ics = $p->getICSURL($id);
            }
        } else {// pas d'id, donc ajout d'un agent
            $id = null;
            $nom = null;
            $prenom = null;
            $mail = null;
            $statut = null;
            $categorie = null;
            $check_hamac = 0;
            $mSGraphCheck = 0;
            $check_ics = array(0,0,0);
            $service = null;
            $heuresHebdo = null;
            $heuresTravail = null;
            $arrivee = null;
            $depart = null;
            $login = null;
            $temps = null;
            $postes_attribues = array();
            $access = array();
            $matricule = null;
            $url_ics = null;
            $mailsResponsables = array();
            $informations = null;
            $recup = null;
            $sites = array();
            $titre = "Ajout d'un agent";
            $action = "ajout";
            if (!empty($_SESSION['perso_actif']) and $_SESSION['perso_actif'] != 'Supprim&eacute;') {
                $actif = $_SESSION['perso_actif'];
            }// vérifie dans quel tableau on se trouve pour la valeur par défaut
        }

        $jours = array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
        $contrats = array("Titulaire","Contractuel");

        //        --------------        Début listes des activités        ---------------------//
        // Toutes les activités
        $a = new \activites();
        $a->fetch();
        $activites = $a->elements;

        $postes_complet = array();
        $postes_completNoms = array();

        foreach ($activites as $elem) {
            $postes_completNoms[] = array($elem['nom'],$elem['id']);
            $postes_complet[] = $elem['id'];
        }

        // les activités non attribuées (disponibles)
        $postes_dispo = array();
        if ($postes_attribues) {
            $postes = implode(",", $postes_attribues);    //    activités attribuées séparées par des virgules (valeur transmise à valid.php)
            foreach ($postes_complet as $elem) {
                if (!in_array($elem, $postes_attribues)) {
                    $postes_dispo[] = $elem;
                }
            }
        } else {
            //activités attribuées séparées par des virgules (valeur transmise à valid.php)
            $postes = "";
            $postes_dispo = $postes_complet;
        }

        // traduction en JavaScript du tableau postes_completNoms
        // pour les fonctions seltect_add* et select_drop
        $postes_completNoms_json = json_encode($postes_completNoms);
        $this->templateParams(array(
            'postes_completNoms_json' => $postes_completNoms_json
        ));

        $postes_attribues = $this->postesNoms($postes_attribues, $postes_completNoms);
        $postes_dispo = $this->postesNoms($postes_dispo, $postes_completNoms);

        $this->templateParams(array(
            'demo'              => empty($this->config('demo')) ? 0 : 1,
            'can_manage_agent'  => in_array(21, $droits) ? 1 : 0,
            'titre'             => $titre,
            'conges_enabled'    => $this->config('Conges-Enable'),
            'conges_mode'       => $this->config('Conges-Mode'),
            'multi_site'        => $this->config('Multisites-nombre') > 1 ? 1 : 0,
            'nb_sites'          => $this->config('Multisites-nombre'),
            'recup_agent'       => $this->config('Recup-Agent'),
            'Hamac_csv'         => $this->config('Hamac-csv'),
            'ICS_Server1'       => $this->config('ICS-Server1'),
            'ICS_Server2'       => $this->config('ICS-Server2'),
            'ICS_Server3'       => $this->config('ICS-Server3'),
            'ICS_Code'          => $this->config('ICS-Code'),
            'MSGraphConfig'     => !empty($_ENV['MS_GRAPH_CLIENT_ID']),
            'MSGraphCheck'      => $mSGraphCheck,
            'ics'               => $ics,
            'CSRFSession'       => $CSRFSession,
            'action'            => $action,
            'id'                => $id,
            'nom'               => $nom,
            'prenom'            => $prenom,
            'mail'              => $mail,
            'statuts'           => $statuts,
            'statut'            => $statut,
            'statuts_utilises'  => $statuts_utilises,
            'categories'        => $categories,
            'login'             => $login,
            'contrats'          => $contrats,
            'categorie'         => $categorie,
            'services'          => $services,
            'services_utilises' => $services_utilises,
            'service'           => $service,
            'heures_hebdo'      => $heuresHebdo,
            'heures_travail'    => $heuresTravail,
            'actif'             => $actif,
            'arrivee'           => $arrivee,
            'depart'            => $depart,
            'matricule'         => $matricule,
            'mailsResponsables' => $mailsResponsables,
            'mailsResp_joined'  => implode("; ", $mailsResponsables),
            'informations'      => $informations,
            'informations_str'  => str_replace("\n", "<br/>", strval($informations)),
            'recup'             => $recup,
            'recup_str'         => str_replace("\n", "<br/>", strval($recup)),
            'recupAgents'       => $recupAgents,
            'postes'            => $postes,
            'postes_dispo'      => $postes_dispo,
            'postes_attribues'  => $postes_attribues,
        ));

        $this->templateParams(array(
            'lang_send_ics_url_subject' => $lang['send_ics_url_subject'],
            'lang_send_ics_url_message' => $lang['send_ics_url_message'],
        ));
        if ($this->config('ICS-Server1') or $this->config('ICS-Server2')
            or $this->config('ICS-Server3') or $this->config('ICS-Export')
            or $this->config('Hamac-csv')
            or !empty($_ENV['MS_GRAPH_CLIENT_ID'])) {
            $this->templateParams(array( 'agendas_and_sync' => 1 ));
        }

        if (in_array(21, $droits)) {
            $granularite = $this->config('Granularite') == 1
                ? 5 : $this->config('Granularite');

            $nb_interval = 60 / $granularite;
            $end = 40;
            $times = array();
            for ($i = 1; $i < $end; $i++) {
                $times[] = array($i + 0, $i . 'h00');
                $minute = 0;
                for ($y = 1; $y < $nb_interval; $y++) {
                    $minute = sprintf("%02d", $minute + $granularite);
                    $decimal = round($minute / 60, 2);
                    $times[] = array($i + $decimal, $i . "h$minute");
                }
            }
            $times[] = array($end, $end . "h00");
            $this->templateParams(array( 'times' => $times ));

        } else {
            $heuresHebdo_label = $heuresHebdo;
            if (!stripos($heuresHebdo, "%")) {
                $heuresHebdo_label .= " heures";
            }
            $this->templateParams(array(
                'heuresHebdo_label'   => $heuresHebdo_label,
                'heuresTravail_label' => $heuresTravail . " heures",
            ));
        }

        // Multi-sites
        if ($this->config('Multisites-nombre') > 1) {
            $sites_select = array();
            for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
                $site_select = array(
                    'id' => $i,
                    'name' => $this->config("Multisites-site$i"),
                    'checked' => 0
                );
                if ( in_array($i, $sites) ) {
                    $site_select['checked'] = 1;
                }
                $sites_select[] = $site_select;
            }
            $this->templateParams(array( 'sites_select' => $sites_select ));
        }

        include(__DIR__ . "/../../public/personnel/hours_tables.php");
        $this->templateParams(array( 'hours_tab' => $hours_tab ));

        if ($this->config('Hamac-csv')) {
            $hamac_pattern = !empty($this->config('Hamac-motif')) ? $this->config('Hamac-motif') : 'Hamac';
            $this->templateParams(array(
                'hamac_pattern'     => $hamac_pattern,
                'check_hamac'       => !empty($check_hamac) ? 1 : 0,
            ));
        }

        if ($this->config('ICS-Server1')) {
            $ics_pattern = !empty($this->config('ICS-Pattern1')) ? $this->config('ICS-Pattern1') : 'Serveur ICS N°1';
            $this->templateParams(array(
                'ics_pattern'     => $ics_pattern,
                'check_ics'       => !empty($check_ics[0]) ? 1 : 0,
            ));
        }

        if ($this->config('ICS-Server2')) {
            $ics_pattern = !empty($this->config('ICS-Pattern2')) ? $this->config('ICS-Pattern2') : 'Serveur ICS N°2';
            $this->templateParams(array(
                'ics_pattern2'     => $ics_pattern,
                'check_ics2'       => !empty($check_ics[1]) ? 1 : 0,
            ));
        }

        // URL du flux ICS à importer
        if ($this->config('ICS-Server3')) {
            $ics_pattern = !empty($this->config('ICS-Pattern3')) ? $this->config('ICS-Pattern3') : 'Serveur ICS N°3';
            $this->templateParams(array(
                'ics_pattern3'     => $ics_pattern,
                'check_ics3'       => !empty($check_ics[2]) ? 1 : 0,
                'url_ics'          => $url_ics,
            ));
        }

        // URL du fichier ICS Planno
        if ($id and isset($ics)) {
            if ($this->config('ICS-Code')) {
            }
        }

        // List of excluded rights with Planook configuration
        $planook_excluded_rights = array(6, 9, 701, 3, 17, 1301, 23, 1001, 901, 801);

        $rights = array();

        foreach ($groupes as $elem) {
            // N'affiche pas les droits d'accès à la configuration (réservée au compte admin)
            if ($elem['groupe_id'] == 20) {
                continue;
            }

            // N'affiche pas les droits de gérer les congés si le module n'est pas activé
            if (!$this->config('Conges-Enable') and in_array($elem['groupe_id'], array(25, 401, 601))) {
                continue;
            }

            // N'affiche pas les droits de gérer les plannings de présence si le module n'est pas activé
            if (!$this->config('PlanningHebdo') and in_array($elem['groupe_id'], array(1101, 1201))) {
                continue;
            }

            // N'affiche pas le droit gestion des absences niveau 2 si la config Abences-validation est désactivé
            // on doit garder le niveau 1 pour permettre aux administrateurs la saisie d'asbences pour d'autres agents)
            if (!$this->config('Absences-validation') and $elem['groupe_id'] == 501 ) {
                continue;
            }

            // With Planook configuration, some rights are not displayed
            if ($this->config('Planook') and in_array($elem['groupe_id'], $planook_excluded_rights)) {
                continue;
            }

            if ( is_array($acces) ) {
                $elem['checked'] = in_array($elem['groupe_id'], $acces) ? true : false;
            }

            $rights[ $elem['categorie'] ]['rights'][] = $elem;
        }
        $this->templateParams(array('rights' => $rights));

        // Affichage des droits d'accès dépendant des sites (si plusieurs sites)
        if ($this->config('Multisites-nombre') > 1) {
            $sites_for_rights = array();
            for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
                $sites_for_rights[] = array( 'site_name' => $this->config("Multisites-site$i") );
            }

            $this->templateParams(array('sites_for_rights' => $sites_for_rights));

            $rights_sites = array();
            foreach ($groupes_sites as $elem) {
                // N'affiche pas les droits de gérer les congés si le module n'est pas activé
                if (!$this->config('Conges-Enable') and in_array($elem['groupe_id'], array(25, 401, 601))) {
                    continue;
                }

                // N'affiche pas le droit gestion des absences niveau 2 si la config Abences-validation est désactivé
                // on doit garder le niveau 1 pour permettre aux administrateurs la saisie d'asbences pour d'autres agents)
                if (!$this->config('Absences-validation') and $elem['groupe_id'] == 501 ) {
                    continue;
                }

                // With Planook configuration, some rights are not displayed
                if ($this->config('Planook') and in_array($elem['groupe_id'], $planook_excluded_rights)) {
                    continue;
                }

                $elem['sites'] = array();
                for ($i = 1; $i < $this->config('Multisites-nombre') +1; $i++) {
                    $groupe_id = $elem['groupe_id'] - 1 + $i;

                    $checked = false;
                    if (is_array($acces)) {
                        $checked = in_array($groupe_id, $acces) ? true : false;
                    }

                    $elem['sites'][] = array(
                        'groupe_id' => $groupe_id,
                        'checked'   => $checked,
                    );
                }

                $rights_sites[ $elem['categorie'] ]['rights'][] = $elem;
            }
            $this->templateParams(array('rights_sites' => $rights_sites));
        }

        if ($this->config('Conges-Enable')) {
            $c = new \conges();
            $c->perso_id = $id;
            $c->fetchCredit();
            $conges = $c->elements;
            $holiday_helper = new HolidayHelper();

            $annuelHeures  = $conges['annuelHeures']  ? $conges['annuelHeures']  : 0;
            $annuelMinutes = $conges['annuelMinutes'] ? $conges['annuelMinutes'] : 0;
            $annuelString  = '';

            $creditHeures  = $conges['creditHeures']  ? $conges['creditHeures']  : 0;
            $creditMinutes = $conges['creditMinutes'] ? $conges['creditMinutes'] : 0;
            $creditString  = '';

            $reliquatHeures  = $conges['reliquatHeures']  ? $conges['reliquatHeures']  : 0;
            $reliquatMinutes = $conges['reliquatMinutes'] ? $conges['reliquatMinutes'] : 0;
            $reliquatString  = '';

            $anticipationHeures  = $conges['anticipationHeures']  ? $conges['anticipationHeures']  : 0;
            $anticipationMinutes = $conges['anticipationMinutes'] ? $conges['anticipationMinutes'] : 0;
            $anticipationString  = '';

            $recupHeures  = $conges['recupHeures']  ? $conges['recupHeures']  : 0;
            $recupMinutes = $conges['recupMinutes'] ? $conges['recupMinutes'] : 0;

            if ($this->config('Conges-Mode') == 'jours' ) {
                $event = new OnTransformLeaveHours($conges);
                $this->dispatcher->dispatch($event, $event::ACTION);

                if ($event->hasResponse()) {
                    $response = $event->response();
                    $annuelHeures = $response['annuel'];
                    $annuelString = $annuelHeures;
                    $creditHeures = $response['credit'];
                    $creditString = $creditHeures;
                    $reliquatHeures = $response['reliquat'];
                    $reliquatString = $reliquatHeures;
                    $anticipationHeures = $response['anticipation'];
                    $anticipationString = $anticipationHeures;
                } else {
                    $annuelHeures = $conges['annuel'] / 7;
                    $annuelHeures = round($annuelHeures * 2) / 2;
                    $annuelString = $annuelHeures;
                    $creditHeures = $conges['credit'] / 7;
                    $creditHeures = round($creditHeures * 2) / 2;
                    $creditString = $creditHeures;
                    $reliquatHeures = $conges['reliquat'] / 7;
                    $reliquatHeures = round($reliquatHeures * 2) / 2;
                    $reliquatString = $reliquatHeures;
                    $anticipationHeures = $conges['anticipation'] / 7;
                    $anticipationHeures = round($anticipationHeures * 2) / 2;
                    $anticipationString = $anticipationHeures;
                }
            }

            $templateParams = array(
                'annuel_heures'         => $annuelHeures,
                'annuel_min'            => $annuelMinutes,
                'annuel_string'         => $annuelString,
                'credit_heures'         => $creditHeures,
                'credit_min'            => $creditMinutes,
                'credit_string'         => $creditString,
                'reliquat_heures'       => $reliquatHeures,
                'reliquat_min'          => $reliquatMinutes,
                'reliquat_string'       => $reliquatString,
                'anticipation_heures'   => $anticipationHeures,
                'anticipation_min'      => $anticipationMinutes,
                'anticipation_string'   => $anticipationString,
                'recup_heures'          => $recupHeures,
                'recup_min'             => $recupMinutes,
                'lang_comp_time'        => $lang['comp_time'],
                'show_hours_to_days'    => $holiday_helper->showHoursToDays(),
            );
            if ($holiday_helper->showHoursToDays()) {
                $templateParams['annuel_jours'] = $id ? $holiday_helper->hoursToDays(heure4($annuelString), $id) : '';
                $templateParams['credit_jours'] = $id ? $holiday_helper->hoursToDays(heure4($creditString), $id) : '';
                $templateParams['reliquat_jours'] = $id ? $holiday_helper->hoursToDays(heure4($reliquatString), $id) : '';
                $templateParams['anticipation_jours'] = $id ? $holiday_helper->hoursToDays(heure4($anticipationString), $id) : '';
                $templateParams['hours_per_day'] = $id ? $holiday_helper->hoursPerDay($id) : '';

            }
            $this->templateParams($templateParams);
        }

        $this->templateParams(array(
            'edt_samedi'    => $this->config('EDTSamedi'),
            'current_tab'   => $currentTab,
            'nb_semaine'    => $this->config('nb_semaine'),
        ));

        return $this->output('agents/edit.html.twig');
    }

    #[Route(path: '/agent', name: 'agent.save', methods: ['POST'])]
    public function save(Request $request)
    {

        $params = $request->request->all();

        $arrivee = $request->get('arrivee');
        $depart = $request->get('depart');
        $CSRFToken = $request->get('CSRFToken');
        $heuresHebdo = $request->get('heuresHebdo');
        $heuresTravail = $request->get('heuresTravail');
        $id = $request->get('id');
        $mail = $request->get('mail');

        $actif = htmlentities($params['actif'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $action = $params['action'];
        $check_hamac = !empty($params['check_hamac']) ? 1 : 0;
        $mSGraphCheck = !empty($request->get('MSGraph')) ? 1 : 0;
        $check_ics1 = !empty($params['check_ics1']) ? 1 : 0;
        $check_ics2 = !empty($params['check_ics2']) ? 1 : 0;
        $check_ics3 = !empty($params['check_ics3']) ? 1 : 0;
        $check_ics = "[$check_ics1,$check_ics2,$check_ics3]";
        $droits = array_key_exists("droits", $params) ? $params['droits'] : null;
        $categorie = isset($params['categorie']) ? trim($params['categorie']) : null;
        $informations = isset($params['informations']) ? trim($params['informations']) : null;
        $mailsResponsables = isset($params['mailsResponsables']) ? trim(str_replace(array("\n", " "), '', $params['mailsResponsables'])) : null;
        $matricule = isset($params['matricule']) ? trim($params['matricule']) : null;
        $url_ics = isset($params['url_ics']) ? trim($params['url_ics']) : null;
        $nom = trim($params['nom']);
        $postes = $params['postes'] ?? null;
        $prenom = trim($params['prenom']);
        $recup = isset($params['recup']) ? trim($params['recup']) : null;
        $service = $params['service'] ?? null;
        $sites = array_key_exists("sites", $params) ? $params['sites'] : null;
        $statut = $params['statut'] ?? null;
        $temps = array_key_exists("temps", $params) ? $params['temps'] : null;

        // Modification du choix des emplois du temps avec l'option EDTSamedi == 1 (EDT différent les semaines avec samedi travaillé)
        $eDTSamedi = array_key_exists("EDTSamedi", $params) ? $params['EDTSamedi'] : null;

        // Modification du choix des emplois du temps avec l'option EDTSamedi == 2 (EDT différent les semaines avec samedi travaillé et les semaines à ouverture restreinte)
        if ($this->config('EDTSamedi') == 2) {
            $eDTSamedi = array();
            foreach ($params as $k => $v) {
                if (substr($k, 0, 10) == 'EDTSamedi_' and $v > 1) {
                    $eDTSamedi[] = array(substr($k, -10), $v);
                }
            }
        }

        $premierLundi = array_key_exists("premierLundi", $params) ? $params['premierLundi'] : null;
        $dernierLundi = array_key_exists("dernierLundi", $params) ? $params['dernierLundi'] : null;

        if (is_array($temps)) {
            foreach ($temps as $day => $hours) {
                foreach ($hours as $i => $hour) {
                    $temps[$day][$i] = HourHelper::toHis($hour);
                }
            }
        }

        $droits = $droits ? $droits : array();
        $postes = $postes ? json_encode(explode(",", $postes)) : '[]';
        $sites = $sites ? json_encode($sites) : null;
        $temps = $temps ? json_encode($temps) : null;

        $arrivee = dateSQL($arrivee);
        $depart = dateSQL($depart);

        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            // Modification des plannings Niveau 2 donne les droits Modification des plannings Niveau 1
            if (in_array((300+$i), $droits) and !in_array((1000+$i), $droits)) {
                $droits[]=1000+$i;
            }
        }

        // Le droit de gestion des absences (20x) donne le droit modifier ses propres absences (6) et le droit d'ajouter des absences pour plusieurs personnes (9)
        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (in_array((200+$i), $droits) or in_array((500+$i), $droits)) {
                $droits[] = 6;
                break;
            }
        }

        $droits[] = 99;
        $droits[] = 100;
        if ($id == 1) {        // Ajoute config. avancée à l'utilisateur admin.
            $droits[] = 20;
        }
        $droits = json_encode($droits);

        switch ($action) {
          case "ajout":
            $db = new \db();
            $db->select2("personnel", array(array("name"=>"MAX(`id`)", "as"=>"id")));
            $id = $db->result[0]['id']+1;

            $login = $this->login($prenom, $nom, $mail);

            // Demo mode
            if (!empty($this->config('demo'))) {
                $mdp_crypt = password_hash("password", PASSWORD_BCRYPT);
                $msg = "Vous utilisez une version de démonstration : l'agent a été créé avec les identifiants $login / password";
                $msg .= "#BR#Sur une version standard, les identifiants de l'agent lui auraient été envoyés par e-mail.";
                $msgType = "success";
            } else {
                $mdp = gen_trivial_password();
                $mdp_crypt = password_hash($mdp, PASSWORD_BCRYPT);

                $notifier = $this->notifier;
                $notifier->setRecipients($mail)
                         ->setMessageCode('create_account')
                         ->setMessageParameters(array(
                             'login' => $login,
                             'password' => $mdp
                         ));
                $notifier->send();

                // Si erreur d'envoi de mail, affichage de l'erreur
                $msg = null;
                $msgType = null;
                if ($notifier->getError()) {
                    $msg = $notifier->getError();
                    $msgType = "error";
                }
            }

            // Enregistrement des infos dans la base de données
            $insert = array(
                "nom"=>$nom,
                "prenom"=>$prenom,
                "mail"=>$mail,
                "statut"=>$statut,
                "categorie"=>$categorie,
                "service"=>$service,
                "heures_hebdo"=>$heuresHebdo,
                "heures_travail"=>$heuresTravail,
                "arrivee"=>$arrivee,
                "depart"=>$depart,
                "login"=>$login,
                "password"=>$mdp_crypt,
                "actif"=>$actif,
                "droits"=>$droits,
                "postes"=>$postes,
                "temps"=>$temps,
                "informations"=>$informations,
                "recup"=>$recup,
                "sites"=>$sites,
                "mails_responsables"=>$mailsResponsables,
                "matricule"=>$matricule,
                "url_ics"=>$url_ics,
                "check_ics"=>$check_ics,
                "check_hamac"=>$check_hamac,
                'check_ms_graph' => $mSGraphCheck,
            );
            $holidays = $this->save_holidays($params);
            $insert = array_merge($insert, $holidays);

            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->insert("personnel", $insert);

            // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
            $p = new \personnel();
            $p->CSRFToken = $CSRFToken;
            $p->updateEDTSamedi($eDTSamedi, $premierLundi, $dernierLundi, $id);

            return $this->redirectToRoute('agent.index', array('msg' => $msg, 'msgType' => $msgType));

            break;

          case "mdp":

            // Demo mode
            if (!empty($this->config('demo'))) {
                $msg = "Le mot de passe n'a pas été modifié car vous utilisez une version de démonstration";
                return $this->redirectToRoute('agent.index', array('msg' => $msg, 'msgType' => 'success'));
                break;
            }

            $mdp=gen_trivial_password();
            $mdp_crypt = password_hash($mdp, PASSWORD_BCRYPT);
            $db = new \db();
            $db->select2("personnel", "login", array("id"=>$id));
            $login = $db->result[0]['login'];

            // Envoi du mail
            $message = "Votre mot de passe Planno a été modifié";
            $message.= "<ul><li>Login : $login</li><li>Mot de passe : $mdp</li></ul>";

            $m = new \CJMail();
            $m->subject = "Modification du mot de passe";
            $m->message = $message;
            $m->to = $mail;
            $m->send();

            // Si erreur d'envoi de mail, affichage de l'erreur
            $msg = null;
            $msgType = null;
            if ($m->error) {
                $msg = $m->error_CJInfo;
                $msgType = "error";
            } else {
                $msg = "Le mot de passe a été modifié et envoyé par e-mail à l'agent";
                $msgType = "success";
            }

            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update("personnel", array("password"=>$mdp_crypt), array("id"=>$id));
            return $this->redirectToRoute('agent.index', array('msg' => $msg, 'msgType' => $msgType));

            break;

          case "modif":
            $update = array(
                "nom"=>$nom,
                "prenom"=>$prenom,
                "mail"=>$mail,
                "statut"=>$statut,
                "categorie"=>$categorie,
                "service"=>$service,
                "heures_hebdo"=>$heuresHebdo,
                "heures_travail"=>$heuresTravail,
                "actif"=>$actif,
                "droits"=>$droits,
                "arrivee"=>$arrivee,
                "depart"=>$depart,
                "postes"=>$postes,
                "informations"=>$informations,
                "recup"=>$recup,
                "sites"=>$sites,
                "mails_responsables"=>$mailsResponsables,
                "matricule"=>$matricule,
                "url_ics"=>$url_ics,
                "check_ics"=>$check_ics,
                "check_hamac"=>$check_hamac,
                'check_ms_graph' => $mSGraphCheck,
            );
            // Si le champ "actif" passe de "supprimé" à "service public" ou "administratif", on réinitialise les champs "supprime" et départ
            if (!strstr($actif, "Supprim")) {
                $update["supprime"]="0";
                // Si l'agent était supprimé et qu'on le réintégre, on change sa date de départ
                // pour qu'il ne soit pas supprimé de la liste des agents actifs
                $db = new \db();
                $db->select2("personnel", "*", array("id" => $id));
                if (strstr($db->result[0]['actif'], "Supprim") and $db->result[0]['depart'] <= date("Y-m-d")) {
                    $update["depart"] = "0000-00-00";
                }
            } else {
                $update["actif"] = "Supprim&eacute;";
            }

            // Mise à jour de l'emploi du temps si modifié à partir de la fiche de l'agent
            if ($temps) {
                $update["temps"] = $temps;
            }

            $holidays = $this->save_holidays($params);
            $update = array_merge($update, $holidays);

            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update("personnel", $update, array("id" => $id));

            // Mise à jour de la table pl_poste en cas de modification de la date de départ
            $db = new \db();        // On met supprime=0 partout pour cet agent
            $db->CSRFToken = $CSRFToken;
            $db->update("pl_poste", array("supprime" => "0"), array("perso_id" => $id));
            if ($depart != "0000-00-00" and $depart != "") {
                // Si une date de départ est précisée, on met supprime=1 au dela de cette date
                $db = new \db();
                $id = $db->escapeString($id);
                $depart = $db->escapeString($depart);
                $dbprefix = $this->config('dbprefix');
                $db->query("UPDATE `{$dbprefix}pl_poste` SET `supprime`='1' WHERE `perso_id`='$id' AND `date`>'$depart';");
            }

            // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
            $p = new \personnel();
            $p->CSRFToken = $CSRFToken;
            $p->updateEDTSamedi($eDTSamedi, $premierLundi, $dernierLundi, $id);

            return $this->redirectToRoute('agent.index');

            break;
        }
    }

    private function changeAgentPassword(Request $request, $agent_id, $password) {

        $agent = $this->entityManager->find(Agent::class, $agent_id);

        $response = new Response();
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);

            return $response;
        }

        if (!$password) {
            $response->setContent('Missing password');
            $response->setStatusCode(400);

            return $response;
        }

        if (!$this->check_password_complexity($password)) {
            $response->setContent('Password too weak');
            $response->setStatusCode(400);

            return $response;
        }

        $password = password_hash($password, PASSWORD_BCRYPT);
        $agent->password($password);
        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        $response->setContent('Password successfully changed');
        $response->setStatusCode(200);

        return $response;
    }

    #[Route(path: '/ajax/change-own-password', name: 'ajax.changeownpassword', methods: ['POST'])]
    public function changeOwnPassword(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            $response = new Response();
            $response->setContent('CSRF token error');
            $response->setStatusCode(400);
            return $response;
        }

        $session = $request->getSession();

        $agent_id = $session->get('loginId');
        $password = $request->get('password');
        $current_password = $request->get('current_password');

        if ($this->checkCurrentPassword($agent_id, $current_password)) {
            return $this->changeAgentPassword($request, $agent_id, $password);
        } else {
            $response = new Response();
            $response->setContent('Current password is erroneous');
            $response->setStatusCode(400);
            return $response;
        }
    }

    #[Route(path: '/ajax/check-password', name: 'ajax.checkpassword', methods: ['GET'])]
    public function check_password(Request $request)
    {
        $password = $request->get('password');
        $response = new Response();
        $ok = $this->check_password_complexity($password);
        $response->setContent($ok ? "ok" : "not ok");
        $response->setStatusCode(200);

        return $response;
    }

    // Returns true if the password is complex enough, and false otherwise
    private function check_password_complexity($password)
    {
        $minimum_password_length = $this->config('Auth-PasswordLength') ?? 8;
        if (strlen($password) < $minimum_password_length) {
            return false;
        }
        if (!preg_match("#[0-9]+#", $password)) {
            return false;
        }
        if (!preg_match("#[A-Z]+#", $password)) {
            return false;
        }
        if (!preg_match("#[a-z]+#", $password)) {
            return false;
        }
        # Special chars list come from this list: https://owasp.org/www-community/password-special-characters
        $chars = array('!', '"', '#', '$', '%', '&', "'", '(', ')', '*', '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~');
        foreach($chars as $char) {
            if (strpos($password, $char) !== false) {
                return true;
            }
        }
        return false;
    }

    #[Route(path: '/ajax/is-current-password', name: 'ajax.iscurrentpassword', methods: ['GET'])]
    public function isCurrentPassword(Request $request)
    {
        $session = $request->getSession();

        $agent_id = $session->get('loginId');
        $password = $request->get('password');
        $response = new Response();

        $isCurrentPassword = $this->checkCurrentPassword($agent_id, $password);

        $response->setContent($isCurrentPassword ? "1" : 0);
        $response->setStatusCode(200);

        return $response;
    }

    private function checkCurrentPassword($agent_id, $password)
    {
        $isCurrentPassword = false;
        $agent = $this->entityManager->find(Agent::class, $agent_id);
        $hashedPassword = $agent->password();
	
        if (password_verify($password, $hashedPassword)) {
            $isCurrentPassword = true;
        }

        return $isCurrentPassword;
    }

    #[Route(path: '/ajax/update_agent_login', name: 'ajax.update_agent_login', methods: ['POST'])]
    public function update_login(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $login = $request->get('login');
        $agent_id = $request->get('id');
        $response = new Response();

        $login = filter_var($login, FILTER_SANITIZE_EMAIL);

        $agent = $this->entityManager->find(Agent::class, $agent_id);

        $duplicate = $this->entityManager
            ->getRepository(Agent::class)
            ->findOneBy(array('login' => $login));

        if ($login == $agent->login()) {
            $response->setContent('identic');
            $response->setStatusCode(400);

            return $response;
        }

        if ($duplicate && $login != $agent->login()) {
            $response->setContent('duplicate');
            $response->setStatusCode(400);

            return $response;
        }

        $agent = $this->entityManager->find(Agent::class, $agent_id);
        $agent->login($login);
        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        $response->setContent($login);
        $response->setStatusCode(200);

        return $response;
    }

    #[Route(path: '/agent/ldap', name: 'agent.ldap', methods: ['GET'])]
    public function ldap_index(Request $request)
    {
        $searchTerm = $request->get('searchTerm');

        $results = array();
        if ($searchTerm) {
            $infos = array();
            if (!$this->config('LDAP-Port')) {
                // Default LDAP port.
                $this->config('LDAP-Port', 389);
            }
            if (!$this->config('LDAP-Filter')) {
                // Default LDAP filter.
                $filter = '(objectclass=inetorgperson)';
            } elseif ($this->config('LDAP-Filter')[0] != '(') {
                $filter = '(' . $this->config('LDAP-Filter') . ')';
            } else {
                $filter = $this->config('LDAP-Filter');
            }

            $ldap_id_attribute = $this->config('LDAP-ID-Attribute');

            // Add search values into filter.
            $filter = "(&{$filter}(|({$ldap_id_attribute}=*$searchTerm*)(givenname=*$searchTerm*)(sn=*$searchTerm*)(mail=*$searchTerm*)))";

            // Connect to LDAP server
            $url = $this->config('LDAP-Protocol') .'://'
                . $this->config('LDAP-Host') . ':'
                . $this->config('LDAP-Port');

            $ldapconn = ldap_connect($url)
                or die("Impossible de joindre le serveur LDAP");

            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

            if ($ldapconn) {
                $ldapbind = ldap_bind($ldapconn, $this->config('LDAP-RDN'), decrypt($this->config('LDAP-Password')))
                    or die("Impossible de se connecter au serveur LDAP");
            }

            if ($ldapbind) {
                $justthese = array('dn',
                    $this->config('LDAP-ID-Attribute'),
                    'sn', 'givenname', 'userpassword', 'mail');

                if (!empty($this->config('LDAP-Matricule'))) {
                    $justthese = array_merge($justthese, array($this->config('LDAP-Matricule')));
                }

                $sr = ldap_search($ldapconn, $this->config('LDAP-Suffix'), $filter, $justthese);
                $infos = ldap_get_entries($ldapconn, $sr);
            }

            // Search existing agents.
            $agents_existants = array();
            $db = new \db();
            $db->query("SELECT `login` FROM `{$GLOBALS['dbprefix']}personnel` WHERE `supprime`<>'2' ORDER BY `login`;");
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $agents_existants[] = $elem['login'];
                }
            }

            // Remove existing agents from LDAP results.
            $tab = array();
            if (!empty($infos)) {
                foreach ($infos as $info) {
                    if (!is_array($info)) {
                        continue;
                    }
                    if (!in_array($info[$this->config('LDAP-ID-Attribute')][0], $agents_existants) and !empty($info)) {
                        $tab[] = $info;
                    }
                }
                $infos=$tab;
            }

            //	Affichage du tableau
            if (!empty($infos)) {
                usort($infos, "cmp_ldap");

                foreach ($infos as $info) {
                    $sn=array_key_exists('sn', $info)?$info['sn'][0]:null;
                    $givenname=array_key_exists('givenname', $info)?$info['givenname'][0]:null;
                    $mail=array_key_exists('mail', $info)?$info['mail'][0]:null;

                    $matricule = null;
                    if (!empty($this->config('LDAP-Matricule'))
                        and !empty($info[$this->config('LDAP-Matricule')])) {
                        $matricule = is_array($info[$this->config('LDAP-Matricule')])
                            ? $info[$this->config('LDAP-Matricule')][0]
                            : $info[$this->config('LDAP-Matricule')];
                    }

                    $result = array(
                        'id'        => utf8_decode($info[$this->config('LDAP-ID-Attribute')][0]),
                        'sn'        => $sn,
                        'givenname' => $givenname,
                        'mail'      => $mail,
                        'login'     => $info[$this->config('LDAP-ID-Attribute')][0],
                        'matricule' => $matricule
                    );
                    $results[] = $result;
                }
            }
        }

        $this->templateParams(array(
            'CSRFSession'   => $GLOBALS['CSRFSession'],
            'action'        => 'agent/ldap',
            'title1'        => "Importation des agents à partir de l'annuaire LDAP",
            'title2'        => "Importation de nouveaux agents à partir de l'annuaire LDAP",
            'searchTerm'    => $searchTerm,
            'results'       => $results
        ));

        return $this->output('agents/import-form.html.twig');
    }

    #[Route(path: '/agent/ldap', name: 'agent.ldap.import', methods: ['POST'])]
    public function ldap_import(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $actif = 'Actif';
        $date = date("Y-m-d H:i:s");
        $commentaires = "Importation LDAP $date";
        $droits = json_encode(array(99, 100));
        $password = "password_bidon_pas_importé_depuis_ldap";
        $postes = json_encode(array());
        $erreurs = false;

        $post = $request->request->all();
        $searchTerm = $post["searchTerm"];

        // Get selected agents uid.
        $uids = array();
        if (array_key_exists("chk", $post)) {
            foreach ($post["chk"] as $elem) {
                $uids[] = ldap_escape($elem, '', LDAP_ESCAPE_FILTER);
            }
        } else {
            $session->getFlashBag()->add('error', "Aucun agent n'est sélectionné");
            return $this->redirectToRoute(
                'agent.ldap',
                array(
                    'searchTerm' => $searchTerm,
                ),
                //Response::HTTP_MOVED_PERMANENTLY // = 301
            );
        }

        // Connect to LDAP server.
        if (!$this->config('LDAP-Port')) {
            $this->config('LDAP-Port', 389);
        }

        $url = $this->config('LDAP-Protocol') . '://'
            . $this->config('LDAP-Host') . ':'
            . $this->config('LDAP-Port');

        $ldapconn = ldap_connect($url)
          or die("Impossible de se connecter au serveur LDAP");

        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

        if ($ldapconn) {
            $ldapbind=ldap_bind($ldapconn, $this->config('LDAP-RDN'), decrypt($this->config('LDAP-Password')));
        }

        // Préparation de la requête pour insérer les données dans la base de données
        $req = "INSERT INTO `{$GLOBALS['dbprefix']}personnel` (`login`,`nom`,`prenom`,`mail`,`matricule`,`password`,`droits`,`arrivee`,`postes`,`actif`,`commentaires`) ";
        $req .= "VALUES (:login, :nom, :prenom, :mail, :matricule, :password, :droits, :arrivee, :postes, :actif, :commentaires);";
        $db = new \dbh();
        $db->CSRFToken = $CSRFToken;
        $db->prepare($req);

        // Recuperation des infos LDAP et insertion dans la base de données
        if ($ldapbind) {
            foreach ($uids as $uid) {
                $filter='(' . $this->config('LDAP-ID-Attribute') . "=$uid)";
                $justthese=array("dn",$this->config('LDAP-ID-Attribute'),"sn","givenname","userpassword","mail");

                if (!empty($this->config('LDAP-Matricule'))) {
                    $justthese = array_merge($justthese, array($this->config('LDAP-Matricule')));
                }

                $sr=ldap_search($ldapconn, $this->config('LDAP-Suffix'), $filter, $justthese);
                $infos=ldap_get_entries($ldapconn, $sr);
                if ($infos[0][$this->config('LDAP-ID-Attribute')]) {
                    $login=$infos[0][$this->config('LDAP-ID-Attribute')][0];
                    $nom=array_key_exists("sn", $infos[0])?htmlentities($infos[0]['sn'][0], ENT_QUOTES|ENT_IGNORE, "UTF-8", false):"";
                    $prenom=array_key_exists("givenname", $infos[0])?htmlentities($infos[0]['givenname'][0], ENT_QUOTES|ENT_IGNORE, "UTF-8", false):"";
                    $mail=array_key_exists("mail", $infos[0])?$infos[0]['mail'][0]:"";

                    $matricule = '';
                    if (!empty($this->config('LDAP-Matricule'))
                        and !empty($infos[0][$this->config('LDAP-Matricule')])) {
                        $matricule = is_array($infos[0][$this->config('LDAP-Matricule')])
                            ? strval($infos[0][$this->config('LDAP-Matricule')][0])
                            : strval($infos[0][$this->config('LDAP-Matricule')]);
                    }

                    $values = array(
                        ':login'        => $login,
                        ':nom'          => $nom,
                        ':prenom'       => $prenom,
                        ':mail'         => $mail,
                        ':matricule'    => $matricule,
                        ':password'     => $password,
                        ':droits'       => $droits,
                        ':arrivee'      => $date,
                        ':postes'       => $postes,
                        ':actif'        => $actif,
                        ':commentaires' => $commentaires
                    );

                    // Execution de la requête (insertion dans la base de données)
                    $db->execute($values);
                    if ($db->error) {
                        $erreurs=true;
                    }
                }
            }
        }

        if ($erreurs) {
            $session->getFlashBag()->add('error', "Il y a eu des erreurs pendant l'importation.#BR#Veuillez vérifier la liste des agents");
        } else {
            $session->getFlashBag()->add('notice', 'Les agents ont été importés avec succès');
        }
        return $this->redirectToRoute(
            'agent.ldap',
            array(
                'searchTerm' => $searchTerm,
            ),
            //Response::HTTP_MOVED_PERMANENTLY // = 301
        );
    }


    #[Route(path: '/agent/ldif', name: 'agent.ldif', methods: ['GET'])]
    public function ldif_index(Request $request)
    {
        $searchTerm = $request->get('searchTerm');

        $results = $this->ldif_search($searchTerm);

        // Ignore already imported agents
        $agents = $this->entityManager->getRepository(Agent::class)->getAgentsList(1);

        foreach ($results as $key => $value) {
            foreach ($agents as $agent) {
                if ($agent->login() == $key) {
                    unset($results[$key]);
                }
            }
        }

        $this->templateParams(array(
            'CSRFSession'   => $GLOBALS['CSRFSession'],
            'action'        => 'agent/ldif',
            'title1'        => "Importation des agents à partir d'un fichier LDIF",
            'title2'        => "Importation de nouveaux agents à partir d'un fichier LDIF",
            'searchTerm'    => $searchTerm,
            'results'       => $results
        ));

        return $this->output('agents/import-form.html.twig');
    }


    #[Route(path: '/agent/ldif', name: 'agent.ldif.import', methods: ['POST'])]
    public function ldif_import(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $erreurs = false;

        $post = $request->request->all();
        $searchTerm = $post["searchTerm"];

        // Get selected agents uid.
        $uids = array();
        if (array_key_exists("chk", $post)) {
            foreach ($post["chk"] as $elem) {
                $uids[] = $elem;
            }
        } else {
            $session->getFlashBag()->add('error', "Aucun agent n'est sélectionné");
            return $this->redirectToRoute(
                'agent.ldif',
                array(
                    'searchTerm' => $searchTerm,
                ),
            );
        }

        // Préparation de la requête pour insérer les données dans la base de données
        $req = "INSERT INTO `{$GLOBALS['dbprefix']}personnel` (`login`,`nom`,`prenom`,`mail`,`matricule`,`password`,`droits`,`arrivee`,`postes`,`actif`,`commentaires`) ";
        $req .= "VALUES (:login, :nom, :prenom, :mail, :matricule, :password, :droits, :arrivee, :postes, :actif, :commentaires);";
        $db = new \dbh();
        $db->CSRFToken = $CSRFToken;
        $db->prepare($req);

        $results = $this->ldif_search($uids);

        foreach ($results as $elem) {
            $values = array(
                ':login'        => $elem['login'],
                ':nom'          => $elem['sn'],
                ':prenom'       => $elem['givenname'],
                ':mail'         => $elem['mail'],
                ':matricule'    => $elem['matricule'],
                ':arrivee'      => date('Y-m-d H:i:s'),
                ':password'     => 'LDIF import, the password is not stored',
                ':droits'       => '[99,100]',
                ':postes'       => '[]',
                ':actif'        => 'Actif',
                ':commentaires' => 'Importation LDIF ' . date('Y-m-d H:i:s'),
            );

            // Execution de la requête (insertion dans la base de données)
            $db->execute($values);
            if ($db->error) {
                $erreurs=true;
            }
        }

        if ($erreurs) {
            $session->getFlashBag()->add('error', "Il y a eu des erreurs pendant l'importation.#BR#Veuillez vérifier la liste des agents");
        } else {
            $session->getFlashBag()->add('notice', 'Les agents ont été importés avec succès');
        }

        return $this->redirectToRoute(
            'agent.ldif',
            array(
                'searchTerm' => $searchTerm,
            ),
        );
    }


    private function ldif_search($searchTerms) {

        // Return an empty list if $searchTerms is empty (as for an LDAP search)
        if (empty($searchTerms)) {
            return array();
        }

        // If $searchTerms is an array, we look for selected people for import (second search). The attribute is the unique identifier.
        if (is_array($searchTerms)) {
            $attributes = array(
                $this->config('LDIF-ID-Attribute'),
            );

        // If $searchTerms is a string, we search one term in all defined attributes (first search)
        } else {
            // Define attributes to uses for searches
            $attributes = array(
                'cn',
                'givenname',
                'mail',
                $this->config('LDIF-ID-Attribute'),
            );
 
            // Add an extra attribute (optional)
            if ($this->config('LDIF-Matricule')) {
                $attributes[] = $this->config('LDIF-Matricule');
            }

            $searchTerms = array($searchTerms);
        }

        $results = array();

        // Parse the LDIF file
        $ld = new Ldif2Array($this->config('LDIF-File'), true, $this->config('LDIF-Encoding'));

        foreach ($ld->entries as $elem) {
            $keep = false;

            foreach ($searchTerms as $searchTerm) {

                foreach ($attributes as $attr) {
                    if (isset($elem[$attr])) {
                        if (is_array($elem[$attr])) {
                            foreach ($elem[$attr] as $value) {
                                if (str_contains(strtolower($value), strtolower($searchTerm))) {
                                    $keep = true;
                                    break 2;
                                }
                            }
                        } else {
                            if (str_contains(strtolower($elem[$attr]), strtolower($searchTerm))) {
                                $keep = true;
                                break;
                            }
                        }
                    }
                }
   
                if ($keep) {
                    $result = $elem;
    
                    foreach ($attributes as $attr) {
                        if (isset($result[$attr])) {
                            if (is_array($result[$attr])) {
                                $result[$attr] = $result[$attr][0];
                            }
                        } else {
                            $result[$attr] = null;
                        }
                    }
                    $id = $result[$this->config('LDIF-ID-Attribute')];
                    $result['id'] = $id;
                    $result['login'] = $result[$this->config('LDIF-ID-Attribute')];
                    $result['matricule'] = $result[$this->config('LDIF-Matricule')] ?? null;

                    $results[$id] = $result;
                }
            }
        }

        return $results;
    }

    #[Route(path: '/agent', name: 'agent.delete', methods: ['DELETE'])]
    public function deleteAgent(Request $request, Session $session)
    {
        // Initialisation des variables
        $id = $request->get('id');
        $CSRFToken = $request->get('CSRFToken');
        $date = $request->get('date');

        // Disallow admin deletion
        if ($id == 1) {
            return $this->json("error");

        // If the date parameter is given, even if empty : deletion level 1
        } elseif ($date !== null) {
            $date = dateSQL($date);
            // Mise à jour de la table personnel
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update("personnel", array("supprime"=>"1","actif"=>"Supprim&eacute;","depart"=>$date), array("id"=>$id));

            // Mise à jour de la table pl_poste
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('pl_poste', array('supprime'=>1), array('perso_id' => "$id", 'date' =>">$date"));

            // Mise à jour de la table responsables
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete("responsables", array('responsable' => $id));

            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete("responsables", array('perso_id' => $id));

            return $this->json("level 1 delete OK");

        // If the date parameter is not given : deletion level 2
        } else {
            $p = new \personnel();
            $p->CSRFToken = $CSRFToken;
            $p->delete($id);
            return $this->json("permanent delete OK");
        }
    }

    private function save_holidays($params)
    {
        if (!$this->config('Conges-Enable')) {
            return array();
        }

        $available_keys = array(
            'conges_credit',
            'conges_reliquat',
            'conges_anticipation',
            'comp_time',
            'conges_annuel');

        foreach ($available_keys as $key) {
            $params[$key . '_min'] = isset($params[$key . '_min']) ? $params[$key . '_min'] : 0;
            $params[$key] = !empty($params[$key]) ? $params[$key] : 0;
        }

        $comp_time = HourHelper::hoursMinutesToDecimal(trim($params['comp_time_hours']), trim($params['comp_time_min']));

        if ($this->config('Conges-Mode') == 'jours' ) {
            $event = new OnTransformLeaveDays($params);
            $this->dispatcher->dispatch($event, $event::ACTION);

            if ($event->hasResponse()) {
                $credits = $event->response();
            } else {
                $credits = array(
                    'conges_credit'       => $params['conges_credit'] *= 7,
                    'conges_reliquat'     => $params['conges_reliquat'] *= 7,
                    'conges_anticipation' => $params['conges_anticipation'] *= 7,
                    'comp_time'           => $comp_time,
                    'conges_annuel'       => $params['conges_annuel'] *= 7,
                );
            }
        } else {

            $conges_annuel       = HourHelper::hoursMinutesToDecimal(trim($params['conges_annuel_hours']),       trim($params['conges_annuel_min']));
            $conges_anticipation = HourHelper::hoursMinutesToDecimal(trim($params['conges_anticipation_hours']), trim($params['conges_anticipation_min']));
            $conges_credit       = HourHelper::hoursMinutesToDecimal(trim($params['conges_credit_hours']),       trim($params['conges_credit_min']));
            $conges_reliquat     = HourHelper::hoursMinutesToDecimal(trim($params['conges_reliquat_hours']),     trim($params['conges_reliquat_min']));

            $credits = array(
                'conges_annuel'       => $conges_annuel,
                'conges_anticipation' => $conges_anticipation,
                'conges_credit'       => $conges_credit,
                'conges_reliquat'     => $conges_reliquat,
                'comp_time'           => $comp_time,
            );
        }

        $c = new \conges();
        $c->perso_id = $params['id'];
        $c->CSRFToken = $params['CSRFToken'];
        $c->maj($credits, $params['action']);

        return $credits;
    }

    private function login($firstname = '', $lastname = '', $mail = '')
    {

        $firstname = trim($firstname);
        $lastname = trim($lastname);
        $mail = trim($mail);

        $tmp = array();

        switch ($this->config('Auth-LoginLayout')) {
            case 'lastname.firstname' :
                if ($lastname) {
                    $tmp[] = $lastname;
                }
                if ($firstname) {
                    $tmp[] = $firstname;
                }
                break;

            case 'mail' :
                $tmp[] = $mail;
                break;

            case 'mailPrefix' :
                $tmp[] = preg_replace('/(.[^@]*)@.*$/i', '$1', $mail);
                break;

            default :
                if ($firstname) {
                    $tmp[] = $firstname;
                }
                if ($lastname) {
                    $tmp[] = $lastname;
                }
                break;
        }

        $login = implode('.', $tmp);
        $login = removeAccents(strtolower($login));
        $login = str_replace(' ', '-', $login);
        $login = substr($login, 0, 95);

        $i = 1;
        while ($this->entityManager->getRepository(Agent::class)->findOneBy(['login' => $login])) {
            $i++;

            $tmp = explode('@', $login);

            if ($i == 2) {
                $tmp[0] .= '2';
            } else {
                $tmp[0] = substr($tmp[0], 0, strlen($tmp[0]) -1) . $i;
            }

            $login = $tmp[0];

            if (!empty($tmp[1])) {
                $login .= '@' . $tmp[1];
            }
        }

        return $login;
    }

    // Ajout des noms dans les tableaux postes attribués et dispo
    private function postesNoms($postes, $tab_noms)
    {
        $tmp = array();
        if (is_array($postes)) {
            foreach ($postes as $elem) {
                if (is_array($tab_noms)) {
                    foreach ($tab_noms as $noms) {
                        if ($elem==$noms[1]) {
                            $tmp[] = array($elem,$noms[0]);
                            break;
                        }
                    }
                }
            }
        }
        usort($tmp, "cmp_1");
        return $tmp;
    }
 
}
