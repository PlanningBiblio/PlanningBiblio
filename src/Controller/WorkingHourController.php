<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

require_once(__DIR__. '/../../public/planningHebdo/class.planningHebdo.php');
require_once(__DIR__. '/../../public/personnel/class.personnel.php');

class WorkingHourController extends BaseController
{

    private $imported = false;
    private $adminN1 = false;
    private $adminN2 = false;
    private $workinghours = array();

    /**
     * @Route("/ajax/workinghour-tables", name="ajax.workinghourtables", methods={"GET"})
     */
    public function tables(Request $request)
    {

        $nbSemaine = $request->get('weeks');
        $perso_id = $request->get('perso_id');
        $ph_id = $request->get('ph_id');

        if (!$nbSemaine || !$perso_id) {
            $response = new Response();
            $response->setContent('Wrong parameters');
            $response->setStatusCode(400);
            return $response;
        }

        $droits = $GLOBALS['droits'];
        $pause2_enabled = $this->config('PlanningHebdo-Pause2');
        $pauseLibre_enabled = $this->config('PlanningHebdo-PauseLibre');

        // Sécurité
        $this->adminN1 = in_array(1101, $droits);
        $this->adminN2 = in_array(1201, $droits);

        switch ($nbSemaine) {
            case 1:
                $cellule = array("Jour");
                break;
            case 2:
                $cellule = array("Semaine Impaire","Semaine Paire");
                break;
            default:
                $cellule = array();
                for ($i = 1; $i <= $nbSemaine; $i++) {
                    array_push($cellule, "Semaine $i");
                }
                break;
        }

        $temps = null;
        $breaktime = array();

        // Informations sur l'agents
        $p = new \personnel();
        $p->fetchById($perso_id);
        $sites = $p->elements[0]['sites'];
        $nbSites = $this->config('Multisites-nombre');
        $multisites = array();
        foreach ($sites as $site) {
            $multisites[$site] = $this->config("Multisites-site{$site}");
        }

        if ($ph_id != null) {
            $p = new \planningHebdo();
            $p->id = $ph_id;
            $p->fetch();
            $this->workinghours = $p->elements[0];
            $cle = $p->elements[0]['cle'];
            $this->imported = $cle ? true : false;
            $perso_id = $p->elements[0]['perso_id'];
            $temps = $p->elements[0]['temps'];
            $breaktime = $p->elements[0]['breaktime'];
        }

        $modifAutorisee = $this->can_edit();

        // Decimal breaktime to time (H:i).
        foreach ($breaktime as $index => $time) {
            $breaktime[$index] = $breaktime[$index]
                ? gmdate('H:i', floor($breaktime[$index] * 3600)) : '';
        }

        $fin = $this->config('Dimanche') ? array(7,14,21,28,35,42,49,56,63,70) : array(6,13,20,27,34,41,48,55,62,69);
        $debut = array(1,8,15,22,29,36,43,50,57,64);
        $jours = array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
        $GLOBALS['temps'] = $temps;

        $this->templateParams(
            array(
                "breaktime"      => $breaktime,
                "cellule"        => $cellule,
                "debut"          => $debut,
                "fin"            => $fin,
                "jours"          => $jours,
                "modifAutorisee" => $modifAutorisee,
                "multisites"     => $multisites,
                "nbSemaine"      => $nbSemaine,
                "nbSites"        => $nbSites,
                "pause2_enabled" => $pause2_enabled,
                "pauseLibre_enabled" => $pauseLibre_enabled,
                "sites"          => $sites,
                "temps"          => $temps
            )
        );
        return $this->output('/workinghour/tables.html.twig');

    }


