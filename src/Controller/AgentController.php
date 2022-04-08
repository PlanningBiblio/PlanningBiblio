<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Event\OnTransformLeaveDays;
use App\PlanningBiblio\Event\OnTransformLeaveHours;
use App\PlanningBiblio\Helper\HolidayHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__ . "/../../public/personnel/class.personnel.php");
require_once(__DIR__ . "/../../public/activites/class.activites.php");
require_once(__DIR__ . "/../../public/planningHebdo/class.planningHebdo.php");
require_once(__DIR__ . "/../../public/conges/class.conges.php");

class AgentController extends BaseController
{
    /**
     * @Route("/agent", name="agent.index", methods={"GET"})
     */
    public function index(Request $request){

        $actif = $request->get('actif');
        $lang = $GLOBALS['lang'];
        $droits = $GLOBALS['droits'];
        $login_id = $_SESSION['login_id'];
        $LDAP_host = $this->config('LDAP-Host');
        $LDAP_suf = $this->config('LDAP-Suffix');

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
            "LDAP_host"              => $LDAP_host,
            "LDAP_suf"               => $LDAP_suf,
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

    /**
     * @Route("/agent/add", name="agent.add", methods={"GET"})
     * @Route("/agent/{id}", name="agent.edit", methods={"GET"})
     */
    public function add(Request $request)
    {
        $id = $request->get('id');
        $CSRFSession = $GLOBALS['CSRFSession'];
        $lang = $GLOBALS['lang'];
        $currentTab = '';
        global $temps;

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
            $db->sanitize_string = false;
            $db->select2("personnel", "*", array("id"=>$id));
            $actif = $db->result[0]['actif'];
            $nom = $db->result[0]['nom'];
            $prenom = $db->result[0]['prenom'];
            $mail = $db->result[0]['mail'];
            $statut = $db->result[0]['statut'];
            $categorie = $db->result[0]['categorie'];
            $check_hamac = $db->result[0]['check_hamac'];
            $check_ics = json_decode($db->result[0]['check_ics'], true);
            $service = $db->result[0]['service'];
            $heuresHebdo = $db->result[0]['heures_hebdo'];
            $heuresTravail = $db->result[0]['heures_travail'];
            $arrivee = dateFr($db->result[0]['arrivee']);
            $depart = dateFr($db->result[0]['depart']);
            $login = $db->result[0]['login'];
            if ($this->config('PlanningHebdo')) {
                $p = new \planningHebdo();
                $p->perso_id = $id;
                $p->debut = date("Y-m-d");
                $p->fin = date("Y-m-d");
                $p->valide = true;
                $p->fetch();
                if (!empty($p->elements)) {
                    $temps = $p->elements[0]['temps'];
                } else {
                    $temps = array();
                }
            } else {
                $temps = json_decode(html_entity_decode($db->result[0]['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                if (!is_array($temps)) {
                    $temps = array();
                }
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
            $check_hamac = 1;
            $check_ics = array(1,1,1);
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
            if ($_SESSION['perso_actif'] and $_SESSION['perso_actif']!="Supprim&eacute;") {
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

        foreach ($activites as $elem) {
            $postes_completNoms[] = array($elem['nom'],$elem['id']);
            $postes_complet[] = $elem['id'];
        }

        // les activités non attribuées (disponibles)
        $postes_dispo = array();
        if ($postes_attribues) {
            $postes = implode(",", $postes_attribues);    //    activités attribuées séparées par des virgules (valeur transmise à valid.php)
            if (is_array($postes_complet)) {
                foreach ($postes_complet as $elem) {
                    if (!in_array($elem, $postes_attribues)) {
                        $postes_dispo[] = $elem;
                    }
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

        // Ajout des noms dans les tableaux postes attribués et dispo
        function postesNoms($postes, $tab_noms)
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
        $postes_attribues = postesNoms($postes_attribues, $postes_completNoms);
        $postes_dispo = postesNoms($postes_dispo, $postes_completNoms);

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
            'informations_str'  => str_replace("\n", "<br/>", $informations),
            'recup'             => $recup,
            'recup_str'         => str_replace("\n", "<br/>", $recup),
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
            or $this->config('Hamac-csv')) {
            $this->templateParams(array( 'agendas_and_sync' => 1 ));
        }

        if (in_array(21, $droits)) {
            $h = array();
            for ($i = 1; $i<40; $i++) {
                if ($this->config('Granularite') == 5) {
                    $h[] = array($i,$i."h00");
                    $h[] = array($i.".08",$i."h05");
                    $h[] = array($i.".17",$i."h10");
                    $h[] = array($i.".25",$i."h15");
                    $h[] = array($i.".33",$i."h20");
                    $h[] = array($i.".42",$i."h25");
                    $h[] = array($i.".5",$i."h30");
                    $h[] = array($i.".58",$i."h35");
                    $h[] = array($i.".67",$i."h40");
                    $h[] = array($i.".75",$i."h45");
                    $h[] = array($i.".83",$i."h50");
                    $h[] = array($i.".92",$i."h55");
                } elseif ($this->config('Granularite')  == 15) {
                    $h[] = array($i,$i."h00");
                    $h[] = array($i.".25",$i."h15");
                    $h[] = array($i.".5",$i."h30");
                    $h[] = array($i.".75",$i."h45");
                } elseif ($this->config('Granularite') == 30) {
                    $h[] = array($i,$i."h00");
                    $h[] = array($i.".5",$i."h30");
                } else {
                    $h[] = array($i,$i."h00");
                }
            }
            $this->templateParams(array( 'times' => $h ));
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
            $ics_pattern = !empty($this->config('ICS-Pattern1')) ? $this->config('ICS-Pattern1') : 'Serveur ICS N&deg;1';
            $this->templateParams(array(
                'ics_pattern'     => $ics_pattern,
                'check_ics'       => !empty($check_ics[0]) ? 1 : 0,
            ));
        }

        if ($this->config('ICS-Server2')) {
            $ics_pattern = !empty($this->config('ICS-Pattern2')) ? $this->config('ICS-Pattern2') : 'Serveur ICS N&deg;2';
            $this->templateParams(array(
                'ics_pattern2'     => $ics_pattern,
                'check_ics2'       => !empty($check_ics[1]) ? 1 : 0,
            ));
        }

        // URL du flux ICS à importer
        if ($this->config('ICS-Server3')) {
            $this->templateParams(array(
                'check_ics3' => !empty($check_ics[2]) ? 1 : 0,
                'url_ics'    => $url_ics,
            ));
        }

        // URL du fichier ICS Planning Biblio
        if ($id and isset($ics)) {
            if ($this->config('ICS-Code')) {
            }
        }

        $rights = array();
        foreach ($groupes as $elem) {
            // N'affiche pas les droits d'accès à la configuration (réservée au compte admin)
            if ($elem['groupe_id'] == 20) {
                continue;
            }

            // N'affiche pas les droits de gérer les congés si le module n'est pas activé
            if (!$this->config('Conges-Enable') and in_array($elem['groupe_id'], array(401, 601))) {
                continue;
            }

            // N'affiche pas les droits de gérer les plannings de présence si le module n'est pas activé
            if (!$this->config('PlanningHebdo') and $elem['groupe_id'] == 24) {
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
                if (!$this->config('Conges-Enable') and in_array($elem['groupe_id'], array(401, 601))) {
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

            $annuelHeures = $conges['annuelHeures'] ? $conges['annuelHeures'] : 0;
            $annuelString = heure4($conges['annuel']);

            $creditHeures = $conges['creditHeures'] ? $conges['creditHeures'] : 0;
            $creditString = heure4($conges['credit']);

            $reliquatHeures = $conges['reliquatHeures'] ? $conges['reliquatHeures'] : 0;
            $reliquatString = heure4($conges['reliquat']);

            $anticipationHeures = $conges['anticipationHeures'] ? $conges['anticipationHeures'] : 0;
            $anticipationString = heure4($conges['anticipation']);

            $recupHeures = $conges['recupHeures'] ? $conges['recupHeures'] : 0;
            $recupString = heure4($conges['recup']);

            if ($this->config('Conges-Mode') == 'jours' ) {
                $event = new OnTransformLeaveHours($conges);
                $this->dispatcher->dispatch($event::ACTION, $event);

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
                'annuel_min'            => $conges['annuelCents'],
                'annuel_string'         => $annuelString,
                'credit_heures'         => $creditHeures,
                'credit_min'            => $conges['creditCents'],
                'credit_string'         => $creditString,
                'reliquat_heures'       => $reliquatHeures,
                'reliquat_min'          => $conges['reliquatCents'],
                'reliquat_string'       => $reliquatString,
                'anticipation_heures'   => $anticipationHeures,
                'anticipation_min'      => $conges['anticipationCents'],
                'anticipation_string'   => $anticipationString,
                'recup_heures'          => $recupHeures,
                'recup_min'             => $conges['recupCents'],
                'recup_string'          => $recupString,
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

    /**
     * @Route("/agent", name="agent.save", methods={"POST"})
     */
    public function save(Request $request)
    {

        $params = $request->request->all();

        $arrivee = filter_input(INPUT_POST, "arrivee", FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $CSRFToken = filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING);
        $depart = filter_input(INPUT_POST, "depart", FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $heuresHebdo = filter_input(INPUT_POST, "heuresHebdo", FILTER_SANITIZE_STRING);
        $heuresTravail = filter_input(INPUT_POST, "heuresTravail", FILTER_SANITIZE_STRING);
        $id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
        $mail = trim(filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL));

        $actif = htmlentities($params['actif'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $action = $params['action'];
        $check_hamac = !empty($params['check_hamac']) ? 1 : 0;
        $check_ics1 = !empty($params['check_ics1']) ? 1 : 0;
        $check_ics2 = !empty($params['check_ics2']) ? 1 : 0;
        $check_ics3 = !empty($params['check_ics3']) ? 1 : 0;
        $check_ics = "[$check_ics1,$check_ics2,$check_ics3]";
        $droits = array_key_exists("droits", $params) ? $params['droits'] : null;
        $categorie = trim($params['categorie']);
        $informations = trim($params['informations']);
        $mailsResponsables = trim(str_replace(array("\n", " "), null, $params['mailsResponsables']));
        $matricule = trim($params['matricule']);
        $url_ics = isset($params['url_ics']) ? trim($params['url_ics']) : null;
        $nom = trim($params['nom']);
        $postes = $params['postes'];
        $prenom = trim($params['prenom']);
        $recup = isset($params['recup']) ? trim($params['recup']) : null;
        $service = $params['service'];
        $sites = array_key_exists("sites", $params) ? $params['sites'] : null;
        $statut = $params['statut'];
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

        $droits = $droits ? $droits : array();
        $postes = $postes ? json_encode(explode(",", $postes)) : null;
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
                $droits[] = 9;
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

            $login = $this->login($nom, $prenom);

            // Demo mode
            if (!empty($this->config('demo'))) {
                $mdp_crypt = password_hash("password", PASSWORD_BCRYPT);
                $msg = "Vous utilisez une version de démonstration : l'agent a été créé avec les identifiants $login / password";
                $msg .= "#BR#Sur une version standard, les identifiants de l'agent lui auraient été envoyés par e-mail.";
                $msgType = "success";
            } else {
                $mdp = gen_trivial_password();
                $mdp_crypt = password_hash($mdp, PASSWORD_BCRYPT);

                // Envoi du mail
                $message = "Votre compte Planning Biblio a &eacute;t&eacute; cr&eacute;&eacute; :";
                $message.= "<ul><li>Login : $login</li><li>Mot de passe : $mdp</li></ul>";

                $m = new \CJMail();
                $m->subject = "Création de compte";
                $m->message = $message;
                $m->to = $mail;
                $m->send();

                // Si erreur d'envoi de mail, affichage de l'erreur
                $msg = null;
                $msgType = null;
                if ($m->error) {
                    $msg = $m->error_CJInfo;
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
                "check_hamac"=>$check_hamac
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
            $message = "Votre mot de passe Planning Biblio a &eacute;t&eacute; modifi&eacute;";
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
                "check_hamac"=>$check_hamac
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

    private function save_holidays($params)
    {
        if (!$this->config('Conges-Enable')) {
            return array();
        }

        $available_keys = array(
            'conges_credit',
            'conges_reliquat',
            'conges_anticipation',
            'recup',
            'conges_annuel');

        foreach ($available_keys as $key) {
            $params[$key . '_min'] = isset($params[$key . '_min']) ? $params[$key . '_min'] : 0;
        }

        if ($this->config('Conges-Mode') == 'jours' ) {
            $event = new OnTransformLeaveDays($params);
            $this->dispatcher->dispatch($event::ACTION, $event);

            if ($event->hasResponse()) {
                $credits = $event->response();
            } else {
                $credits = array(
                    'conges_credit' => $params['conges_credit'] *= 7,
                    'conges_reliquat' => $params['conges_reliquat'] *= 7,
                    'conges_anticipation' => $params['conges_anticipation'] *= 7,
                    'comp_time' => $params['comp_time'] + $params['comp_time_min'],
                    'conges_annuel' => $params['conges_annuel'] *= 7,
                );
            }
        } else {
            $credits = array(
                'conges_credit' => $params['conges_credit'] + $params['conges_credit_min'],
                'conges_reliquat' => $params['conges_reliquat'] + $params['conges_reliquat_min'],
                'conges_anticipation' => $params['conges_anticipation'] + $params['conges_anticipation_min'],
                'comp_time' => $params['comp_time'] + $params['comp_time_min'],
                'conges_annuel' => $params['conges_annuel'] + $params['conges_annuel_min'],
            );
        }

        $c = new \conges();
        $c->perso_id = $params['id'];
        $c->CSRFToken = $params['CSRFToken'];
        $c->maj($credits, $params['action']);

        return $credits;
    }

    private function login($nom, $prenom)
    {
        $prenom = trim($prenom);
        $nom = trim($nom);
        if ($prenom) {
            $tmp[] = $prenom;
        }
        if ($nom) {
            $tmp[] = $nom;
        }

        $tmp = implode('.', $tmp);
        $login = removeAccents(strtolower($tmp));
        $login = str_replace(" ", "-", $login);
        $login = substr($login, 0, 95);

        $i = 1;
        $db = new \db();
        $db->select2("personnel", "*", array("login"=>$login));
        while ($db->result) {
            $i++;
            if ($i == 2) {
                $login.= "2";
            } else {
                $login = substr($login, 0, strlen($login)-1).$i;
            }
            $db = new \db();
            $db->select("personnel", null, "login='$login'");
        }
        return $login;
    }
}
