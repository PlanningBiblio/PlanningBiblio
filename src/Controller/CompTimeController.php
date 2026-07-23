<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Agent;
use App\Planno\Helper\HolidayHelper;
use App\Planno\Helper\HourHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

include_once(__DIR__ . '/../../legacy/Class/class.conges.php');

class CompTimeController extends BaseController
{
    #[Route(path: '/comptime/add', name: 'comptime.add', methods: ['GET'])]
    public function add(Request $request)
    {
        if ($this->config('Conges-Recuperations') == 0  || $this->config('Conges-Enable') == 0 ) {
            return $this->redirectToRoute('access-denied');
        }

        $session = $request->getSession();

        $dbprefix = $GLOBALS['dbprefix'];
        $perso_id = filter_input(INPUT_GET, 'perso_id', FILTER_SANITIZE_NUMBER_INT);

        if (!$perso_id) {
            $perso_id = $session->get('loginId');
        }

        list($admin, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->forAgent($perso_id)
            ->getValidationLevelFor($session->get('loginId'));

        if (!$admin and !$adminN2) {
            $perso_id = $session->get('loginId');
        }

        $c = new \conges();
        $balance = $c->calculCreditRecup($perso_id);

        $p = new \personnel();
        $p->fetchById($perso_id);
        $nom = $p->elements[0]['nom'];
        $prenom = $p->elements[0]['prenom'];
        $credit = number_format($p->elements[0]['conges_credit'], 2, '.', ' ');
        $reliquat = number_format($p->elements[0]['conges_reliquat'], 2, '.', ' ');
        $anticipation = number_format($p->elements[0]['conges_anticipation'], 2, '.', ' ');
        $credit2 = heure4($credit);
        $reliquat2 = heure4($reliquat);
        $anticipation2 = heure4($anticipation);
        $recuperation = number_format((float) $balance[1], 2, '.', ' ');
        $recuperation2=heure4($recuperation);

        $balance_before_days = null;
        $balance2_before_days = null;

        $holiday_helper = new HolidayHelper();
        $hoursPerDay = 0;
        if ($holiday_helper->showHoursToDays()) {
            $hoursPerDay = $holiday_helper->hoursPerDay($perso_id);
            $hoursPerDayInHoursMinutes = HourHelper::decimalToHoursMinutes($hoursPerDay)['as_string'];
            $balance_before_days = $holiday_helper->hoursToDays($balance[1], $perso_id, null, true);
            $balance2_before_days = $holiday_helper->hoursToDays($balance[4], $perso_id, null, true);
        }

        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getManagedFor($session->get('loginId'));

        $date = date("Y-m-d");
        $db = new \db();
        $db->query("SELECT * FROM `{$dbprefix}conges_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
        $holiday_info = array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $elem['start'] = dateFr($elem['debut']);
                $elem['end'] = dateFr($elem['fin']);
                $holiday_info[] = $elem;
            }
        }

        $this->templateParams(array(
            'is_holiday'            => false,
            'request_type'          => 'recover',
            'id'                    => null,
            'allday'                => false,
            'halfday'               => false,
            'hre_debut'             => null,
            'hre_fin'               => null,
            'debut'                 => null,
            'fin'                   => null,
            'debit'                 => null,
            'valide'                => true,
            'delete_button'         => null,
            'commentaires'          => '',
            'anticipation'          => $anticipation,
            'balance_before'        => heure4($balance[1], true),
            'balance_before_days'   => $balance_before_days,
            'balance_date'          => dateFr($balance[0]),
            'balance2_before'       => heure4($balance[4], true),
            'balance2_before_days'  => $balance2_before_days,
            'credit'                => $credit,
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'hours_per_day'         => $hoursPerDay,
            'hours_per_day_in_hhmm' => $hoursPerDayInHoursMinutes,
            'holiday_info'          => $holiday_info,
            'agent_name'            => $_SESSION['login_nom'] . ' ' . $_SESSION['login_prenom'],
            'loggedin_name'         => $_SESSION['login_nom'],
            'loggedin_firstname'    => $_SESSION['login_prenom'],
            'managed'               => $managed,
            'perso_id'              => $perso_id,
            'recuperation'          => $recuperation,
            'recuperation_prev'     => $balance[4],
            'reliquat'              => $reliquat,
            'show_allday'           => true,
            'title'                 => 'Requesting compensation for overtime',
            'action_path'           => 'comptime',
            'selected_agent_id'     => $perso_id,
            'save_button'           => true,
        ));

        return $this->output('holiday/edit.html.twig');
    }

    #[Route(path: '/comptime', name: 'comptime.save', methods: ['POST'])]
    public function save(Request $request, Session $session): RedirectResponse
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $session = $request->getSession();

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

        $validationStatus = $request->request->getInt('valide');
        
        $holidayHelper = new HolidayHelper([
            'start' => $debutSQL,
            'hour_start' => $hre_debut,
            'end' => $finSQL,
            'hour_end' => $hre_fin,
            'perso_id' => $perso_id,
            'is_recover' => 1
        ]);

        $result = $holidayHelper->getCountedHours();
        $recover = ($result['hours'] + ($result['minutes'] / 100));

        $c = new \conges();
        $credit = $c->calculCreditRecup($perso_id, $debut);
        $credit_after_debit = ($credit[1] - $recover);

        if ($credit_after_debit < 0 and $validationStatus == 1) {
            $this->addFlash('error', 'La demande de récupération n\'a pas été enregistrée car le crédit de récupération ne peut pas être négatif.');
            return $this->redirectToRoute('holiday.index', ['recup' => 1]);
        }

        // Enregistrement du congés
        $data = $request->request->all();

        if ($validationStatus) {
            $data['conges-recup'] = 1;
            $data['conges-mode'] = $this->config('Conges-Mode');
            $data['perso_ids'] = array($data['perso_id']);
            $data['confirm'] = 'confirm';
            $data['debit'] = 'recuperation';

            if (!$this->config['Conges-validation']) {
                    $data['valide_n1'] = $session->get('loginId');
                    $data['validation_n1'] = date('Y-m-d H:i:s');
                    $data['valide'] = $session->get('loginId');
                    $data['validation'] = date('Y-m-d H:i:s');
                    $data['valide_init'] = 1;
            } else {
                $data['valide_init'] = $validationStatus;

                switch ($validationStatus) {
                    case -2 :
                        $data['valide_n1'] = -1 * (int) $session->get('loginId');
                        $data['validation_n1'] = date('Y-m-d H:i:s');
                        $data['valide'] = 0;
                        $data['validation'] = null;
                    break;
                    case 2 :
                        $data['valide_n1'] = $session->get('loginId');
                        $data['validation_n1'] = date('Y-m-d H:i:s');
                        $data['valide'] = 0;
                        $data['validation'] = null;
                    break;
                    case -1 :
                        $data['valide'] = -1 * (int) $session->get('loginId');
                        $data['validation'] = date('Y-m-d H:i:s');
                    break;
                    case 1 :
                        $data['valide'] = $session->get('loginId');
                        $data['validation'] = date('Y-m-d H:i:s');
                    break;
                }
            }
        }

        $c = new \conges();
        $c->CSRFToken = $CSRFToken;
        $c->add($data);
        $id = $c->id;

        // Récupération des adresses e-mails de l'agent et des responsables pour l'envoi des alertes
        $agent = $this->entityManager->find(Agent::class, $perso_id);
        $nom = $agent->getLastname();
        $prenom = $agent->getFirstname();

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
        if ($commentaires !== '' && $commentaires !== '0') {
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