    /**
     * @Route("/workinghour", name="workinghour.index", methods={"GET"})
     */
    public function index(Request $request, Session $session){
        // Initialisation des variables
        $debut = $request->get("debut");
        $fin = $request->get("fin");
        $reset = $request->get("reset");
        $droits = $GLOBALS['droits'];
        $lang = $GLOBALS['lang'];

        $debut = filter_var($debut, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $fin = filter_var($fin, FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
        $reset = filter_var($reset, FILTER_CALLBACK, array("options"=>"sanitize_on"));

        if (!$debut) {
            $debut = array_key_exists("planningHebdoDebut", $_SESSION['oups'])?$_SESSION['oups']['planningHebdoDebut']:null;
        }

        if (!$fin) {
            $fin = array_key_exists("planningHebdoFin", $_SESSION['oups'])?$_SESSION['oups']['planningHebdoFin']:null;
        }

        if ($reset) {
            $debut = null;
            $fin = null;
        }
        $_SESSION['oups']['planningHebdoDebut'] = $debut;
        $_SESSION['oups']['planningHebdoFin'] = $fin;
        $message = null;

        // Droits d'administration
        // Seront utilisés pour n'afficher que les agents gérés si l'option "PlanningHebdo-notifications-agent-par-agent" est cochée
        $adminN1 = in_array(1101, $droits);
        $adminN2 = in_array(1201, $droits);

        $notAdmin = !($adminN1 or $adminN2);
        $admin = ($adminN1 or $adminN2);

        // Droits de gestion des plannings de présence agent par agent
        if ($adminN1 and $this->config('PlanningHebdo-notifications-agent-par-agent')) {
            $db = new \db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));

            if (!$adminN2) {
                $perso_ids = array($_SESSION['login_id']);
                if ($db->result) {
                    foreach ($db->result as $elem) {
                        $perso_ids[] = $elem['perso_id'];
                    }
                }
            }
        }

        // Recherche des plannings
        $p = new \planningHebdo();
        $p->merge_exception = false;
        $p->debut=  dateFr($debut);
        $p->fin = dateFr($fin);
        if (!empty($perso_ids)) {
            $p->perso_ids = $perso_ids;
        }
        $p->fetch();

        $a = new \personnel();
        $a->supprime = array(0,1,2);
        $a->fetch();
        $agents = $a->elements;

        $tab = $p->elements;

        foreach ($tab as &$elem) {
            $actuel = $elem['actuel'] ? "Oui" : null;

            // Validation
            $validation_class = 'bold';
            $validation_date = dateFr($elem['saisie'], true);
            $validation = 'Demandé';

            // Validation niveau 1
            if ($elem['valide_n1'] > 0) {
                $validation_date = dateFr($elem['validation_n1'], true);
                $validation = $lang['work_hours_dropdown_accepted_pending'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide_n1'] != 99999) {
                    $validation .= ", ".nom($elem['valide_n1'], 'nom p', $agents);
                }
            } elseif ($elem['valide_n1'] < 0) {
                $validation_date = dateFr($elem['validation_n1'], true);
                $validation = $lang['work_hours_dropdown_refused_pending'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide_n1'] != 99999) {
                    $validation.=", ".nom(-$elem['valide_n1'], 'nom p', $agents);
                }
            }
            // Validation niveau 2
            if ($elem['valide'] > 0) {
                $validation_date = dateFr($elem['validation'], true);
                $validation = $lang['work_hours_dropdown_accepted'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide'] != 99999) {
                    $validation.=", ".nom($elem['valide'], 'nom p', $agents);
                }
            } elseif ($elem['valide'] < 0) {
                $validation_class = 'red';
                $validation_date = dateFr($elem['validation'], true);
                $validation = $lang['work_hours_dropdown_refused'];
                // 99999 : ID cron : donc pas de nom a afficher
                if ($elem['valide'] != 99999) {
                    $validation.=", ".nom(-$elem['valide'], 'nom p', $agents);
                }
            }

            $planningRemplace = $elem['remplace'] == 0 ? dateFr($elem['saisie'], true) : $planningRemplace;
            $commentaires = $elem['remplace']?"Remplace les heures <br/>du $planningRemplace" : null;
            $commentaires = $elem['exception'] ? 'Exception' : $commentaires;

            $elem['debut'] = dateFr($elem['debut']);
            $elem['fin'] = dateFr($elem['fin']);
            $elem['saisie'] = dateFr($elem['saisie'], true);
            $elem['validation'] = $validation;
            $elem['validation_date'] = $validation_date;
            $elem['commentaires'] = $commentaires;

            // Show first and surname
            $elem['firstname'] = '';
            $elem['surname'] = '';
            if (isset($agents[$elem['perso_id']])) {
                $elem['firstname'] = $agents[$elem['perso_id']]['prenom'];
                $elem['surname'] = $agents[$elem['perso_id']]['nom'];
            }
        }

        $this->templateParams(
            array(
                "debut" => $debut,
                "fin"   => $fin,
                "tab"   => $tab
            )
        );
        return $this->output('/workinghour/index.html.twig');
    }

