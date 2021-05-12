<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Helper\HolidayHelper;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;


include_once(__DIR__ . '/../../public/conges/class.conges.php');
include_once(__DIR__ . '/../../public/personnel/class.personnel.php');

class CompTimeController extends BaseController
{
    /**
     * @Route("/comp-time", name="comp-time.index", methods={"GET"})
     */
    public function index(Request $request)
    {

        $holiday_helper = new HolidayHelper();

        $annee = $request->get('annee');
        $reset = $request->get('reset');
        $perso_id = $request->get('perso_id');

        $this->droits = $GLOBALS['droits'];
        $this->setAdminPermissions();
        $lang = $GLOBALS['lang'];

        if ($this->admin and $perso_id === null) {
            $perso_id = isset($_SESSION['oups']['recup_perso_id'])
                ? $_SESSION['oups']['recup_perso_id']
                : $_SESSION['login_id'];
        } elseif ($perso_id === null) {
            $perso_id = $_SESSION['login_id'];
        }

        if (!$annee) {
            $annee = isset($_SESSION['oups']['recup_annee'])
                ? $_SESSION['oups']['recup_annee']
                : (date("m")<9?date("Y")-1:date("Y"));
        }

        if ($reset) {
            $annee = date("m") < 9 ? date("Y") - 1 : date("Y");
            $perso_id = $_SESSION['login_id'];
        }

        $_SESSION['oups']['recup_annee'] = $annee;
        $_SESSION['oups']['recup_perso_id'] = $perso_id;

        $debut = $annee . '-09-01';
        $fin = ($annee + 1) . '-08-31';
        $message = null;

        // Search for existing comp-times
        $c = new \conges();
        $c->admin = $this->admin;
        $c->debut = $debut;
        $c->fin = $fin;
        if ($perso_id != 0) {
            $c->perso_id = $perso_id;
        }
        $c->getRecup();
        $recup = $c->elements;

        // Search agents
        $agents = array();
        if ($this->admin) {
            $p = new \personnel();
            $p->responsablesParAgent = true;
            $p->fetch();
            $agents = $p->elements;

            // If Absences-notifications-agent-par-agent is enables,
            // filter unmanaged agents.
            if ($this->config('Absences-notifications-agent-par-agent') and !$this->adminN2) {
                $tmp = array();

                foreach ($agents as $elem) {
                    foreach ($elem['responsables'] as $resp) {
                        if ($resp['responsable'] == $_SESSION['login_id']) {
                            $tmp[$elem['id']] = $elem;
                            break;
                        }
                    }
                }

                $agents = $tmp;
            }
        }

        if (empty($agents[$_SESSION['login_id']])) {
            $p = new \personnel();
            $p->fetchById($_SESSION['login_id']);
            $agents[$_SESSION['login_id']] = $p->elements[0];
        }

        usort($agents, 'cmp_nom_prenom');

        // List ids of agents to keep:
        $perso_ids = array_column($agents, 'id');

        // School year
        $annees = array();
        for ($d = date("Y") + 2; $d > date("Y") - 11; $d--) {
            $annees[]= array($d, $d . '-' . ($d + 1));
        }

        $this->templateParams(array(
            'years'     => $annees,
            'year_from' => $annee,
            'year_to'   => $annee + 1,
            'admin'     => $this->admin,
        ));

        $comptimes = array();
        foreach ($recup as $elem) {

          // Filtre les agents non-gérés (notamment avec l'option Absences-notifications-agent-par-agent)
            if (!in_array($elem['perso_id'], $perso_ids)) {
                continue;
            }

            $validation="Demandé";
            $validation_date = dateFr($elem['saisie'], true);
            $validationStyle="font-weight:bold;";
            if ($elem['saisie_par'] and $elem['saisie_par']!=$elem['perso_id']) {
                $validation.=" par ".nom($elem['saisie_par']);
            }
            $credits=null;

            if ($elem['valide']>0) {
                $validation = $lang['leave_table_accepted'] ." par ". nom($elem['valide']);
                $validation_date = dateFr($elem['validation'], true);
                $validationStyle=null;
                if ($elem['solde_prec']!=null and $elem['solde_actuel']!=null) {
                    $credits=heure4($elem['solde_prec'])." → ".heure4($elem['solde_actuel']);
                    if ($holiday_helper->showHoursToDays()) {
                        $credits .= "<br />" . $holiday_helper->hoursToDays($elem['solde_prec'], $elem['perso_id']) . "j &rarr; " . $holiday_helper->hoursToDays($elem['solde_actuel'], $elem['perso_id']) . "j";
                    }
                }
            } elseif ($elem['valide']<0) {
                $validation = $lang['leave_table_refused'] ." par ". nom(-$elem['valide']);
                $validation_date = dateFr($elem['validation'], true);
                $validationStyle="color:red;font-weight:bold;";
            } elseif ($elem['valide_n1'] > 0) {
                $validation = $lang['leave_table_accepted_pending'] .", ". nom($elem['valide_n1']);
                $validation_date = dateFr($elem['validation_n1'], true);
                $validationStyle="font-weight:bold;";
            } elseif ($elem['valide_n1'] < 0) {
                $validation = $lang['leave_table_refused_pending'] .", ". nom(-$elem['valide_n1']);
                $validation_date = dateFr($elem['validation_n1'], true);
                $validationStyle="font-weight:bold;";
            }

            $date2 = ($elem['date2'] and $elem['date2']!="0000-00-00") ? " & ".dateFr($elem['date2']) : null;

            $comptime = array(
                'id'                => $elem['id'],
                'date'              => dateFr($elem['date']),
                'date2'             => $date2,
                'name'              => nom($elem['perso_id']),
                'hours'             => heure4($elem['heures']),
                'validation_style'  => $validationStyle,
                'validation'        => $validation,
                'validation_date'   => $validation_date,
                'credits'           => $credits,
                'commentaires'      => html_entity_decode($elem['commentaires'], ENT_QUOTES|ENT_HTML5),
            );

            $comptime['hourstodays'] = null;
            if ($this->config('Conges-Recuperations') == 0 && $holiday_helper->showHoursToDays()) {
                $comptime['hourstodays'] = $holiday_helper->hoursToDays($elem['heures'], $elem['perso_id']);
            }

            $comptimes[]= $comptime;
        }

        $this->templateParams(array(
            'comptimes' => $comptimes,
        ));

        $categories = array();
        foreach ($agents as $index => $elem) {
            $categories[$elem['id']] = $elem['categorie'];
            $agents[$index]['name'] = nom($elem['id']);
        }

        $this->templateParams(array(
            'recup_delaidefaut'         => $this->config('Recup-DelaiDefaut'),
            'recup_delaititulaire1'     => $this->config('Recup-DelaiTitulaire1'),
            'recup_delaititulaire2'     => $this->config('Recup-DelaiTitulaire2'),
            'recup_delaicontractuel1'   => $this->config('Recup-DelaiContractuel1'),
            'recup_delaicontractuel2'   => $this->config('Recup-DelaiContractuel2'),
            'recup_deuxsamedis'         => $this->config('Recup-DeuxSamedis'),
            'recup_samediseulement'     => $this->config('Recup-SamediSeulement') ? 'true' : 'false',
            'recup_uneparjour'          => $this->config('Recup-Uneparjour') ? 'true' : 'false',
            'perso_id'                  => $perso_id,
            'perso_name'                => nom($perso_id, 'prenom nom'),
            'agents'                    => $agents,
            'categories'                => json_encode($categories, JSON_HEX_APOS),
            'label'                     => ($this->config('Recup-DeuxSamedis')) ? "Date (1<sup>er</sup> samedi)" : "Date",
            'saturday'                  => "Date (2<sup>ème</sup> samedi) (optionel)",
        ));

        return $this->output('comp_time/index.html.twig');
    }

