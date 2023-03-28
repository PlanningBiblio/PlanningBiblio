<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\PlanningBiblio\Helper\HolidayHelper;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


include_once(__DIR__ . '/../../public/conges/class.conges.php');

class CompTimeController extends BaseController
{
    /**
     * @Route("/comptime/add", name="comptime.add", methods={"GET"})
     */
    public function add(Request $request)
    {
        $dbprefix = $GLOBALS['dbprefix'];
        $perso_id = filter_input(INPUT_GET, 'perso_id', FILTER_SANITIZE_NUMBER_INT);

        if (!$perso_id) {
            $perso_id = $_SESSION['login_id'];
        }

        list($admin, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->forAgent($perso_id)
            ->getValidationLevelFor($_SESSION['login_id']);

        if (!$admin and !$adminN2) {
            $perso_id=$_SESSION['login_id'];
        }

        $c = new \conges();
        $balance = $c->calculCreditRecup($perso_id);

        $p = new \personnel();
        $p->fetchById($perso_id);
        $nom = $p->elements[0]['nom'];
        $prenom = $p->elements[0]['prenom'];
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
        $hours_per_day = '';
        if ($holiday_helper->showHoursToDays()) {
            $hours_per_day = $holiday_helper->hoursPerDay($perso_id);
            $balance_before_days = $holiday_helper->hoursToDays($balance[1], $perso_id, null, true);
            $balance2_before_days = $holiday_helper->hoursToDays($balance[4], $perso_id, null, true);
        }

        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getManagedFor($_SESSION['login_id']);

        $information = array();
        $date = date("Y-m-d");
        $db = new \db();
        $db->query("SELECT * FROM `{$dbprefix}conges_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
        if ($db->result) {
            foreach ($db->result as $elem) {
                $information[] = 'Du '. dateFr($elem['debut'])
                    . ' au ' . dateFr($elem['fin'])
                    . ' :<br/>' . str_replace("\n", '<br/>', $elem['texte'])
                    . "<br/><br/>\n";
            }
        }

        $this->templateParams(array(
            'anticipation'          => $anticipation,
            'balance_before'        => heure4($balance[1]),
            'balance_before_days'   => $balance_before_days,
            'balance_date'          => dateFr($balance[0]),
            'balance2_before'       => heure4($balance[4]),
            'balance2_before_days'  => $balance2_before_days,
            'credit'                => $credit,
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'hours_per_day'         => $hours_per_day,
            'information'           => $information,
            'loggedin_name'         => $_SESSION['login_nom'],
            'loggedin_firstname'    => $_SESSION['login_prenom'],
            'managed'               => $managed,
            'perso_id'              => $perso_id,
            'recuperation'          => $recuperation,
            'recuperation_prev'     => $balance[4],
            'reliquat'              => $reliquat,
        ));

        return $this->output('comptime/add.html.twig');
    }

    /**
     * @Route("/comptime", name="comptime.save", methods={"POST"})
     */
    public function save(Request $request, Session $session)
    {
        $CSRFToken = $request->get('CSRFToken');
        $perso_id = $request->get('perso_id');

        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $hre_debut = $request->get('hre_debut');
        $hre_fin = $request->get('hre_fin');

        if (!$fin) {
            $fin = $debut;
        }

        $debutSQL = dateSQL($debut);
        $finSQL = dateSQL($fin);
        $hre_debut = $hre_debut ? $hre_debut : '00:00:00';
        $hre_fin = $hre_fin ? $hre_fin : '23:59:59';
        $commentaires = htmlentities($request->get('commentaires'), ENT_QUOTES|ENT_IGNORE, "UTF-8", false);

        // Enregistrement du congés
        $data = $request->request->all();

        // FIXME : this works only when Conges-validation is disabled.
        // TODO : Handle the different levels of validation when the dropdown menu will be added to the template for config Conges-validation enabled
        // TODO : an other (and better) way to fix this is to use the holiday controller and holiday templates for adding comp-time. It's already done for comp-time editions.

        if ($request->get('valide')) {
            $data['conges-recup'] = 1;
            $data['conges-mode'] = $this->config('Conges-Mode');
            $data['perso_ids'] = array($data['perso_id']);
            $data['confirm'] = 'confirm';
            $data['debit'] = 'recuperation';
            $data['valide_init'] = 1;
            $data['valide'] = $_SESSION['login_id'];
            $data['valide_n1'] = $_SESSION['login_id'];
            $data['validation'] = date('Y-m-d H:i:s');
            $data['validation_n1'] = date('Y-m-d H:i:s');
        }

        $c = new \conges();
        $c->CSRFToken = $CSRFToken;
        $c->add($data);
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
        $message ="Nouvelle demande de récupération: <br/>$prenom $nom<br/>Début : $debut";
        if ($hre_debut != '00:00:00') {
            $message .= ' ' . heure3($hre_debut);
        }
        $message .= "<br/>Fin : $fin";
        if ($hre_fin != '23:59:59') {
            $message .= ' ' . heure3($hre_fin);
        }
        if ($commentaires) {
            $message .= "<br/><br/>Commentaire :<br/>$commentaires<br/>";
        }

        // ajout d'un lien permettant de rebondir sur la demande
        $url = $this->config('URL') . "/holiday/edit/$id";
        $message .= "<br/><br/>Lien vers la demande de récupération :<br/><a href='$url'>$url</a><br/><br/>";

        // Envoi du mail
        $m=new \CJMail();
        $m->subject="Nouvelle demande de récupération";
        $m->message=$message;
        $m->to=$destinataires;
        $m->send();

        if ($m->error_CJInfo) {
            $session->getFlashBag()->add('error', $m->error_CJInfo);
        }

        $session->getFlashBag()->add('notice', 'La demande de récupération a été enregistrée');

        return $this->redirectToRoute('holiday.index', array('recup' => 1));
    }
}
