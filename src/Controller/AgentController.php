<?php

namespace App\Controller;

use App\Controller\BaseController;

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
     * @Route("/agent/edit", name="agent.new", methods={"GET"})
     * @Route("/agent/edit/{id}", name="agent.edit", methods={"GET"})
     */
    public function edit(Request $request)
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
                    $groupes_sites[]=$groupes[$groupe];
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
        $statuts_utilises=array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $statuts_utilises[]=$elem['statut'];
            }
        }

        // Liste des services
        $services = array();
        $db = new \db();
        $db->select2("select_services", null, null, "ORDER BY `rang`");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $services[]=$elem;
            }
        }

        // Liste des services utilisés
        $services_utilises = array();
        $db = new \db();
        $db->select2('personnel', 'service', null, "GROUP BY `service`");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $services_utilises[]=$elem['service'];
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
                $temps=json_decode(html_entity_decode($db->result[0]['temps'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
                if (!is_array($temps)) {
                    $temps = array();
                }
            }
            $postes_attribues = json_decode(html_entity_decode($db->result[0]['postes'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            if (is_array($postes_attribues)) {
                sort($postes_attribues);
            }
            $acces=json_decode(html_entity_decode($db->result[0]['droits'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
            $matricule=$db->result[0]['matricule'];
            $url_ics = $db->result[0]['url_ics'];
            $mailsResponsables=explode(";", html_entity_decode($db->result[0]['mails_responsables'], ENT_QUOTES|ENT_IGNORE, "UTF-8"));
            // $mailsResponsables : html_entity_decode necéssaire sinon ajoute des espaces après les accents ($mailsResponsables=join("; ",$mailsResponsables);)
            $informations=stripslashes($db->result[0]['informations']);
            $recup=stripslashes($db->result[0]['recup']);
            $sites=html_entity_decode($db->result[0]['sites'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            $sites=$sites?json_decode($sites, true):array();
            $action="modif";
            $titre=$nom." ".$prenom;

            // URL ICS
            if ($this->config('ICS-Export')) {
                $p = new personnel();
                $p->CSRFToken = $CSRFSession;
                $ics = $p->getICSURL($id);
            }
        } else {// pas d'id, donc ajout d'un agent
            $id=null;
            $nom=null;
            $prenom=null;
            $mail=null;
            $statut=null;
            $categorie=null;
            $check_hamac = 1;
            $check_ics = array(1,1,1);
            $service=null;
            $heuresHebdo=null;
            $heuresTravail=null;
            $arrivee=null;
            $depart=null;
            $login=null;
            $temps=null;
            $postes_attribues=array();
            $access=array();
            $matricule=null;
            $url_ics=null;
            $mailsResponsables=array();
            $informations=null;
            $recup=null;
            $sites=array();
            $titre="Ajout d'un agent";
            $action="ajout";
            if ($_SESSION['perso_actif'] and $_SESSION['perso_actif']!="Supprim&eacute;") {
                $actif=$_SESSION['perso_actif'];
            }// vérifie dans quel tableau on se trouve pour la valeur par défaut
        }

        $jours = array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
        $contrats = array("Titulaire","Contractuel");

        //		--------------		Début listes des activités		---------------------//
        // Toutes les activités
        $a = new \activites();
        $a->fetch();
        $activites = $a->elements;

        foreach ($activites as $elem) {
            $postes_completNoms[]=array($elem['nom'],$elem['id']);
            $postes_complet[]=$elem['id'];
        }

        // les activités non attribuées (disponibles)
        $postes_dispo=array();
        if ($postes_attribues) {
            $postes=join(",", $postes_attribues);	//	activités attribuées séparées par des virgules (valeur transmise à valid.php)
            if (is_array($postes_complet)) {
                foreach ($postes_complet as $elem) {
                    if (!in_array($elem, $postes_attribues)) {
                        $postes_dispo[]=$elem;
                    }
                }
            }
        } else {
            //activités attribuées séparées par des virgules (valeur transmise à valid.php)
            $postes = "";
            $postes_dispo=$postes_complet;
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
            $tmp=array();
            if (is_array($postes)) {
                foreach ($postes as $elem) {
                    if (is_array($tab_noms)) {
                        foreach ($tab_noms as $noms) {
                            if ($elem==$noms[1]) {
                                $tmp[]=array($elem,$noms[0]);
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
            'can_manage_agent'  => in_array(21, $droits) ? 1 : 0,
            'titre'             => $titre,
            'conges_enabled'    => $this->config('Conges-Enable'),
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
            'mailsResp_joined'  => join("; ", $mailsResponsables),
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
            or $this->config('ICS-Server3') or $this->config('ICS-Export')) {
            $this->templateParams(array( 'agendas_and_sync' => 1 ));
        }

        if (in_array(21, $droits)) {
            $h=array();
            for ($i=1;$i<40;$i++) {
                if ($this->config('Granularite') == 5) {
                    $h[]=array($i,$i."h00");
                    $h[]=array($i.".08",$i."h05");
                    $h[]=array($i.".17",$i."h10");
                    $h[]=array($i.".25",$i."h15");
                    $h[]=array($i.".33",$i."h20");
                    $h[]=array($i.".42",$i."h25");
                    $h[]=array($i.".5",$i."h30");
                    $h[]=array($i.".58",$i."h35");
                    $h[]=array($i.".67",$i."h40");
                    $h[]=array($i.".75",$i."h45");
                    $h[]=array($i.".83",$i."h50");
                    $h[]=array($i.".92",$i."h55");
                } elseif ($this->config('Granularite') == 15) {
                    $h[]=array($i,$i."h00");
                    $h[]=array($i.".25",$i."h15");
                    $h[]=array($i.".5",$i."h30");
                    $h[]=array($i.".75",$i."h45");
                } elseif ($this->config('Granularite') == 30) {
                    $h[]=array($i,$i."h00");
                    $h[]=array($i.".5",$i."h30");
                } else {
                    $h[]=array($i,$i."h00");
                }
            }
            $this->templateParams(array( 'times' => $h ));
        } else {
            $heuresHebdo_label = $heuresHebdo;
            if (!stripos($heuresHebdo, "%")) {
                $heuresHebdo_label .= " heures";
            }
            $this->templateParams(array(
                'heuresHebdo_label' => $heuresHebdo_label,
                'heuresTravail_label' => $heuresTravail . " heures",
            ));
        }

        // Multi-sites
        if ($this->config('Multisites-nombre') > 1) {
            $sites_select = array();
            for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
                if (in_array(21, $droits)) {
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
            if ($config['ICS-Code']) {
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
            for ($i = 1; $i <= $config['Multisites-nombre']; $i++) {
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

        if ($config['Conges-Enable']) {
            $c = new \conges();
            $c->perso_id = $id;
            $c->fetchCredit();
            $conges = $c->elements;

            $this->templateParams(array(
                'annuel_heures'     => $conges['annuelHeures'],
                'annuel_min'            => $conges['annuelCents'],
                'annuel_string'         => heure4($conges['annuel']),
                'credit_heures'         => $conges['creditHeures'],
                'credit_min'            => $conges['creditCents'],
                'credit_string'         => heure4($conges['credit']),
                'reliquat_heures'       => $conges['reliquatHeures'],
                'reliquat_min'          => $conges['reliquatCents'],
                'reliquat_string'       => heure4($conges['reliquat']),
                'anticipation_heures'    $conges['anticipationHeures'],
                'anticipation_min'      => $conges['anticipationCents'],
                'anticipation_string'   => heure4($conges['anticipation']),
                'lang_comp_time'        => $lang['comp_time'],
                'recup_heures'           => $conges['recupHeures'],
                'recup_min'             => $conges['recupCents'],
                'recup_string'          => isset($conges['recup_samedi']) ? heure4($conges['recup_samedi']) : '',
            ));
        }

        $this->templateParams(array(
            'edt_samedi'    => $this->config('EDTSamedi'),
            'current_tab'   => $currentTab,
            'nb_semaine'    => $this->config('nb_semaine'),
        ));

        return $this->output('agents/edit.html.twig');
    }

}