    /**
     * @Route("/comp-time/new", name="comp-time.new", methods={"GET"})
     */
    public function add(Request $request)
    {

        $CSRFSession = $GLOBALS['CSRFSession'];
        $perso_id = $_SESSION['login_id'];
        $droits = $GLOBALS['droits'];
        $dbprefix = $GLOBALS['dbprefix'];

        $admin = false;
        $adminN2 = false;
        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (in_array((400+$i), $droits) or in_array((600+$i), $droits)) {
                $admin = true;
            }
            if (in_array((600+$i), $droits)) {
                $adminN2 = true;
            }
        }

        $c = new \conges();
        $balance = $c->calculCreditRecup($perso_id);
        $balance_date = dateFr($balance[0]);
        $balance_before = heure4($balance[1]);
        $balance_after = heure4($balance[4]);

        $p=new \personnel();
        $p->fetchById($perso_id);
        $nom=$p->elements[0]['nom'];
        $prenom=$p->elements[0]['prenom'];
        $credit = number_format((float) $p->elements[0]['conges_credit'], 2, '.', ' ');
        $reliquat = number_format((float) $p->elements[0]['conges_reliquat'], 2, '.', ' ');
        $anticipation = number_format((float) $p->elements[0]['conges_anticipation'], 2, '.', ' ');
        $credit2 = heure4($credit);
        $reliquat2 = heure4($reliquat);
        $anticipation2 = heure4($anticipation);
        $recuperation = number_format((float) $balance[1], 2, '.', ' ');
        $recuperation2=heure4($recuperation);