    /**
     * @Route("/workinghour/add", name="workinghour.add", methods={"GET"})
     */
    public function add(Request $request, Session $session){
        // Initialisation des variables
        $copy = $request->get('copy');
        $retour = $request->get('retour');
        $droits = $GLOBALS['droits'];
        $lang = $GLOBALS['lang'];
        $pause2_enabled = $this->config('PlanningHebdo-Pause2');
        $pauseLibre_enabled = $this->config('PlanningHebdo-PauseLibre');
        $validation = "";
        $id = null;
        $tab = array();
        $action = "ajout";

        $is_new = 1;
        // Sécurité
        $adminN1 = in_array(1101, $droits);
        $adminN2 = in_array(1201, $droits);
        $notAdmin = !($adminN1 or $adminN2);
        $admin = ($adminN1 or $adminN2);
        $cle = null;
        $modifAutorisee = true;
        $debut1 = null;
        $fin1 = null;
        $debut1Fr = null;
        $fin1Fr = null;
        $perso_id = $_SESSION['login_id'];
        $valide_n2 = 0;
        $remplace = null;
        $sites = array();
        $nbSemaine = $this->config('nb_semaine');
        $nbSites = $this->config('Multisites-nombre');
        $multisites = array();
        for ($i = 1; $i < $nbSites+1; $i++) {
            $sites[] = $i;
            $multisites[$i] = $this->config("Multisites-site{$i}");
        }
        $valide_n1 = 0;
        $valide_n2 = 0;
        if ($this->config('PlanningHebdo-notifications-agent-par-agent') and !$adminN2) {
        // Sélection des agents gérés (table responsables) et de l'agent logué
            $perso_ids = array($_SESSION['login_id']);
            $db = new \db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $perso_ids[] = $elem['perso_id'];
                }
            }
            $perso_ids = implode(',', $perso_ids);
            $db = new \db();
            $db->select2('personnel', null, array('supprime'=>0, 'id' => "IN$perso_ids"), 'order by nom,prenom');
        } else {
            $db = new \db();
            $db->select2('personnel', null, array('supprime'=>0), 'order by nom,prenom');
        }
        $nomAgent = nom($perso_id, "prenom nom");

        if ($this->config('PlanningHebdo-notifications-agent-par-agent') and !$adminN2) {
            // Sélection des agents gérés (table responsables) et de l'agent logué
            $perso_ids = array($_SESSION['login_id']);
            $db = new \db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $perso_ids[] = $elem['perso_id'];
                }
            }
            $perso_ids = implode(',', $perso_ids);
            $db = new \db();
            $db->select2('personnel', null, array('supprime'=>0, 'id' => "IN$perso_ids"), 'order by nom,prenom');
            $tab = $db->result;
        } else {
            $db = new \db();
            $db->select2('personnel', null, array('supprime'=>0), 'order by nom,prenom');
            $tab = $db->result;
        }
        if (!($adminN1 or $adminN2) and $valide_n2 > 0) {
            $action = "copie";
        }
        if (!$cle) {
            if ($admin) {
                $selected1 = isset($valide_n1) && $valide_n1 > 0 ? true : false;
                $selected2 = isset($valide_n1) && $valide_n1 < 0 ? true : false;
                $selected3 = isset($valide_n2) && $valide_n2 > 0 ? true : false;
                $selected4 = isset($valide_n2) && $valide_n2 < 0 ? true : false;
                // Si pas admin, affiche le niveau en validation en texte simple
            } else {
                $validation = "Demandé";
                if ($valide_n2 > 0) {
                    $validation = $lang['work_hours_dropdown_accepted'];
                } elseif ($valide_n2 < 0) {
                    $validation = $lang['work_hours_dropdown_refused'];
                } elseif ($valide_n1 > 0) {
                    $validation = $lang['work_hours_dropdown_accepted_pending'];
                } elseif ($valide_n1 < 0) {
                    $validation = $lang['work_hours_dropdown_refused_pending'];
                }
            }
        }

        $this->templateParams(
            array(
                "action"             => $action,
                "admin"              => $admin,
                "adminN1"            => $adminN1,
                "adminN2"            => $adminN2,
                "cle"                => $cle,
                "copy"               => $copy,
                "debut1"             => $debut1,
                "debut1Fr"           => $debut1Fr,
                "exception_id"       => null,
                "exception_back"     => null,
                "fin1"               => $fin1,
                "fin1Fr"             => $fin1Fr,
                "id"                 => $id,
                "is_exception"       => null,
                "is_new"             => $is_new,
                "lang"               => $lang,
                "login_id"           => $_SESSION['login_id'],
                "modifAutorisee"     => $modifAutorisee,
                "multisites"         => $multisites,
                "nbSites"            => $nbSites,
                "nbSemaine"          => $nbSemaine,
                "nomAgent"           => $nomAgent,
                "notAdmin"           => $notAdmin,
                "pause2_enabled"     => $pause2_enabled,
                "pauseLibre_enabled" => $pauseLibre_enabled,
                "perso_id"           => $perso_id,
                "remplace"           => null,
                "retour"             => $retour,
                "request_exception"  => null,
                "selected1"          => null,
                "selected2"          => null,
                "selected3"          => null,
                "selected4"          => null,
                "sites"              => $sites,
                "tab"                => $tab,
                "valide_n1"          => $valide_n1,
                "valide_n2"          => $valide_n2,
                "validation"         => $validation
            )
        );
        return $this->output('/workinghour/edit.html.twig');
    }

    /**
     * @Route("/workinghour/{id}", name="workinghour.edit", methods={"GET"})
     */
    public function edit(Request $request, Session $session){
        // Initialisation des variables
        $copy = $request->get('copy');
        $request_exception = $request->get('exception');
        $id = $request->get('id');
        $retour = $request->get('retour');
        $is_exception = 0;
        $exception_id = '';
        $droits = $GLOBALS['droits'];
        $lang = $GLOBALS['lang'];
        $pause2_enabled = $this->config('PlanningHebdo-Pause2');
        $nbSemaine = $this->config('nb_semaine');
        $nbSites = $this->config('Multisites-nombre');
        $validation = "";
        $sites = array();
        $multisites = array();
        for ($i = 1; $i < $nbSites+1; $i++) {
            $sites[] = $i;
            $multisites[$i] = $this->config("Multisites-site{$i}");
        }
        $exception_back = '/myaccount';
        if ($retour != '/myaccount') {
            $exception_back = $retour;
            $retour = "$retour";
        } else {
           $retour = "$exception_back";
        }

        if ($copy) {
            $id = $copy;
        }

    
        if ($request_exception) {
            $id = $request_exception;
        }

        $is_new = 0;
        // Sécurité
        $this->adminN1 = in_array(1101, $droits);
        $this->adminN2 = in_array(1201, $droits);
        $admin = ($this->adminN1 or $this->adminN2);
        $p = new \planningHebdo();
        $p->id = $id;
        $p->fetch();
        $this->workinghours = $p->elements[0];
        $debut1 = $p->elements[0]['debut'];
        $fin1 = $p->elements[0]['fin'];
        $debut1Fr = dateFr($debut1);
        $fin1Fr = dateFr($fin1);
        $perso_id = $p->elements[0]['perso_id'];
        $temps = $p->elements[0]['temps'];
        $breaktime = $p->elements[0]['breaktime'];
        $thisNbSemaine = $p->elements[0]['nb_semaine'];

        if (!$admin && $perso_id != $_SESSION['login_id']) {
            return $this->redirectToRoute('access-denied');
        }

        if ($p->elements[0]['exception']) {
            $is_exception = 1;
            $exception_id = $p->elements[0]['exception'];
        }

        if ($copy or $request_exception) {
            $valide_n1 = 0;
            $valide_n2 = 0;
        } else {
            $valide_n1 = $p->elements[0]['valide_n1'] ?? 0;
            $valide_n2 = $p->elements[0]['valide'] ?? 0;
        }
        $remplace = $p->elements[0]['remplace'];
        $cle = $p->elements[0]['cle'];
        $this->imported = $cle ? true : false;
        // Informations sur l'agents
        $p = new \personnel();
        $p->fetchById($perso_id);
        $sites = $p->elements[0]['sites'];
        // Droits de gestion des plannings de présence agent par agent
        if ($this->adminN1 and $this->config('PlanningHebdo-notifications-agent-par-agent')) {
            $db = new \db();
            $db->select2('responsables', 'perso_id', array('perso_id' => $perso_id, 'responsable' => $_SESSION['login_id']));
            $this->adminN1 = $db->result ? true : false;
        }
        // Modif autorisée si n'est pas validé ou si validé avec des périodes non définies (BSB).
        // Dans le 2eme cas copie des heures de présence avec modification des dates
        $action = "modif";
        $modifAutorisee = $this->can_edit();

        if (!($this->adminN1 or $this->adminN2) and $valide_n2 > 0) {
            $action = "copie";
        }
        if ($copy or $request_exception) {
            $action = "ajout";
        }

        $nomAgent = nom($perso_id, "prenom nom");
        if ($request_exception) {
            $is_exception = 1;
            $exception_id = $id;
        }

        if ($this->config('PlanningHebdo-notifications-agent-par-agent') and !$this->adminN2 and $copy) {
            // Sélection des agents gérés (table responsables) et de l'agent logué
            $perso_ids = array($_SESSION['login_id']);
            $db = new \db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $perso_ids[] = $elem['perso_id'];
                }
            }
            $perso_ids = implode(',', $perso_ids);
            $db = new \db();
            $db->select2('personnel', null, array('supprime'=>0, 'id' => "IN$perso_ids"), 'order by nom,prenom');
            $tab = $db->result;
        } else {
            $db = new \db();
            $db->select2('personnel', null, array('supprime'=>0), 'order by nom,prenom');
            $tab = $db->result;
        }

        if (!$cle) {
            if ($modifAutorisee) {
                $selected1 = isset($valide_n1) && $valide_n1 > 0 ? true : false;
                $selected2 = isset($valide_n1) && $valide_n1 < 0 ? true : false;
                $selected3 = isset($valide_n2) && $valide_n2 > 0 ? true : false;
                $selected4 = isset($valide_n2) && $valide_n2 < 0 ? true : false;
                // Si pas admin, affiche le niveau en validation en texte simple
            } else {
                $selected1 = false;
                $selected2 = false;
                $selected3 = false;
                $selected4 = false;

                $validation = "Demandé";
                if ($valide_n2 > 0) {
                    $validation = $lang['work_hours_dropdown_accepted'];
                } elseif ($valide_n2 < 0) {
                    $validation = $lang['work_hours_dropdown_refused'];
                } elseif ($valide_n1 > 0) {
                    $validation = $lang['work_hours_dropdown_accepted_pending'];
                } elseif ($valide_n1 < 0) {
                    $validation = $lang['work_hours_dropdown_refused_pending'];
                }
            }
        }
        $this->templateParams(
            array(
                "action"             => $action,
                "admin"              => ($this->adminN1 or $this->adminN2),
                "adminN1"            => $this->adminN1,
                "adminN2"            => $this->adminN2,
                "cle"                => $cle,
                "copy"               => $copy,
                "debut1Fr"           => $debut1Fr,
                "exception_id"       => $exception_id,
                "exception_back"     => $exception_back,
                "fin1Fr"             => $fin1Fr,
                "id"                 => $id,
                "is_exception"       => $is_exception,
                "is_new"             => $is_new,
                "lang"               => $lang,
                "login_id"           => $_SESSION['login_id'],
                "modifAutorisee"     => $modifAutorisee,
                "multisites"         => $multisites,
                "nbSites"            => $nbSites,
                "nbSemaine"          => $nbSemaine,
                "nomAgent"           => $nomAgent,
                "perso_id"           => $perso_id,
                "remplace"           => $remplace,
                "retour"             => $retour,
                "request_exception"  => $request_exception,
                "tab"                => $tab,
                "temps"              => $temps,
                "thisNbSemaine"      => $thisNbSemaine,
                "selected1"          => $selected1,
                "selected2"          => $selected2,
                "selected3"          => $selected3,
                "selected4"          => $selected4,
                "sites"              => $sites,
                "valide_n1"          => $valide_n1,
                "valide_n2"          => $valide_n2,
                "validation"         => $validation
            )
        );
        return $this->output('/workinghour/edit.html.twig');
    }

    /**
     * @Route("/workinghour", name="workinghour.save", methods={"POST"})
     */
    public function save(Request $request, Session $session){
        $post = $request->request->all();
        $msg = null;
        $msgType = null;

        foreach ($post['breaktime'] as $index => $time) {
          $post['breaktime'][$index] = $this->time_to_decimal($time);
        }

        switch ($post["action"]) {
            case "ajout":
                $p = new \planningHebdo();
                $p->add($post);
                if ($p->error) {
                    $msg = "Une erreur est survenue lors de l'enregistrement du planning.";
                    if ($post['id']) {
                        $msg = "Une erreur est survenue lors de la copie du planning.";
                    }
                    $msgType = "error";
                } else {
                    $msg = "Le planning a été ajouté avec succès.";
                    if ($post['id']) {
                        $msg = "Le planning a été copié avec succès.";
                    }
                    $msgType = "success";
                }
                break;
            case "modif":
                $p = new \planningHebdo();
                $p->update($post);
                if ($p->error) {
                    $msg = "Une erreur est survenue lors de la modification du planning.";
                    $msgType = "error";
                } else {
                    $msg = "Le planning a été modifié avec succès.";
                    $msgType = "success";
                }
                break;
            case "copie":
                $p = new \planningHebdo();
                $p->copy($post);
                if ($p->error) {
                    $msg = "Une erreur est survenue lors de la modification du planning.";
                    $msgType = "error";
                } else {
                    $msg = "Le planning a été modifié avec succès.";
                    $msgType = "success";
                }
                break;
        }

        if($post['retour'] == "/myaccount") {
            return $this->redirectToRoute("account.index", array("msg"=>$msg, "msgType" => $msgType));
        } else {
            return $this->redirectToRoute('workinghour.index', array("msg" => $msg, "msgType" => $msgType));
        }
    }

    /**
     * @Route("/workinghour", name="workinghour.delete", methods={"DELETE"})
     */
    public function delete(Request $request, Session $session){
        $CSRFToken = $request->get('CSRFToken');
        $id = $request->get("id");

        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->delete("planning_hebdo", "id=$id");

        $db = new \db();
        $db->CSRFToken = $CSRFToken;
        $db->update('planning_hebdo', array('remplace'=>'0'), array('remplace'=>$id));

        return $this->json('ok');
    }

    // FIXME put this in a helper or
    // a service container.
    private function time_to_decimal($time)
    {
        if (!$time) {
            return 0;
        }

        $hm = explode(":", $time);
        return ($hm[0] + ($hm[1] / 60));
    }

    private function can_edit()
    {
        // Working hours imported from external
        // sources cannot be edited.
        if ($this->imported) {
            return false;
        }

        $admin = ($this->adminN1 or $this->adminN2);
        if (!$admin and !$this->config('PlanningHebdo-Agents')) {
            return false;
        }

        $valide_n2 = $this->workinghours['valide'] ?? 0;
        if ($valide_n2 && !$this->adminN2) {
            return false;
        }

        return true;
    }
}
