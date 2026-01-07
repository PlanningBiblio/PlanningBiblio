<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\SaturdayWorkingHours;
use App\Planno\Event\OnTransformLeaveDays;
use App\Planno\Event\OnTransformLeaveHours;
use App\Planno\Helper\HolidayHelper;
use App\Planno\Helper\HourHelper;
use App\Planno\Ldif2Array;

use App\Entity\Agent;
use App\Entity\Access;
use App\Entity\PlanningPosition;
use App\Entity\SelectCategories;
use App\Entity\SelectStatuts;
use App\Entity\SelectServices;
use App\Entity\Skill;
use App\Entity\WorkingHour;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . '/../../legacy/Class/class.personnel.php');
require_once(__DIR__ . '/../../legacy/Class/class.activites.php');
require_once(__DIR__ . '/../../legacy/Class/class.planningHebdo.php');
require_once(__DIR__ . '/../../legacy/Class/class.conges.php');
require_once(__DIR__ . '/../../legacy/Class/class.ldap.php');

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
        $this->entityManager->getRepository(Agent::class)->updateAsDeletedByDepartDate();

        $agentsTab = $this->entityManager->getRepository(Agent::class)->findNamesByActif($actif);

        $nbSites = $this->config('Multisites-nombre');
        $agents = array();
        foreach ($agentsTab as $agent) {
            $elem = [];
            $id = $agent['id'];
            $arrivee = dateFr($agent['arrivee'] ? $agent['arrivee']->format('Y-m-d') : '');
            $depart = dateFr($agent['depart'] ? $agent['depart']->format('Y-m-d') : '');
            $last_login = date_time($agent['last_login'] ? $agent['last_login']->format('Y-m-d H:i:s') : '0000-00-00 00:00:00');
            $heures = $agent['heures_hebdo'] ? $agent['heures_hebdo'] : null;
            $heures = heure4($heures);
            if (is_numeric($heures)) {
                $heures.= "h00";
            }
            $agent['service'] = str_replace("`", "'", $agent['service']);

            $sites = json_decode($agent['sites'], true);
            if ($nbSites > 1) {
                $tmp = array();
                if (!empty($sites)) {
                    foreach ($sites as $site) {
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

        $statuts = $this->entityManager->getRepository(SelectStatuts::class)->findAll();

        $contrats = array("Titulaire","Contractuel");

        // Liste des services
        $services = $this->entityManager->getRepository(SelectServices::class)->findAll();

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
        $activites = $this->entityManager->getRepository(Skill::class)->findAll();

        $postes_completNoms = array();
        foreach ($activites as $elem) {
            $postes_completNoms[] = array($elem->getName(),$elem->getId());
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
        $canChangePassword = true;

        if ($_SESSION['oups']['Auth-Mode'] == 'CAS'
            or ($this->config('Auth-Mode') == 'LDAP' and $perso_id != 1))
        {
            $canChangePassword = false;
        }

        $this->templateParams([
            'canChangePassword' => $canChangePassword,
        ]);

        return $this->output('/agents/password.html.twig');
    }

    #[Route(path: '/agent/add', name: 'agent.add', methods: ['GET'])]
    #[Route(path: '/agent/{id<\d+>}', name: 'agent.edit', methods: ['GET'])]
    public function edit(Request $request)
    {
        $id = $request->get('id');

        $agent = $id ? $this->entityManager->getRepository(Agent::class)->find($id) : new Agent();
        // TODO : use $agent for edit and add. Default values will be set by App\Entity\Agent, therefore, we won't need to initialize them with "if ($id)" / "else" on line 296+

        $CSRFSession = $GLOBALS['CSRFSession'];
        $lang = $GLOBALS['lang'];
        $currentTab = '';
        global $temps;
        global $breaktimes;

        $actif = null;
        $droits = $GLOBALS['droits'];
        $admin = in_array(21, $droits);

        $groupesAcces = $this->entityManager->getRepository(Access::class)->findGroupesAcces();
        // Tous les droits d'accés
        $groupes = array();
        foreach ($groupesAcces as $elem) {
            if (empty($elem['categorie'])) {
                $elem['categorie'] = 'Divers';
                $elem['ordre'] = '200';
            }
            $groupes[$elem['groupe_id']] = $elem;
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

        $statuts = $this->entityManager->getRepository(SelectStatuts::class)->findAll();
        $categories = $this->entityManager->getRepository(SelectCategories::class)->findAll();
        $statuts_utilises = $this->entityManager->getRepository(Agent::class)->findDistinctStatuts();

        // Liste des services
        $services = $this->entityManager->getRepository(SelectServices::class)->findAll();

        // Liste des services utilisés
        $services_utilises = $this->entityManager->getRepository(Agent::class)->findDistinctServices();

        $acces = array();
        $postes_attribues = array();
        $recupAgents = array("Prime","Temps");
        // récupération des infos de l'agent en cas de modif
        $ics = null;
        if ($id) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($id);
            $arrivee = $agent->getArrival() ? $agent->getArrival()->format('Y-m-d') : '';
            $depart = $agent->getDeparture() ? $agent->getDeparture()->format('Y-m-d') : '';
            $breaktimes = array();
            if ($this->config('PlanningHebdo')) {
                $workingHour = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $id, 'debut' => new \DateTime(), 'fin' => new \DateTime(), 'valide' => true]);
                $temps = $workingHour ? $workingHour->getWorkingHours() : array();
                $breaktimes = $workingHour ? $workingHour->getBreaktime() : array();
            } else {
                $temps = json_decode(html_entity_decode($agent->getWorkingHours(), ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                if (!is_array($temps)) {
                    $temps = array();
                }
            }
            // Decimal breaktime to time (H:i).
            foreach ($breaktimes as $index => $time) {
                $breaktimes[$index] = $breaktimes[$index]
                    ? gmdate('H:i', floor($breaktimes[$index] * 3600)) : '';
            }

            $postes_attribues = json_decode(html_entity_decode($agent->getSkills(), ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            if (is_array($postes_attribues)) {
                sort($postes_attribues);
            }
            $acces = $agent->getACL();
            $managersMails = explode(";", html_entity_decode($agent->getManagersMails(), ENT_QUOTES|ENT_IGNORE, "UTF-8"));
            // $managersMails : html_entity_decode necéssaire sinon ajoute des espaces après les accents ($managersMails=implode("; ",$managersMails);)
            $informations = stripslashes($agent->getInformation());
            $recup = stripslashes($agent->getRecoveryMenu());
            $sites = html_entity_decode($agent->getSites(), ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $sites = $sites !== '' && $sites !== '0'?json_decode($sites, true):array();
            $action = "modif";
            $titre = $agent->getLastname()." ".$agent->getFirstname();

            // URL ICS
            if ($this->config('ICS-Export')) {
                $ics = $this->entityManager->getRepository(Agent::class)->getICSURL($id);
            }
        } else {// pas d'id, donc ajout d'un agent
            $id = null;
            $titre = "Ajout d'un agent";
            $action = "ajout";
            $arrivee = '';
            $depart = '';
            $managersMails = [];
            $informations = "";
            $recup = "";
            $sites = array();
            if (!empty($_SESSION['perso_actif']) and $_SESSION['perso_actif'] != 'Supprim&eacute;') {
                $actif = $_SESSION['perso_actif'];
            }// vérifie dans quel tableau on se trouve pour la valeur par défaut
        }

        $jours = array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
        $contrats = array("Titulaire","Contractuel");

        //        --------------        Début listes des activités        ---------------------//
        // Toutes les activités
        $activites = $this->entityManager->getRepository(Skill::class)->findAll();

        $postes_complet = array();
        $postes_completNoms = array();

        foreach ($activites as $elem) {
            $postes_completNoms[] = array($elem->getName(),$elem->getId());
            $postes_complet[] = $elem->getId();
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
            'agent'            => $agent,
            'arrival'          => $arrivee,
            'departure'        => $depart,
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
            'MSGraphConfig'     => !empty($this->config('MSGraph-ClientID')),
            'ics'               => $ics,
            'CSRFSession'       => $CSRFSession,
            'action'            => $action,
            'id'                => $id,
            'statuts'           => $statuts,
            'statuts_utilises'  => $statuts_utilises,
            'categories'        => $categories,
            'contrats'          => $contrats,
            'services'          => $services,
            'services_utilises' => $services_utilises,
            'actif'             => $actif,
            'mailsResponsables' => $managersMails,
            'mailsResp_joined'  => implode("; ", $managersMails),
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
            or !empty($this->config('MSGraph-ClientID'))) {
            $this->templateParams(array( 'agendas_and_sync' => 1 ));
        }

        if (in_array(21, $droits)) {
            $granularite = $this->config('Granularite') == 1
                ? 5 : $this->config('Granularite');

            $nb_interval = 60 / $granularite;
            $end = 40;
            $times = array();
            for ($i = 1; $i < $end; $i++) {
                $times[] = array($i, $i . 'h00');
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
            $heuresHebdo_label = $agent->getWeeklyServiceHours();
            if (!stripos($agent->getWeeklyServiceHours(), "%")) {
                $heuresHebdo_label .= " heures";
            }
            $this->templateParams(array(
                'heuresHebdo_label'   => $heuresHebdo_label,
                'heuresTravail_label' => $agent->getWeeklyWorkingHours() . " heures",
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

        include(__DIR__ . '/../../legacy/Agent/hours_tables.php');
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
                $elem['checked'] = in_array($elem['groupe_id'], $acces);
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
                        $checked = in_array($groupe_id, $acces);
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
            $conges = $this->entityManager->getRepository(Agent::class)->fetchCredit($id);
            $holiday_helper = new HolidayHelper();

            $annuelHeures  = $conges['annuelHeures']  ?? 0;
            $annuelMinutes = $conges['annuelMinutes'] ?? 0;
            $annuelString  = '';

            $creditHeures  = $conges['creditHeures']  ?? 0;
            $creditMinutes = $conges['creditMinutes'] ?? 0;
            $creditString  = '';

            $reliquatHeures  = $conges['reliquatHeures']  ?? 0;
            $reliquatMinutes = $conges['reliquatMinutes'] ?? 0;
            $reliquatString  = '';

            $anticipationHeures  = $conges['anticipationHeures']  ?? 0;
            $anticipationMinutes = $conges['anticipationMinutes'] ?? 0;
            $anticipationString  = '';

            $recupHeures  = $conges['recupHeures']  ?? 0;
            $recupMinutes = $conges['recupMinutes'] ?? 0;

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

                $hours_per_day = $id ? HourHelper::decimalToHoursMinutes($holiday_helper->hoursPerDay($id))['as_string'] : '';

                $templateParams['annuel_jours']       = $id ? $holiday_helper->hoursToDays($conges['annuel'], $id, null, true)       : '';
                $templateParams['credit_jours']       = $id ? $holiday_helper->hoursToDays($conges['credit'], $id, null, true)       : '';
                $templateParams['reliquat_jours']     = $id ? $holiday_helper->hoursToDays($conges['reliquat'], $id, null, true)     : '';
                $templateParams['anticipation_jours'] = $id ? $holiday_helper->hoursToDays($conges['anticipation'], $id, null, true) : '';
                $templateParams['hours_per_day']      = $hours_per_day;
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
    public function save(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {

        $params = $request->request->all();
        $arrivee = $request->get('arrivee') ? new \DateTime($request->get('arrivee')) : null;
        $depart = $request->get('depart') ? new \DateTime($request->get('depart')) : null;
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
        $check_ics = [$check_ics1,$check_ics2,$check_ics3];
        $droits = array_key_exists("droits", $params) ? $params['droits'] : null;
        $categorie = isset($params['categorie']) ? trim($params['categorie']) : null;
        $informations = isset($params['informations']) ? trim($params['informations']) : null;
        $managersMails = isset($params['mailsResponsables']) ? trim(str_replace(array("\n", " "), '', $params['mailsResponsables'])) : null;
        $matricule = isset($params['matricule']) ? trim($params['matricule']) : null;
        $url_ics = isset($params['url_ics']) ? trim($params['url_ics']) : null;
        $nom = trim($params['nom']);
        $postes = $params['postes'] ?? null;
        $prenom = trim($params['prenom']);
        $recup = isset($params['recup']) ? trim($params['recup']) : '';
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

        $firstMonday = array_key_exists("premierLundi", $params) ? $params['premierLundi'] : null;
        $lastMonday = array_key_exists("dernierLundi", $params) ? $params['dernierLundi'] : null;

        if (is_array($temps)) {
            foreach ($temps as $day => $hours) {
                foreach ($hours as $i => $hour) {
                    $temps[$day][$i] = HourHelper::toHis($hour);
                }
            }
        }

        $droits = $droits ? $droits : array();
        $postes = $postes ? json_encode(explode(",", $postes)) : '[]';
        $sites = $sites ? json_encode($sites) : '';
        $temps = $temps ? json_encode($temps) : '';

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

        switch ($action) {
          case "ajout":
            $id = $this->entityManager->getRepository(Agent::class)->findMaxId() + 1;

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
            $agentInsert = new Agent();
            $agentInsert->setLastname($nom);
            $agentInsert->setFirstname($prenom);
            $agentInsert->setMail($mail);
            $agentInsert->setStatus($statut);
            $agentInsert->setCategory($categorie);
            $agentInsert->setService($service);
            $agentInsert->setWeeklyServiceHours($heuresHebdo);
            $agentInsert->setWeeklyWorkingHours($heuresTravail);
            $agentInsert->setArrival($arrivee);
            $agentInsert->setDeparture($depart);
            $agentInsert->setLogin($login);
            $agentInsert->setPassword($mdp_crypt);
            $agentInsert->setActive($actif);
            $agentInsert->setACL($droits);
            $agentInsert->setSkills($postes);
            $agentInsert->setWorkingHours($temps);
            $agentInsert->setInformation($informations);
            $agentInsert->setRecoveryMenu($recup);
            $agentInsert->setSites($sites);
            $agentInsert->setManagersMails($managersMails);
            $agentInsert->setEmployeeNumber($matricule);
            $agentInsert->setIcsUrl($url_ics);
            $agentInsert->setIcsCheck($check_ics);
            $agentInsert->setHamacCheck($check_hamac);
            $agentInsert->setMsGraphCheck($mSGraphCheck);

            $holidays = $this->save_holidays($params);
            $agentInsert->setHolidayCompTime($holidays['comp_time']);
            $agentInsert->setHolidayAnnualCredit($holidays['conges_annuel']);
            $agentInsert->setHolidayAnticipation($holidays['conges_anticipation']);
            $agentInsert->setHolidayCredit($holidays['conges_credit']);
            $agentInsert->setHolidayRemainder($holidays['conges_reliquat']);

            $this->entityManager->persist($agentInsert);
            $this->entityManager->flush();

            // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
            if ($this->config['EDTSamedi'] and !$this->config['PlanningHebdo']) {
                $repo = $this->entityManager->getRepository(SaturdayWorkingHours::class);
                $repo->update($eDTSamedi, $firstMonday, $lastMonday, $id);
            }

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
            $login = $this->entityManager->getRepository(Agent::class)->find($id)->getLogin();

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

            $this->entityManager->getRepository(Agent::class)->find($id)->setPassword($mdp_crypt);
            $this->entityManager->flush();
            return $this->redirectToRoute('agent.index', array('msg' => $msg, 'msgType' => $msgType));

            break;

          case "modif":
            $agentUpdate = $this->entityManager->getRepository(Agent::class)->find($id);
            $agentUpdate->setLastname($nom);
            $agentUpdate->setFirstname($prenom);
            $agentUpdate->setMail($mail);
            $agentUpdate->setStatus($statut);
            $agentUpdate->setCategory($categorie);
            $agentUpdate->setService($service);
            $agentUpdate->setWeeklyServiceHours($heuresHebdo);
            $agentUpdate->setWeeklyWorkingHours($heuresTravail);
            $agentUpdate->setActive($actif);
            $agentUpdate->setACL($droits);
            $agentUpdate->setArrival($arrivee);
            $agentUpdate->setDeparture($depart);
            $agentUpdate->setSkills($postes);
            $agentUpdate->setInformation($informations);
            $agentUpdate->setRecoveryMenu($recup);
            $agentUpdate->setSites($sites);
            $agentUpdate->setManagersMails($managersMails);
            $agentUpdate->setEmployeeNumber($matricule);
            $agentUpdate->setIcsUrl($url_ics);
            $agentUpdate->setIcsCheck($check_ics);
            $agentUpdate->setHamacCheck($check_hamac);
            $agentUpdate->setMsGraphCheck($mSGraphCheck);
            // Si le champ "actif" passe de "supprimé" à "service public" ou "administratif", on réinitialise les champs "supprime" et départ
            if (!strstr($actif, "Supprim")) {
                $agentUpdate->setDeletion("0");
                // Si l'agent était supprimé et qu'on le réintégre, on change sa date de départ
                // pour qu'il ne soit pas supprimé de la liste des agents actifs
                if (strstr($agentUpdate->getActive(), "Supprim") and $agentUpdate->getDeparture() <= date("Y-m-d")) {
                    $agentUpdate->setDeparture("0000-00-00");
                }
            } else {
                $agentUpdate->setActive("Supprimé;");
            }

            // Mise à jour de l'emploi du temps si modifié à partir de la fiche de l'agent
            if ($temps) {
                $update["temps"] = $temps;
            }

            $holidays = $this->save_holidays($params);
            $agentUpdate->setHolidayCompTime($holidays['comp_time']);
            $agentUpdate->setHolidayAnnualCredit($holidays['conges_annuel']);
            $agentUpdate->setHolidayAnticipation($holidays['conges_anticipation']);
            $agentUpdate->setHolidayCredit($holidays['conges_credit']);
            $agentUpdate->setHolidayRemainder($holidays['conges_reliquat']);

            $this->entityManager->persist($agentUpdate);
            $this->entityManager->flush();

            // Mise à jour de la table pl_poste en cas de modification de la date de départ
            $this->entityManager->getRepository(PlanningPosition::class)->updateDeletionByUserId($id);
            if ($depart != "0000-00-00" and $depart != "") {
                // Si une date de départ est précisée, on met supprime=1 au dela de cette date
                $this->entityManager->getRepository(PlanningPosition::class)->deleteAfterDate($id, $depart->format('Y-m-d'));
            }

            // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
            if ($this->config['EDTSamedi'] and !$this->config['PlanningHebdo']) {
                $repo = $this->entityManager->getRepository(SaturdayWorkingHours::class);
                $repo->update($eDTSamedi, $firstMonday, $lastMonday, $id);
            }

            return $this->redirectToRoute('agent.index');

            break;
        }
    }

    private function changeAgentPassword(Request $request, $agent_id, $password): \Symfony\Component\HttpFoundation\Response {

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
        $agent->setPassword($password);
        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        $response->setContent('Password successfully changed');
        $response->setStatusCode(200);

        return $response;
    }

    #[Route(path: '/ajax/change-own-password', name: 'ajax.changeownpassword', methods: ['POST'])]
    public function changeOwnPassword(Request $request): \Symfony\Component\HttpFoundation\Response
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
    public function check_password(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $password = $request->get('password');
        $response = new Response();
        $ok = $this->check_password_complexity($password);
        $response->setContent($ok ? "ok" : "not ok");
        $response->setStatusCode(200);

        return $response;
    }

    // Returns true if the password is complex enough, and false otherwise
    private function check_password_complexity($password): bool
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
    public function isCurrentPassword(Request $request): \Symfony\Component\HttpFoundation\Response
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
        $hashedPassword = $agent->getPassword();
	
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

        if ($login == $agent->getLogin()) {
            $response->setContent('identic');
            $response->setStatusCode(400);

            return $response;
        }

        if ($duplicate && $login != $agent->getLogin()) {
            $response->setContent('duplicate');
            $response->setStatusCode(400);

            return $response;
        }

        $agent = $this->entityManager->find(Agent::class, $agent_id);
        $agent->setLogin($login);
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
            $login = $this->entityManager->getRepository(Agent::class)->findAllLoginsNotDeleted();
            foreach ($login as $elem) {
                $agents_existants[] = $elem['login'];
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
    public function ldap_import(Request $request, Session $session): \Symfony\Component\HttpFoundation\RedirectResponse
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

                    $agentInsert = new Agent();
                    $agentInsert->setLogin($login);
                    $agentInsert->setLastname($nom);
                    $agentInsert->setFirstname($prenom);
                    $agentInsert->setMail($mail);
                    $agentInsert->setEmployeeNumber($matricule);
                    $agentInsert->setPassword($password);
                    $agentInsert->setACL($droits);
                    $agentInsert->setArrival($date);
                    $agentInsert->setSkills($postes);
                    $agentInsert->setActive($actif);
                    $agentInsert->setInformation($commentaires);
                    $this->entityManager->persist($agentInsert);
                }
            }

            $this->entityManager->flush();

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
                if ($agent->getLogin() == $key) {
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
    public function ldif_import(Request $request, Session $session): \Symfony\Component\HttpFoundation\RedirectResponse
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

        $results = $this->ldif_search($uids);

        foreach ($results as $elem) {
            $agentInsert = new Agent();
            $agentInsert->setLogin($elem['login']);
            $agentInsert->setLastname($elem['sn']);
            $agentInsert->setFirstname($elem['givenname']);
            $agentInsert->setMail($elem['mail']);
            $agentInsert->setEmployeeNumber($elem['matricule']);
            $agentInsert->setPassword('LDIF import, the password is not stored');
            $agentInsert->setACL([99,100]);
            $agentInsert->setArrival(new \DateTime());
            $agentInsert->setSkills('[]');
            $agentInsert->setActive('Actif');
            $agentInsert->setInformation('Importation LDIF ' . date('Y-m-d H:i:s'));
            $this->entityManager->persist($agentInsert);
        }

        $this->entityManager->flush();

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


    /**
     * @return mixed[]|non-empty-array[]
     */
    private function ldif_search($searchTerms): array {

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

        foreach ($ld->entries as $entry) {
            $keep = false;

            $elem = array_change_key_case($entry, CASE_LOWER);

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
                    $result['login'] = $result[strtolower($this->config('LDIF-ID-Attribute'))];
                    $result['matricule'] = $result[strtolower($this->config('LDIF-Matricule'))] ?? null;

                    $results[$id] = $result;
                }
            }
        }

        return $results;
    }

    #[Route(path: '/agent', name: 'agent.delete', methods: ['DELETE'])]
    public function deleteAgent(Request $request, Session $session): \Symfony\Component\HttpFoundation\JsonResponse
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
            $agentUpdate = $this->entityManager->getRepository(Agent::class)->find($id);
            $agentUpdate->setDeletion(1);
            $agentUpdate->setActive("Supprimé");
            $agentUpdate->setDeparture(new \DateTime($date));

            // Mise à jour de la table pl_poste
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('pl_poste', array('supprime'=>1), array('perso_id' => "$id", 'date' =>">$date"));
            $p = $this->entityManager->getRepository(Agent::class)->find($id);

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

    /*
     * Supprime les agents sélectionnés à partir de la liste des agents.
     * Les agents ne sont pas supprimés définitivement, ils sont marqués comme supprimés dans la table personnel (champ supprime=1)
     * Appelé par la fonction JS public/js/agent.js : agent_list
    */
    #[Route(path: '/agent/bulk/delete', name: 'agent.bulk.delete', methods: ['DELETE'])]
    public function bulkDelete(Request $request, Session $session)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $list = $request->get('list');
        $CSRFToken = $request->get('CSRFToken');

        $list = html_entity_decode($list, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        // prohibits removal of admin and "tout le monde"
        $tab = array();
        $tmp = json_decode($list);
        foreach ($tmp as $elem) {
            if ($elem > 2) {
                $tab[] = $elem;
            }
        }

        if ($session->get('perso_actif')=="Supprimé") {
            $this->entityManager->getRepository(Agent::class)->delete($tab);
        } else {
            // TODO : demander la date de suppression en popup
            // Date de suppression
            $date = date('Y-m-d');

            // Mise à jour de la table personnel
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update("personnel", array("supprime"=>"1","actif"=>"Supprim&eacute;","depart"=>$date), array("id"=>"IN$list"));

            // Mise à jour de la table pl_poste
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('pl_poste', array('supprime'=>1), array('perso_id' => "IN$list", 'date' =>">$date"));

            // Mise à jour de la table responsables
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete("responsables", array('responsable' => "IN$list"));
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $db->delete("responsables", array('perso_id' => "IN$list"));
        }

        $return = ["ok"];
        return new Response(json_encode($return));
    }

    /*
     * Met à jour les fiches des agents sélectionnés à partir de la liste des agents.
     * Appelé par la fonction JS public/js/agent.js : agent_list
     */
    #[Route(path: '/agent/bulk/update', name: 'agent.bulk.update', methods: ['POST'])]
    public function bulkUpdate(Request $request, Session $session)
    {
        // CSFR Protection
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        if (!in_array(21, $_SESSION['droits'])) {
            $return = ['Accès refusé'];
            return new Response(json_encode($return));
        }

        // Selected agents
        $list = $request->get('list');
        $list = html_entity_decode($list, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $list = json_decode($list);

        // Main tab
        $actif = $request->get('actif');
        $contrat = $request->get('contrat');
        $heures_hebdo = $request->get('heures_hebdo');
        $heures_travail = $request->get('heures_travail');
        $service = $request->get('service');
        $statut = $request->get('statut');

        // Skills tab
        $postes = $request->get('postes');

        if ($postes != '-1') {
            $postes = explode(',', $postes);
            $postes = json_encode($postes);
        }

        // Update DB
        $agents = $this->entityManager->getRepository(Agent::class)->findById($list);

        foreach ($agents as $agent) {
            // Main Tab
            if ($actif != '-1') {
                $agent->setActive($actif);
            }

            if ($contrat != '-1') {
                $agent->setCategory($contrat);
            }

            if ($heures_hebdo != '-1') {
                $agent->setWeeklyServiceHours($heures_hebdo);
            }

            if ($heures_travail != '-1') {
                $agent->setWeeklyWorkingHours($heures_travail);
            }

            if ($service != '-1') {
                $agent->setService($service);
            }

            if ($statut != '-1') {
                $agent->setStatus($statut);
            }

            // Skills tab
            if ($postes != '-1') {
                $agent->setSkills($postes);
            }

            $this->entityManager->persist($agent);

        }
        $this->entityManager->flush();

        $return = ['ok'];

        return new Response(json_encode($return));
    }

    /*
     * Envoi par mail à l'agent sélectionné les URL de ses agendas Planno
     * Lors de la validation du formulaire "Envoi de l'URL de l'agenda Planning Biblio" accessible depuis l'onglet Agenda des fiches "agent"
     * Appelé par $( "#ics-url-form" ).dialog({ Envoyer ]), public/js/agent.js
     */
    #[Route(path: '/agent/ics/send-url', name: 'agent.ics.send_url', methods: ['POST'])]
    public function sendIcsUrl(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $message = $request->get('message');
        $recipient = $request->get('recipient');
        $subject = $request->get('subject');

        $message = trim($message);
        $message = preg_replace("/(http:\/\/.*[^ \n])/", "<a href='$1' target='_blank'>$1</a>", $message);
        $message = str_replace(array("\n","\r"), "<br/>", $message);

        $recipient = filter_var($recipient, FILTER_SANITIZE_EMAIL);

        // Envoi du mail
        $m = new \CJMail();
        $m->subject = $subject;
        $m->message = $message;
        $m->to = $recipient;
        $isSent = $m->send();

        // retour vers la fonction JS
        if ($m->error) {
            $return = array('error' => $m->error);
        } elseif (!$isSent) {
            $return = array('error' => 'Une erreur est survenue lors de l\'envoi du mail');
        } else {
            $return = ['ok'];
        }

        return new Response(json_encode($return));
    }

    #[Route('/agent/ics/reset-url', name: 'agent.ics.reset_url', methods: ['POST'])]
    public function resetIcsUrl(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $id = $request->get('id');
        $CSRFToken = $request->get('CSRFToken');

        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

        $this->entityManager->getRepository(Agent::class)->find($id)->setICSCode(null);

        $p = new \personnel();
        $p->CSRFToken = $CSRFToken;
        $url = $p->getICSURL($id);
        $url = html_entity_decode($url, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        return new Response(json_encode(array('url' => $this->config('URL') . $url)));
    }

    /*
     * Met à jour la liste des agents dans les select des pages /absence et conges/voir.php
     * Affiche dans cette liste les agents supprimés ou non en fonction de la variable $_GET['deleted']
     * Appelé en Ajax via la fonction JS updateAgentsList à partir de la page voir.php
     */
    #[Route(path: '/agent/update-list', name: 'agent.update_list', methods: ['GET'])]
    public function updateAgentList(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $p=new \personnel();
        if ($request->get('deleted')=="yes") {
            $p->supprime=array(0,1);
        }
        $p->fetch();
        $p->elements;

        $tab=array();
        foreach ($p->elements as $elem) {
            $tab[]=array("id"=>$elem['id'],"nom"=>$elem['nom'],"prenom"=>$elem['prenom']);
        }

        return new Response(json_encode($tab));
    }

    private function save_holidays($params)
    {
        if (!$this->config('Conges-Enable')) {
            return array(
                'conges_annuel'       => 0,
                'conges_anticipation' => 0,
                'conges_credit'       => 0,
                'conges_reliquat'     => 0,
                'comp_time'           => 0,
            );;
        }

        $available_keys = ['comp_time'];

        if ($this->config('Conges-Mode') == 'heures') {
            $available_keys = [
                'comp_time',
                'conges_credit',
                'conges_reliquat',
                'conges_anticipation',
                'conges_annuel'
            ];
        }

        foreach ($available_keys as $key) {
            $params[$key . '_hours'] = !empty(trim($params[$key . '_hours'])) ? trim($params[$key . '_hours']) : 0;
            $params[$key . '_min'] = !empty(trim($params[$key . '_min'])) ? trim($params[$key . '_min']) : 0;
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

    private function login($firstname = '', $lastname = '', $mail = ''): string
    {

        $firstname = trim($firstname);
        $lastname = trim($lastname);
        $mail = trim($mail);

        $tmp = array();

        switch ($this->config('Auth-LoginLayout')) {
            case 'lastname.firstname' :
                if ($lastname !== '' && $lastname !== '0') {
                    $tmp[] = $lastname;
                }
                if ($firstname !== '' && $firstname !== '0') {
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
                if ($firstname !== '' && $firstname !== '0') {
                    $tmp[] = $firstname;
                }
                if ($lastname !== '' && $lastname !== '0') {
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
    /**
     * @return array{mixed, mixed}[]
     */
    private function postesNoms($postes, $tab_noms): array
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