        $balance_before_days = null;
        $balance2_before_days = null;

        $holiday_helper = new HolidayHelper();
        if ($holiday_helper->showHoursToDays()) {
            $hours_per_day = $holiday_helper->hoursPerDay($perso_id);
            $balance_before_days = $holiday_helper->hoursToDays($balance[1], $perso_id, null, true);
            $balance2_before_days = $holiday_helper->hoursToDays($balance[4], $perso_id, null, true);
        }

        $db_perso = null;
        error_log("admin : $admin");
        error_log("adminN2 : $adminN2");
        if ($admin) {
            $db_perso = $this->get_agents($adminN2);            
        }

        $date=date("Y-m-d");
        $db=new \db();
        $db->query("SELECT * FROM `{$dbprefix}conges_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
        $holiday_info = array();
        if ($db->result) {
            echo "<b>Informations sur les congés :</b><br/><br/>\n";
            foreach ($db->result as $elem) {
                $elem['start'] = dateFr($elem['debut']);
                $elem['end'] = dateFr($elem['fin']);
                $elem['texte'] = str_replace("\n", "<br/>", $elem['texte']);
                $holiday_info[] = $elem;
            }
        }



        $this->templateParams(array(
            'CSRFToken'       => $CSRFSession,
            'perso_id'       => $perso_id,
            'admin' => $admin,
            'agent_name' => $_SESSION['login_nom'] . ' ' . $_SESSION['login_prenom'],
            'anticipation'       => $anticipation,
            'anticipation2'       => $anticipation2,
            'balance_date' => $balance_date,
            'balance_before' => $balance_before,
            'balance_before_days'       => $balance_before_days,
            'balance2_before_days'       => $balance_before_days,
            'balance_after' => $balance_after,
            'credit' => $credit,
            'credit2'       => $credit2,
            'db_perso' => $db_perso,
            'debut' => null,
            'fin' => null,
            'holiday_info' => $holiday_info,
            'reliquat'       => $reliquat,
            'reliquat2'       => $reliquat2,
            'recuperation'       => $recuperation2,
            'recuperation_prev' => $balance[4],
        ));

        return $this->output('comp_time/add.html.twig');
    }

    /**
     * Get managed agents
     */
    private function get_agents($adminN2)
    {
        // Si l'option "Absences-notifications-agent-par-agent" est cochée, filtrer les agents à afficher dans le menu déroulant pour permettre la sélection des seuls agents gérés
        if ($this->config('Absences-notifications-agent-par-agent') and !$adminN2) {
            $perso_ids = array($_SESSION['login_id']);

            $db = new \db();
            $db->select2('responsables', 'perso_id', array('responsable' => $_SESSION['login_id']));
            if ($db->result) {
                foreach ($db->result as $elem) {
                    $perso_ids[] = $elem['perso_id'];
                }
            }

            $perso_ids = implode(',', $perso_ids);

            $db_perso=new \db();
            $db_perso->select2('personnel', null, array('supprime' => '0', 'id' => "IN$perso_ids"), 'ORDER BY nom,prenom');
        }

        // Si l'option "Absences-notifications-agent-par-agent" n'est pas cochée, on affiche tous les agents dans le menu déroulant
        else {
            $db_perso=new \db();
            $db_perso->select2('personnel', null, array('supprime' => '0'), 'ORDER BY nom,prenom');
        }
        $agents = array();
        foreach($db_perso->result as $elem) {
            $agent = array();
            $agent['nom'] = $elem['nom'];
            $agent['prenom'] = $elem['prenom'];
            $agent['id'] = $elem['id'];
            array_push($agents, $agent);
        }
        return $agents;
    }


