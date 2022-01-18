<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Helper\HolidayHelper;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


include_once(__DIR__ . '/../../public/conges/class.conges.php');
include_once(__DIR__ . '/../../public/personnel/class.personnel.php');

class CompTimeController extends BaseController
{
    use \App\Controller\Traits\EntityValidationStatuses;
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
        $lang = $GLOBALS['lang'];

        list($admin, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getValidationLevelFor($_SESSION['login_id']);

        if (($admin or $adminN2) and $perso_id === null) {
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
        $c->admin = ($admin or $adminN2);
        $c->debut = $debut;
        $c->fin = $fin;
        if ($perso_id != 0) {
            $c->perso_id = $perso_id;
        }
        $c->getRecup();
        $recup = $c->elements;

        // Search agents
        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getManagedFor($_SESSION['login_id']);

        $perso_ids = array_map(function($a) { return $a->id(); }, $managed);

        // School year
        $annees = array();
        for ($d = date("Y") + 2; $d > date("Y") - 11; $d--) {
            $annees[]= array($d, $d . '-' . ($d + 1));
        }

        $this->templateParams(array(
            'years'     => $annees,
            'year_from' => $annee,
            'year_to'   => $annee + 1,
            'admin'     => ($admin or $adminN2),
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
        foreach ($managed as $index => $m) {
            $categories[$m->id()] = $m->categorie();
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
            'managed'                   => $managed,
            'categories'                => json_encode($categories, JSON_HEX_APOS),
            'label'                     => ($this->config('Recup-DeuxSamedis')) ? "Date (1<sup>er</sup> samedi)" : "Date",
            'saturday'                  => "Date (2<sup>ème</sup> samedi) (optionel)",
        ));

        return $this->output('comp_time/index.html.twig');
    }

    /**
     * @Route("/comp-time/{id}", name="comp-time.edit", methods={"GET"})
     */
    public function edit(Request $request)
    {

        $id = $request->get('id');

        $c = new \conges();
        $c->recupId = $id;
        $c->getRecup();
        $recup = $c->elements[0];
        $perso_id = $recup['perso_id'];

        // Droits d'administration niveau 1 et niveau 2
        list($adminN1, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->forAgent($perso_id)
            ->getValidationLevelFor($_SESSION['login_id']);

        // Prevent non manager to access other agents request.
        if (!$adminN1 and !$adminN2 and $perso_id != $_SESSION['login_id']) {
            return $this->output('access-denied.html.twig');
        }

        $this->setStatusesParams(array($perso_id), 'comptime', $id);


        // Initialisation des variables (suite)
        $agent = nom($recup['perso_id'], "prenom nom");
        $recup['saisie'] = dateFr($recup['saisie'], true);
        $recup['saisie_par_nom'] = nom($recup['saisie_par']);
        $recup['time'] = gmdate('H:i', floor($recup['heures'] * 3600));
        $recup['editable'] = $recup['valide'] <= 0 ? 1 : 0;
        $recup['save'] = ((($adminN1 or $adminN2) and $recup['valide']<=0)
            or $recup['valide'] == 0) ? 1 : 0;

        $this->templateParams(array(
            'agent'     => $agent,
            'comptime'  => $recup,
            'CSRFToken' => $GLOBALS['CSRFSession'],
            'id'        => $id,
        ));

        return $this->output('comp_time/edit.html.twig');
    }

    /**
     * @Route("/comp-time", name="comp-time.save", methods={"POST"})
     */
    public function save(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $id = $request->get('id');
        $commentaires = trim($request->get('commentaires'));
        $heures = $request->get('heures');
        $refus = trim($request->get('refus'));
        $validation = $request->get('validation');
        $lang = $GLOBALS['lang'];

        list($hours, $minutes) = explode(':', $heures);
        $heures = intVal($hours) + intVal($minutes) / 60;

        $result = array(
            'type' => 'notice',
            'message' => 'Vos modifications ont été enregistrées'
        );

        // Retrieving compensatory time.
        $c = new \conges();
        $c->recupId = $id;
        $c->getRecup();
        $recup = $c->elements[0];
        $perso_id = $recup['perso_id'];

        // Administration right level 1 and 2
        list($adminN1, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->forAgent($perso_id)
            ->getValidationLevelFor($_SESSION['login_id']);


        // Update hours.
        $update = array(
            'heures' => $heures,
            'commentaires' => $commentaires,
            'modif' => $_SESSION['login_id'],
            'modification' => date('Y-m-d H:i:s')
        );

        // FIXME this should be checked better.
        if ($validation !== null and ($adminN1 or $adminN2)) {
            // Validation level 1
            if ($validation == 2 or $validation == -2) {
                $update['valide_n1'] = $validation / 2 * $_SESSION['login_id'] ;
                $update['validation_n1'] = date("Y-m-d H:i:s");
            }

            // Validation level 2
            if ($validation == 1 or $validation == -1) {
                $update['valide'] = $validation * $_SESSION['login_id'] ;
                $update['validation'] = date("Y-m-d H:i:s");
            }

            $update['refus'] = $refus;
        }

        if (isset($update)) {
            // Update table 'recuperations'
            $db = new \db();
            $db->CSRFToken = $CSRFToken;
            $db->update('recuperations', $update, array('id' => $id));
            if ($db->error) {
                $result['type'] = 'error';
                $result['message'] = 'Une erreur est survenue lors de la validation de vos modifications.';
            }

            // Update comp-time credit if it is validated
            if (isset($update['valide']) and $update['valide'] > 0) {
                $db = new \db();
                $db->select('personnel', 'comp_time', "id='$perso_id'");
                $solde_prec = $db->result[0]['comp_time'];
                $recup_update = $solde_prec+$update['heures'];

                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update('personnel', array('comp_time' => $recup_update), array('id' => $perso_id));
                $db = new \db();
                $db->CSRFToken = $CSRFToken;
                $db->update('recuperations', array('solde_prec' => $solde_prec, 'solde_actuel' => $recup_update), array('id' => $id));
            }

            // Notifiy agent and managers.
            $agent = $this->entityManager->find(Agent::class, $perso_id);
            $nom = $agent->nom();
            $prenom = $agent->prenom();

            if (isset($update['valide']) and $update['valide'] > 0) {
                $sujet = $lang['comp_time_subject_accepted'];
                $notifications = 4;
            } elseif (isset($update['valide']) and $update['valide'] < 0) {
                $sujet = $lang['comp_time_subject_refused'];
                $notifications = 4;
            } elseif (isset($update['valide_n1']) and $update['valide_n1'] > 0) {
                $sujet = $lang['comp_time_subject_accepted_pending'];
                $notifications = 3;
            } elseif (isset($update['valide_n1']) and $update['valide_n1'] < 0) {
                $sujet = $lang['comp_time_subject_refused_pending'];
                $notifications = 3;
            } else {
                $sujet="Demande de récupération modifiée";
                $notifications = 2;
            }

            $message = $sujet;
            $message .= "<br/><br/>\n";
            $message .= "Pour l'agent : $prenom $nom";
            $message .= "<br/>\n";
            $message .= "Date : ".dateFr($recup['date']);
            $message .= "<br/>\n";
            $message .= "Nombre d'heures : ".heure4($update['heures']);
            if ($update['commentaires']) {
                $message.="<br/><br/><u>Commentaires</u> :<br/>".str_replace("\n", "<br/>", $update['commentaires']);
            }
            if ($update['refus']) {
                $message.="<br/><br/><u>Motif du refus</u> :<br/>".str_replace("\n", "<br/>", $update['refus']);
            }

            // Choix des destinataires en fonction de la configuration
            if ($config['Absences-notifications-agent-par-agent']) {
                $a = new \absences();
                $a->getRecipients2(null, $perso_id, $notifications, 600, $recup['date'], $recup['date']);
                $destinataires = $a->recipients;
            } else {
                $c->getResponsables($recup['date'], $recup['date'], $perso_id);
                $responsables = $c->responsables;

                $a = new \absences();
                $a->getRecipients($notifications, $responsables, $agent, 'Recup');
                $destinataires = $a->recipients;
            }

            // Envoi du mail
            $m = new \CJMail();
            $m->subject = $sujet;
            $m->message = $message;
            $m->to = $destinataires;
            $m->send();

            // Si erreur d'envoi de mail, affichage de l'erreur
            if ($m->error_CJInfo) {
                $result['type'] = 'error';
                $result['message'] = $m->error_CJInfo;
            }
        }

        $session->getFlashBag()->add($result['type'], $result['message']);

        return $this->redirectToRoute('comp-time.index');
    }
}