    /**
     * @Route("/comp-time", name="comp-time.save", methods={"POST"})
     */
    public function add_confirm(Request $request, Session $session) {
        $result = $this->save($request);

        if (!empty($result['msg'])) {
            $type = $result['msgType'] == 'success' ? 'notice' : 'error';
            $session->getFlashBag()->add($type, $result['msg']);
        }

        if (!empty($result['msg2'])) {
            $type = $result['msg2Type'] == 'success' ? 'notice' : 'error';
            $session->getFlashBag()->add($type, $result['msg2']);
        }

        return $this->redirectToRoute('comp-time.index');
    }


    public function save(Request $request)
    {
        // Initialisation des variables
        #$debutSQL = dateSQL($debut);
        #$finSQL=dateSQL($fin);
        #$hre_debut=$_GET['hre_debut']?$_GET['hre_debut']:"00:00:00";
        #$hre_fin=$_GET['hre_fin']?$_GET['hre_fin']:"23:59:59";
        #$commentaires=htmlentities($_GET['commentaires'], ENT_QUOTES|ENT_IGNORE, "UTF-8", false);
error_log("save");

        $CSRFToken = $request->get('CSRFToken');
        $perso_id = $request->get('perso_id');
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $debutSQL = dateSQL($request->get('debut'));
        $finSQL = dateSQL($request->get('fin'));
        $hre_debut = $request->get('hre_debut') ? $request->get('hre_debut') :"00:00:00";
        $hre_fin = $request->get('hre_fin') ? $request->get('hre_fin') : "23:59:59";
        $commentaires = htmlentities($request->get('commentaires'), ENT_QUOTES|ENT_IGNORE, "UTF-8", false);
        $valide = $request->get('valide');
        $request->request->set('valide_init', $valide);


        // Enregistrement du congés
        $c = new \conges();
        $c->CSRFToken = $CSRFToken;
        $c->add($request->request->all());
        $id = $c->id;

        // Récupération des adresses e-mails de l'agent et des responsables pour l'envoi des alertes
        $agent = $this->entityManager->find(Agent::class, $perso_id);
        $nom = $agent->nom();
        $prenom = $agent->prenom();

        // Choix des destinataires en fonction de la configuration
        if ($this->config('Absences-notifications-agent-par-agent')) {
            $a = new \absences();
            $a->getRecipients2(null, $perso_id, 1);
            $destinataires = $a->recipients;
        } else {
            $c = new \conges();
            $c->getResponsables($debutSQL, $finSQL, $perso_id);
            $responsables = $c->responsables;

            $a = new \absences();
            $a->getRecipients('-A1', $responsables, $agent);
            $destinataires = $a->recipients;
        }

        // Message qui sera envoyé par email
        $message = "Nouveau congés: <br/>$prenom $nom<br/>Début : $debut";
        if ($hre_debut != "00:00:00") {
            $message .= " " . heure3($hre_debut);
        }
        $message .= "<br/>Fin : $fin";
        if ($hre_fin != "23:59:59") {
            $message .=" " . heure3($hre_fin);
        }
        if ($commentaires) {
            $message .= "<br/><br/>Commentaire :<br/>$commentaires<br/>";
        }

        // ajout d'un lien permettant de rebondir sur la demande
        $url = $this->config('URL') . "/holiday/edit/$id";
        $message.="<br/><br/>Lien vers la demande de cong&eacute; :<br/><a href='$url'>$url</a><br/><br/>";

        // Envoi du mail
        $m = new \CJMail();
        $m->subject = "Nouveau congés";
        $m->message = $message;
        $m->to = $destinataires;
        $m->send();

        // Si erreur d'envoi de mail, affichage de l'erreur
        $msg2 = null;
        $msg2Type = null;
        if ($m->error) {
            $msg2 = urlencode($m->error_CJInfo);
            $msg2Type = "error";
        }

        $msg = "La demande de congé a été enregistrée";

        return array(
            'msg'       => $msg,
            'msgType'   => 'success',
            'msg2'      => $msg2,
            'msg2Type'  => $msg2Type
        );
    }


    private function setAdminPermissions()
    {
        // If can validate level 1: admin = true.
        // If can validate level 2: adminN2 = true.
        $this->admin = false;
        $this->adminN2 = false;
        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (in_array((400+$i), $this->droits)) {
                $this->admin = true;
            }
            if (in_array((600+$i), $this->droits)) {
                $this->admin = true;
                $this->adminN2 = true;
                break;
            }
        }
    }
}
