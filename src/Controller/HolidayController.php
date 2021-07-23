<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\Agent;
use App\PlanningBiblio\Helper\HolidayHelper;
use App\PlanningBiblio\Helper\WeekPlanningHelper;
use App\Model\AbsenceReason;

use App\PlanningBiblio\Helper\HourHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/personnel/class.personnel.php');
require_once(__DIR__ . '/../../public/planningHebdo/class.planningHebdo.php');

class HolidayController extends BaseController
{
    /**
     * @Route("/holiday/index", name="holiday.index", methods={"GET"})
     */
    public function index(Request $request)
    {
        $annee = $request->get('annee');
        $congesAffiches = $request->get('congesAffiches');
        $perso_id = $request->get('perso_id');
        $reset = $request->get('reset');
        $supprimes = $request->get('supprimes');
        $voir_recup = $request->get('recup');

        $lang = $GLOBALS['lang'];

        // Gestion des droits d'administration
        // NOTE : Ici, pas de différenciation entre les droits niveau 1 et niveau 2
        // NOTE : Les agents ayant les droits niveau 1 ou niveau 2 sont admin ($admin, droits 40x et 60x)
        // Initialisation des variables
        list($admin, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getValidationLevelFor($_SESSION['login_id']);

        if (($admin or $adminN2) and $perso_id==null) {
            $perso_id=isset($_SESSION['oups']['conges_perso_id'])?$_SESSION['oups']['conges_perso_id']:$_SESSION['login_id'];
        } elseif ($perso_id==null) {
            $perso_id=$_SESSION['login_id'];
        }

        $agents_supprimes=isset($_SESSION['oups']['conges_agents_supprimes'])?$_SESSION['oups']['conges_agents_supprimes']:false;
        $agents_supprimes=($annee and $supprimes)?true:$agents_supprimes;
        $agents_supprimes=($annee and !$supprimes)?false:$agents_supprimes;

        if (!$annee) {
            $annee=isset($_SESSION['oups']['conges_annee'])?$_SESSION['oups']['conges_annee']:(date("m")<9?date("Y")-1:date("Y"));
        }

        if (!$congesAffiches) {
            $congesAffiches=isset($_SESSION['oups']['congesAffiches'])?$_SESSION['oups']['congesAffiches']:"aVenir";
        }

        if ($reset) {
            $annee=date("m")<9?date("Y")-1:date("Y");
            $perso_id=$_SESSION['login_id'];
            $agents_supprimes=false;
        }
        $_SESSION['oups']['conges_annee']=$annee;
        $_SESSION['oups']['congesAffiches']=$congesAffiches;
        $_SESSION['oups']['conges_perso_id']=$perso_id;
        $_SESSION['oups']['conges_agents_supprimes']=$agents_supprimes;


        $debut=$annee."-09-01";
        $fin=($annee+1)."-08-31";

        if ($congesAffiches=="aVenir") {
            $debut=date("Y-m-d");
        }

        $c = new \conges();
        $c->debut = $debut;
        $c->fin = $fin . " 23:59:59";
        if ($perso_id != 0) {
            $c->perso_id = $perso_id;
        }
        if ($agents_supprimes) {
            $c->agents_supprimes = array(0,1);
        }

        $addLink = '/holiday/new';
        // Si la gestion des congés et des récupérations est dissociée, on ne recherche que les infos voulues
        if ($this->config('Conges-Recuperations') == '1') {
            if ($voir_recup) {
                $c->debit='recuperation';
                $addLink = '/index.php?page=conges/recup_pose.php';
            } else {
                $c->debit='credit';
            }
        }
        $this->templateParams(array('addlink' => $addLink));
        $c->fetch();

        // Recherche des agents pour le menu
        $managed = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getManagedFor($_SESSION['login_id'], $agents_supprimes);
        $perso_ids = array_map(function($a) { return $a->id(); }, $managed);

        // Recherche des agents pour la fonction nom()
        $p = new \personnel();
        $p->supprime=array(0,1,2);
        $p->fetch();
        $agents=$p->elements;

        // Années universitaires
        $annees=array();
        for ($d=date("Y")+2;$d>date("Y")-11;$d--) {
            $annees[]=array($d,$d."-".($d+1));
        }

        $holiday_helper = new HolidayHelper();

        $templateParams = array(
            'admin'                 => $admin || $adminN2,
            'perso_id'              => $perso_id,
            'managed'               => $managed,
            'deleted_agents'        => $agents_supprimes ? 1 : 0,
            'conges_recuperation'   => $this->config('Conges-Recuperations'),
            'conges_mode'           => $this->config('Conges-Mode'),
            'show_recovery'         => $voir_recup,
            'agent_name'            => nom($perso_id, "prenom nom", $agents),
            'from_year'             => $annee,
            'to_year'               => $annee + 1,
            'years'                 => $annees,
            'forthcoming'           => $congesAffiches == "aVenir" ? 1 : 0,
            'balance'               => $this->config('Conges-Recuperations') == '0' or !$voir_recup ? 1 : 0,
            'recovery'              => $this->config('Conges-Recuperations') == '0' or $voir_recup ? 1 : 0,
            'perso_ids'             => $perso_ids,
            'show_hours_to_days'    => $holiday_helper->showHoursToDays() ? 1 : 0,
        );

        $this->templateParams($templateParams);

        $holidays = array();
        foreach ($c->elements as $elem) {
            // Filter non handled agent.
            // See among others option Absences-notifications-agent-par-agent.
            if (!in_array($elem['perso_id'], $perso_ids)) {
                continue;
            }

            // If Conges-Recuperations is 1, also search
            // credits updates.
            if ($this->config('Conges-Recuperations') == '1') {
                if ($elem['debit'] == null) {
                    if ($voir_recup and $elem['recup_actuel'] == $elem['recup_prec']) {
                        continue;
                    }
                    if (!$voir_recup
                        and $elem['solde_actuel'] == $elem['solde_prec']
                        and $elem['reliquat_actuel'] == $elem['reliquat_prec']
                        and $elem['anticipation_actuel'] == $elem['anticipation_prec']) {
                        continue;
                    }
                }
            }

            $elem['start'] = str_replace("00h00", "", dateFr($elem['debut'], true));
            $elem['end'] = str_replace("23h59", "", dateFr($elem['fin'], true));

            $force = null;
            if ($voir_recup) {
                $force = 'heures';
            }
            $elem['hours'] = $holiday_helper->HumanReadableDuration($elem['heures'], $force);
            if ($holiday_helper->showHoursToDays()) {
                $elem['days'] = $holiday_helper->hoursToDays($elem['heures'], $elem['perso_id']);
            }
            $elem['status'] = "Demandé, ".dateFr($elem['saisie'], true);
            $elem['validationDate'] = dateFr($elem['saisie'], true);

            foreach (array('solde_prec', 'solde_actuel',
                'reliquat_prec', 'reliquat_actuel',
                'anticipation_prec', 'anticipation_actuel') as $key) {
                if ($holiday_helper->showHoursToDays()) {
                    $elem[$key . '_days'] = $holiday_helper->hoursToDays($elem[$key], $elem['perso_id']);
                }
                $elem[$key] = $holiday_helper->HumanReadableDuration($elem[$key]);
            }

            foreach (array('recup_prec', 'recup_actuel') as $key) {
                if ($holiday_helper->showHoursToDays()) {
                    $elem[$key . '_days'] = $holiday_helper->hoursToDays($elem[$key], $elem['perso_id']);
                }
                $elem[$key] = $holiday_helper->HumanReadableDuration($elem[$key], 'heures');
            }

            $elem['reliquat'] = '';
            $elem['recuperations'] = '';
            $elem['anticipation'] = '';

            if ($elem['saisie_par'] and $elem['perso_id']!=$elem['saisie_par']) {
                $elem['status'] .= " par ".nom($elem['saisie_par'], 'nom p', $agents);
            }

            if ($elem['valide'] < 0) {
                $elem['status'] = "Refusé, ".nom(-$elem['valide'], 'nom p', $agents);
                $elem['validationDate'] = dateFr($elem['validation'], true);
            } elseif ($elem['valide'] or $elem['information']) {
                $elem['status'] = "Validé, ".nom($elem['valide'], 'nom p', $agents);
                $elem['validationDate'] = dateFr($elem['validation'], true);
            } elseif ($elem['valide_n1']) {
                $elem['status'] = $elem['valide_n1'] > 0 ? $lang['leave_table_accepted_pending'] : $lang['leave_table_refused_pending'];
                $elem['validationDate'] = dateFr($elem['validation_n1'], true);
                $elem['validationStyle'] = "font-weight:bold;";
            }

            if ($elem['information']) {
                $elem['nom'] = $elem['information']<999999999?nom($elem['information'], 'nom p', $agents).", ":null;	// >999999999 = cron
                $elem['status'] = "Mise à jour des crédits, " . $elem['nom'];
                $elem['validationDate'] = dateFr($elem['info_date'], true);
                $elem['validationStyle'] = '';
            } elseif ($elem['supprime']) {
                $elem['status'] = "Supprimé, ".nom($elem['supprime'], 'nom p', $agents);
                $elem['validationDate'] = dateFr($elem['suppr_date'], true);
                $elem['validationStyle'] = '';
            }

            // This holiday is not a regularization,
            // but there is one related to.
            if (!empty($elem['regul_id'])) {
                $r = new \conges();
                $r->id = $elem['regul_id'];
                $r->fetch();
                $data = $r->elements[0];

                $regul = $data['recup_actuel'] - $data['recup_prec'];
                $elem['regul'] = $regul;
                $elem['hr_regul'] = heure4(abs($regul));
            }

            // This is a regularization.
            if (!empty($elem['origin_id'])) {
                $origin = new \conges();
                $origin->id = $elem['origin_id'];
                $origin->fetch();
                $data = $origin->elements[0];

                $elem['origin_start'] = str_replace("00h00", "", dateFr($data['debut'], true));
                $elem['origin_end'] = str_replace("23h59", "", dateFr($data['fin'], true));
            }

            $elem['nom'] = $admin ? nom($elem['perso_id'], 'nom p', $agents) : '';

            $holidays[] = $elem;
        }

        $this->templateParams(array('holidays' => $holidays));

        return $this->output('conges/index.html.twig');
    }

    /**
     * @Route("/ajax/holidays-hours-to-days", name="ajax.holidays-hours-to-days", methods={"GET"})
     */
    public function hoursToDays(Request $request, Session $session)
    {
        $hours_to_convert = $request->get('hours_to_convert');
        $hours_per_year = $request->get('hours_per_year');
        $holiday_helper = new HolidayHelper();
        $results = array();
        $results['hoursToDays'] = $holiday_helper->hoursToDays($hours_to_convert, null, $hours_per_year);
        $results['hoursPerDay'] = $holiday_helper->hoursPerDay(null, $hours_per_year);
        return $this->json($results);
    }

    /**
     * @Route("/holiday/edit", name="holiday.update", methods={"POST"})
     * @Route("/holiday/edit/{id}", name="holiday.edit", methods={"GET"})
     */
    public function edit(Request $request, Session $session)
    {
        $id = $request->get('id');
        $confirm = $request->get('confirm');
        $dbprefix = $GLOBALS['dbprefix'];
        $this->droits = $GLOBALS['droits'];

        // Elements du congé demandé
        $c = new \conges();
        $c->id = $id;
        $c->fetch();
        $data = $c->elements[0];
        $perso_id = $data['perso_id'];

        // FIXME: move into a dedicated model's method when possible: $holiday->isEditable().
        if ($c->elements[0]['information'] != 0 or $c->elements[0]['supprime'] != 0) {
            return $this->output('access-denied.html.twig');
        }

        // Calcul des crédits de récupération disponibles lors de l'ouverture du formulaire (date du jour)
        $c = new \conges();
        $balance = $c->calculCreditRecup($perso_id);

        if ( $confirm ) {
            $result = $this->update($request);
            $msg = $result['msg'];
            $msg2 = $result['msg2'];
            $msg2Type = $result['msg2Type'];
            $recover = 0;

            if ($result['back_to'] == 'recover') {
                $recover = 1;
            }


            if (!empty($msg)) {
                $session->getFlashBag()->add('notice', $msg);
            }

            if (!empty($msg2)) {
                $type = $msg2Type == 'success' ? 'notice' : 'error';
                $session->getFlashBag()->add($type, $msg2);
            }

            return $this->redirectToRoute("holiday.index", array('recup' => $recover));
        }

        list($adminN1, $adminN2) = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->forAgent($perso_id)
            ->getValidationLevelFor($_SESSION['login_id']);

        if (!$adminN1 and !$adminN2 and $perso_id != $_SESSION['login_id']) {
            return $this->output('access-denied.html.twig');
        }

        if ($this->config('Conges-Validation-N2') && $data['valide_n1'] == 0) {
            $adminN2 = false;
        }

        $this->templateParams(array('CSRFToken' => $GLOBALS['CSRFSession']));
        $valide=$data['valide']>0?true:false;
        $displayRefus = ($data['valide_n1'] < 0 and ($adminN1 or $adminN2)) ? null : "display:none;";
        $displayRefus = $data['valide'] > 0 ? "display:none;" : $displayRefus;
        $debut=dateFr(substr($data['debut'], 0, 10));
        $fin=dateFr(substr($data['fin'], 0, 10));
        $hre_debut=substr($data['debut'], -8);
        $hre_fin=substr($data['fin'], -8);
        $jours=number_format(($data['heures']/7), 2, ".", " ");
        $tmp=explode(".", $data['heures']);
        $heures=$tmp[0];
        $minutes=$tmp[1];

        // Crédits
        $p = new \personnel();
        $p->fetchById($perso_id);

        $agent_name = $p->elements[0]['nom'] . ' ' . $p->elements[0]['prenom'];

        $conges_anticipation = $p->elements[0]['conges_anticipation'];
        $conges_credit = $p->elements[0]['conges_credit'];
        $conges_reliquat = $p->elements[0]['conges_reliquat'];

        $credit = number_format((float) $conges_credit, 2, '.', ' ');
        $reliquat = number_format((float) $conges_reliquat, 2, '.', ' ');
        $anticipation = number_format((float) $conges_anticipation, 2, '.', ' ');
        $recuperation = number_format((float) $balance[1], 2, '.', ' ');
        $recuperation2=heure4($recuperation, true);
        if ($balance[4] < 0) {
            $balance[4] = 0;
        }
        $request_type = 'holiday';
        if ($this->config('Conges-Recuperations') == 1 and $data['debit']=="recuperation") {
            $request_type = 'recover';
        }

        $show_allday = 0;
        if ($this->config('Conges-Mode') == 'heures'
            and (!$this->config('Conges-Recuperations')
                or $this->config('Conges-Heures')
                or $data['debit']=="recuperation")) {
            $show_allday = 1;
        }

        $displayHeures=null;
        if ($hre_debut=="00:00:00" and $hre_fin=="23:59:59") {
            $displayHeures="style='display:none;'";
        }

        $holiday_helper = new HolidayHelper();

        $anticipation_jours = null;
        $credit_jours = null;
        $reliquat_jours = null;

        $hoursPerDay = null;
        if ($holiday_helper->showHoursToDays()) {
            $hoursPerDay = $holiday_helper->hoursPerDay($perso_id);

            $anticipation_jours = $holiday_helper->hoursToDays($conges_anticipation, $perso_id, null, true);
            $credit_jours = $holiday_helper->hoursToDays($conges_credit, $perso_id, null, true);
            $reliquat_jours = $holiday_helper->hoursToDays($conges_reliquat, $perso_id, null, true);
        }

        $templateParams = array(
            'id'                    => $id,
            'perso_id'              => $perso_id,
            'selected_agent_id'     => $perso_id,
            'agent_name'            => $agent_name,
            'halfday'               => $data['halfday'],
            'start_halfday'         => $data['start_halfday'],
            'end_halfday'           => $data['end_halfday'],
            'hours_per_day'         => $hoursPerDay,
            'reliquat'              => $reliquat,
            'reliquat2'             => $holiday_helper->HumanReadableDuration($reliquat),
            'reliquat_jours'        => $reliquat_jours,
            'recuperation'          => $recuperation,
            'recuperation_prev'     => $balance[4],
            'credit'                => $credit,
            'credit2'               => $holiday_helper->HumanReadableDuration($credit),
            'credit_jours'          => $credit_jours,
            'anticipation'          => $anticipation,
            'anticipation2'         => $holiday_helper->HumanReadableDuration($anticipation),
            'anticipation_jours'    => $anticipation_jours,
            'conges_recuperations'  => $this->config('Conges-Recuperations'),
            'debut'                 => $debut,
            'fin'                   => $fin,
            'hre_debut'             => $hre_debut,
            'hre_fin'               => $hre_fin,
            'conges_mode'           => $this->config('Conges-Mode'),
            'conges_heures'         => $this->config('Conges-Heures'),
            'conges_demi_journee'   => $this->config('Conges-demi-journees'),
            'conges_tous'           => $this->config('Conges-tous'),
            'request_type'          => $request_type,
            'adminN1'               => $adminN1,
            'adminN2'               => $adminN2,
            'show_allday'           => $show_allday,
            'debit'                 => $data['debit'],
            'valide'                => $data['valide'],
            'valide_n1'             => $data['valide_n1'],
            'balance_date'          => dateFr($balance[0]),
            'balance_before'        => heure4($balance[1]),
            'balance2_before'       => heure4($balance[4], true),
            'recup4'                => heure4($balance[1], true),
            'commentaires'          => html_entity_decode($data['commentaires'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'),
            'refus'                 => html_entity_decode($data['refus'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'),
            'saisie'                => dateFr($data['saisie'], true),
            'displayRefus'          => $displayRefus,
        );

        $this->templateParams($templateParams);

        $saisie_par = '';
        if ($data['saisie_par'] and $data['saisie_par'] != $data['perso_id']) {
            $saisie_par = nom($data['saisie_par']);
        }
        $this->templateParams(array('saisie_par' => $saisie_par));

        // Si droit de validation niveau 2 sans avoir le droit de validation niveau 1,
        // on affiche l'état de validation niveau 1
        if ($adminN2 and !$adminN1) {
            if ($data['valide_n1'] == 0) {
                $validation_n1 = "Congé demandé";
            } elseif ($data['valide_n1'] > 0) {
                $validation_n1 = "Congé accepté au niveau 1";
            } else {
                $validation_n1 = "Congé refusé au niveau 1";
            }
            $this->templateParams(array('validation_n1' => $validation_n1));
        }
        $lang = $GLOBALS['lang'];
        $this->templateParams(array(
            'accepted_pending_str' => $lang['leave_dropdown_accepted_pending'],
            'refused_pending_str' => $lang['leave_dropdown_refused_pending']
        ));

        $save_button = 0;
        if ((!$valide and ($adminN1 or $adminN2)) or ($data['valide']==0 and $data['valide_n1']==0)) {
            $save_button = 1;
        }
        $this->templateParams(array('save_button' => $save_button));
        $delete_button = 0;
        if (($adminN1 and $data['valide']==0) or $adminN2) {
            $delete_button = 1;
        }
        $this->templateParams(array('delete_button' => $delete_button));
        return $this->output('conges/edit.html.twig');
    }

    /**
     * @Route("/holiday", name="holiday.save", methods={"POST"})
     */
    public function add_confirm(Request $request, Session $session)
    {
        $result = $this->save($request);

        if (!empty($result['msg'])) {
            $type = $result['msgType'] == 'success' ? 'notice' : 'error';
            $session->getFlashBag()->add($type, $result['msg']);
        }

        if (!empty($result['msg2'])) {
            $type = $result['msg2Type'] == 'success' ? 'notice' : 'error';
            $session->getFlashBag()->add($type, $result['msg2']);
        }

        return $this->redirectToRoute('holiday.index');
    }

    /**
     * @Route("/holiday/new", name="holiday.new", methods={"GET", "POST"})
     * @Route("/holiday/new/{perso_id}", name="holiday.new.new", methods={"GET", "POST"})
     */
    public function add(Request $request)
    {
        // Initialisation des variables
        $perso_id = $request->get('perso_id');
        $dbprefix = $GLOBALS['dbprefix'];

        $agentRepository = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday');

        if ($perso_id) {
            $agentRepository->forAgent($perso_id);
        }
        list($admin, $adminN2) = $agentRepository->getValidationLevelFor($_SESSION['login_id']);

        if (!$perso_id) {
            $perso_id = $_SESSION['login_id'];
        }

        $sites_select = $this->entityManager
            ->getRepository(Agent::class)
            ->setModule('holiday')
            ->getManagedSitesFor($_SESSION['login_id']);

        $agents_multiples = (($admin || $adminN2) && $this->config('Conges-Recuperations') == 1);

        // Si pas de droits de gestion des congés, on force $perso_id = son propre ID
        if (!$admin && !$adminN2) {
            $perso_id=$_SESSION['login_id'];
        }

        // Calcul des crédits de récupération disponibles lors de l'ouverture du formulaire (date du jour)
        $c = new \conges();
        $balance = $c->calculCreditRecup($perso_id);

        // Initialisation des variables
        $holiday_helper = new HolidayHelper();
        $perso_id=$perso_id?$perso_id:$_SESSION['login_id'];
        $p=new \personnel();
        $p->fetchById($perso_id);
        $conges_anticipation = $p->elements[0]['conges_anticipation'];
        $conges_credit = $p->elements[0]['conges_credit'];
        $conges_reliquat = $p->elements[0]['conges_reliquat'];

        $credit = number_format((float) $conges_credit, 2, '.', ' ');
        $reliquat = number_format((float) $conges_reliquat, 2, '.', ' ');
        $anticipation = number_format((float) $conges_anticipation, 2, '.', ' ');
        $recuperation = number_format((float) $balance[1], 2, '.', ' ');

        $anticipation_jours = null;
        $credit_jours = null;
        $reliquat_jours = null;

        $hoursPerDay = null;
        if ($holiday_helper->showHoursToDays()) {
            $hoursPerDay = $holiday_helper->hoursPerDay($perso_id);

            $anticipation_jours = $holiday_helper->hoursToDays($conges_anticipation, $perso_id, null, true);
            $credit_jours = $holiday_helper->hoursToDays($conges_credit, $perso_id, null, true);
            $reliquat_jours = $holiday_helper->hoursToDays($conges_reliquat, $perso_id, null, true);
        }

        if ($balance[4] < 0) {
            $balance[4] = 0;
        }

        $show_allday = 0;
        if ($this->config('Conges-Mode') == 'heures'
            and (!$this->config('Conges-Recuperations')
                or $this->config('Conges-Heures'))) {
            $show_allday = 1;
        }

        $lang = $GLOBALS['lang'];
        $templateParams = array(
            'admin'                 => $admin || $adminN2,
            'adminN1'               => $admin,
            'adminN2'               => $adminN2,
            'agents_multiples'      => $agents_multiples,
            'perso_id'              => $perso_id,
            'conges_recuperations'  => $this->config('Conges-Recuperations'),
            'conges_mode'           => $this->config('Conges-Mode'),
            'conges_heures'         => $this->config('Conges-Heures'),
            'conges_demi_journee'   => $this->config('Conges-demi-journees'),
            'conges_tous'           => $this->config('Conges-tous'),
            'CSRFToken'             => $GLOBALS['CSRFSession'],
            'hours_per_day'         => $hoursPerDay,
            'reliquat'              => $reliquat,
            'reliquat2'             => $holiday_helper->HumanReadableDuration($reliquat),
            'reliquat_jours'        => $reliquat_jours,
            'recuperation'          => $recuperation,
            'recuperation_prev'     => $balance[4],
            'balance0'              => dateFr($balance[0]),
            'balance1'              => heure4($balance[1], true),
            'balance4'              => heure4($balance[4], true),
            'credit'                => $credit,
            'credit2'               => $holiday_helper->HumanReadableDuration($credit),
            'credit_jours'          => $credit_jours,
            'anticipation'          => $anticipation,
            'anticipation2'         => $holiday_helper->HumanReadableDuration($anticipation),
            'anticipation_jours'    => $anticipation_jours,
            'agent_name'            => $_SESSION['login_nom'] . ' ' . $_SESSION['login_prenom'],
            'login_id'              => $_SESSION['login_id'],
            'login_nom'             => $_SESSION['login_nom'],
            'login_prenom'          => $_SESSION['login_prenom'],
            'accepted_pending_str'  => $lang['leave_dropdown_accepted_pending'],
            'refused_pending_str'   => $lang['leave_dropdown_refused_pending'],
            'loggedin_id'           => $_SESSION['login_id'],
            'loggedin_name'         => $_SESSION['login_nom'],
            'loggedin_firstname'    => $_SESSION['login_prenom'],
            'selected_agent_id'     => $perso_id,
            'sites_select'          => $sites_select,
            'show_allday'           => $show_allday,
        );

        $this->templateParams($templateParams);

        $date = date("Y-m-d");
        $db = new \db();
        $db->sanitize_string = false;
        $db->query("SELECT * FROM `{$dbprefix}conges_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");

        $holiday_info = array();
        if ($db->result) {
            foreach ($db->result as $elem) {
                $elem['start'] = dateFr($elem['debut']);
                $elem['end'] = dateFr($elem['fin']);
                $holiday_info[] = $elem;
            }
        }

        $this->templateParams(array('holiday_info' => $holiday_info));

        return $this->output('conges/add.html.twig');
    }

    /**
     * @Route("/holiday/accounts", name="holiday.accounts", methods={"GET"})
     */
    public function account(Request $request)
    {

        $droits = $GLOBALS['droits'];
        $admin = false;
        $sites = array();

        for ($i = 1; $i <= $this->config('Multisites-nombre'); $i++) {
            if (in_array((400+$i), $droits) or in_array((600+$i), $droits)) {
                $admin = true;
                $sites[] = $i;
            }
        }

        if (!$admin) {
            return $this->redirectToRoute('access-denied');
        }

        $holiday_helper = new HolidayHelper();
        $show_hours_to_days = $holiday_helper->showHoursToDays();

        // Initialisation des variables
        $agents_supprimes = isset($_SESSION['oups']['conges_agents_supprimes'])
            ? $_SESSION['oups']['conges_agents_supprimes'] : false;
        $agents_supprimes = (isset($_GET['get']) and isset($_GET['supprimes']))
            ? true: $agents_supprimes;
        $agents_supprimes = (isset($_GET['get']) and !isset($_GET['supprimes']))
            ? false : $agents_supprimes;

        $credits_effectifs = isset($_SESSION['oups']['conges_credits_effectifs'])
            ? $_SESSION['oups']['conges_credits_effectifs'] : true;
        $credits_effectifs = (isset($_GET['get']) and isset($_GET['effectifs']))
            ?true : $credits_effectifs;
        $credits_effectifs = (isset($_GET['get']) and !isset($_GET['effectifs']))
            ? false: $credits_effectifs;

        $credits_en_attente = isset($_SESSION['oups']['conges_credits_attente'])
            ? $_SESSION['oups']['conges_credits_attente'] : true;
        $credits_en_attente = (isset($_GET['get']) and isset($_GET['attente']))
            ? true : $credits_en_attente;
        $credits_en_attente = (isset($_GET['get']) and !isset($_GET['attente']))
            ?false : $credits_en_attente;

        global $hours_to_days;
        $hours_to_days = $_SESSION['oups']['conges_hours_to_days'] ?? false;
        $hours_to_days = $_GET['hours_to_days'] ?? $hours_to_days;
        $hours_to_days = (isset($_GET['get']) and !isset($_GET['hours_to_days']))
            ? false : $hours_to_days;

        $_SESSION['oups']['conges_agents_supprimes'] = $agents_supprimes;
        $_SESSION['oups']['conges_credits_effectifs'] = $credits_effectifs;
        $_SESSION['oups']['conges_credits_attente'] = $credits_en_attente;
        $_SESSION['oups']['conges_hours_to_days'] = $hours_to_days;

        $checked1=$agents_supprimes?"checked='checked'":null;
        $checked2=$credits_effectifs?"checked='checked'":null;
        $checked3=$credits_en_attente?"checked='checked'":null;
        $checked4=$hours_to_days?"checked='checked'":null;

        $c = new \conges();
        if ($agents_supprimes) {
            $c->agents_supprimes = array(0,1);
        }
        if ($this->config('Multisites-nombre') > 1) {
            $c->sites = $sites;
        }
        $c->fetchAllCredits();

        $this->templateParams(array(
            'deleted_agents'        => $agents_supprimes,
            'effective_account'     => $credits_effectifs,
            'waiting_credits'       => $credits_en_attente,
            'hours_to_days'         => $hours_to_days,
            'show_hours_to_days'    => $show_hours_to_days,
            'accounts'              => $c->elements
        ));

        $this->templateParams(array('bar' => 'foo'));

        return $this->output('conges/accounts.html.twig');
    }

    /**
     * @Route("/ajax/holiday-halfday-hours", name="ajax.holiday-halfday-hours", methods={"GET"})
     */
    public function halfdayHours(Request $request)
    {
        $agent = $request->get('agent');
        $start = $request->get('start');
        $end = $request->get('end');

        $response = new Response();
        if (!$agent or !$start or !$end) {
            $response->setContent('Wrong parameters');
            $response->setStatusCode(404);
            return $response;
        }

        $hours_helper = new WeekPlanningHelper();
        $hours_first_day = $hours_helper->getTimes($start, $agent);
        $hours_last_day = $hours_helper->getTimes($end, $agent);

        // Define morning's end from the end of the first period of the last day, default 12:00:00
        $morning_end = $hours_last_day[0][1] ?? '12:00:00';
        $afternoon_start = '12:00:00';

        // If the 2nd period exists, afternoon_start = the first hour of this period
        if (!empty($hours_first_day[1][0])) {
            $afternoon_start = $hours_first_day[1][0];

        } else {
            // If the 2nd period doesn't exist and if the first period starts after 12:00,
            // we suppose that this period is an afternoon and use the first hour to define afternoon_start
            if ($hours_first_day[0][0] >= '12:00:00') {
                $afternoon_start = $hours_first_day[0][0];

            // Else, afternoon_start = the end of the single period
            } else {
                $afternoon_start = $hours_first_day[0][1];
            }
        }

        // For the last day
        // If the 2nd period doesn't exist and if the first period starts after 12:00,
        // we suppose that this period is an afternoon and use the first hour to define morning_end
        if (empty($hours_last_day[1][0])) {
            if ($hours_last_day[0][0] >= '12:00:00') {
                $morning_end = $hours_last_day[0][0];
            }
        }

        $result = array(
            'morning_end' => $morning_end,
            'afternoon_start' => $afternoon_start,
        );

        $response->setContent(json_encode($result));
        $response->setStatusCode(200);

        return $response;
    }

    /**
     * @Route("/ajax/check-planning", name="ajax.checkplanning", methods={"POST"})
     */
    public function checkPlanning(Request $request)
    {
        $perso_ids = json_decode($request->get('perso_ids'));
        $start =dateSQL($request->get('start'));
        $end =dateSQL($request->get('end'));
        $maxAgentsDisplay = 5;

        // On consulte le planning de présence de l'agent
        $message = "";
        $agents = array();
        $unknownHours = 0;
        foreach ($perso_ids as $perso_id) {
            $p=new \planningHebdo();
            $p->debut = $start;
            $p->fin = $end;
            $p->perso_id=$perso_id;
            $p->valide=true;
            $p->fetch();
            // Si le planning n'est pas validé pour l'une des dates, on affiche un message d'erreur et on arrête le calcul
            if (empty($p->elements)) {
                $agent = new \personnel();
                $agent->fetchById($perso_id);
                $agent_infos = $agent->elements[0];
                $unknownHours++;
                if ($unknownHours <= $maxAgentsDisplay) {
                    array_push($agents, $agent_infos['prenom'] . " " . $agent_infos['nom']);
                }
            }
        }
        if (!empty($agents)) {
            $message = "Impossible de déterminer le nombre d'heures correspondant aux congés demandés pour les agents suivants: " . join(', ', $agents);
            if ($unknownHours > $maxAgentsDisplay) {
                $agentsLeft = $unknownHours - $maxAgentsDisplay;
                $message .= " et " . $agentsLeft . ($agentsLeft == 1 ? " autre." : " autres.");
            }
        }
        return $this->json($message);
    }

    /**
     * @Route("/ajax/holiday-credit", name="ajax.holidaycredit", methods={"GET"})
     */
    public function checkCredit(Request $request)
    {
        // Initilisation des variables
        $debut =dateSQL($request->get('debut'));
        $fin =dateSQL($request->get('fin'));
        $start_halfday= $request->get('start_halfday');
        $end_halfday= $request->get('end_halfday');
        $perso_id = $request->get('perso_id');
        $is_recover = $request->get('is_recover');

        if (!$perso_id) {
            return $this->json(array('error' => true));
        }

        list($hre_debut, $hre_fin) = HourHelper::StartEndFromRequest($request);

        $c = new \conges();
        $recover = $c->calculCreditRecup($perso_id, $debut);

        // If halfday is checked, starting and
        // ending hours depends on agent's working hours
        if ($start_halfday && $end_halfday) {
            $holidayHelper = new HolidayHelper(array(
                'agent' => $perso_id,
                'start' => $debut,
                'end' => $fin,
                'start_halfday' => $start_halfday,
                'end_halfday' => $end_halfday,
            ));
            list($hre_debut, $hre_fin) = $holidayHelper->halfDayStartEndHours();
        }

        $holidayHlper = new HolidayHelper(array(
            'start' => $debut,
            'hour_start' => $hre_debut,
            'end' => $fin,
            'hour_end' => $hre_fin,
            'perso_id' => $perso_id,
            'is_recover' => $is_recover
        ));
        $result = $holidayHlper->getCountedHours();

        $result['recover'] = $recover;

        return $this->json($result);
    }

    /**
     * @Route("/ajax/current-credits", name="ajax.currentcredits", methods={"get"})
     */
    public function current_credits(Request $request)
    {
        $agent_id = $request->get('id');

        $agent = $this->entityManager->find(Agent::class, $agent_id);

        $hh = new HolidayHelper();
        $holiday_account = array(
            'holiday_balance' => $hh->HumanReadableDuration($agent->conges_reliquat()),
            'holiday_balance_decimal' => $agent->conges_reliquat() ?? 0,
            'holiday_credit' => $hh->HumanReadableDuration($agent->conges_credit()),
            'holiday_credit_decimal' => $agent->conges_credit() ?? 0,
            'holiday_debit' => $hh->HumanReadableDuration($agent->conges_anticipation()),
            'holiday_debit_decimal' => $agent->conges_anticipation() ?? 0
        );

        return $this->json($holiday_account);
    }

    private function save($request)
    {
        $perso_id = $request->get('perso_id');
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $halfday = $request->get('halfday');

        $perso_ids = array();
        if (!empty($perso_id) && $perso_id != 0) {
            $perso_ids[] = $perso_id;
        } else {
            $perso_ids = $request->get('perso_ids');
        }

        $CSRFToken = $request->get('CSRFToken');
        $debutSQL = dateSQL($request->get('debut'));
        $finSQL = dateSQL($request->get('fin'));
        $is_recover = $request->get('is_recover');
        list($hre_debut, $hre_fin) = HourHelper::StartEndFromRequest($request);
        $commentaires=htmlentities($request->get('commentaires'), ENT_QUOTES|ENT_IGNORE, "UTF-8", false);
        $refus = $request->get('refus');
        $valide = $request->get('valide');
        $login_id = $_SESSION['login_id'];
        $lang = $GLOBALS['lang'];

        $request->request->set('valide_init', $valide);

        switch ($valide) {
            case -2:
                $request->request->set('valide', 0);
                $request->request->set('valide_n1', $login_id * -1);
                $request->request->set('validation_n1', date("Y-m-d H:i:s"));
                break;
            case -1:
                $request->request->set('valide', $login_id * -1);
                $request->request->set('valide_n1', $login_id * -1);
                $request->request->set('validation', date("Y-m-d H:i:s"));
                $request->request->set('validation_n1', date("Y-m-d H:i:s"));
                break;
            case 1:
                $request->request->set('valide', $login_id);
                $request->request->set('valide_n1', $login_id);
                $request->request->set('validation', date("Y-m-d H:i:s"));
                $request->request->set('validation_n1', date("Y-m-d H:i:s"));
                break;
            case 2:
                $request->request->set('valide_n1', $login_id);
                $request->request->set('validation_n1', date("Y-m-d H:i:s"));
                $request->request->set('valide', 0);
                break;
        }

        if (!$finSQL) {
            $finSQL = $debutSQL;
        }

        // Enregistrement du congés
        $data = $request->request->all();
        $data['hre_debut'] = $hre_debut;
        $data['hre_fin'] = $hre_fin;

        foreach ($perso_ids as $perso_id) {

            // If halfday is checked, starting and
            // ending hours depends on agent's working hours
            if ($halfday) {
                $holidayHelper = new HolidayHelper(array(
                    'agent' => $perso_id,
                    'start' => $debutSQL,
                    'end' => $finSQL,
                    'start_halfday' => $data['start_halfday'],
                    'end_halfday' => $data['end_halfday'],
                ));
                list($hre_debut, $hre_fin) = $holidayHelper->halfDayStartEndHours();
                $data['hre_debut']= $hre_debut;
                $data['hre_fin']= $hre_fin;

            }

            $holidayHlper = new HolidayHelper(array(
                'start' => $debutSQL,
                'hour_start' => $hre_debut,
                'end' => $finSQL,
                'hour_end' => $hre_fin,
                'perso_id' => $perso_id,
                'is_recover' => $is_recover
            ));
            $result = $holidayHlper->getCountedHours();
            $data['heures'] = $result['hours'];
            $data['minutes'] = $result['minutes'];

            // Enregistrement du congés
            $c = new \conges();
            $c->CSRFToken = $CSRFToken;
            $data['perso_id'] = $perso_id;
            $c->add($data);
            $id = $c->id;

            // Récupération des adresses e-mails de l'agent et des responsables pour l'envoi des alertes
            $agent = $this->entityManager->find(Agent::class, $perso_id);
            $nom = $agent->nom();
            $prenom = $agent->prenom();

            // Choix du sujet et des destinataires en fonction du degré de validation
            switch ($valide) {
            // Modification sans validation
            case 0:
              $sujet="Demande de congés";
              $notifications='2';
              break;
            // Validations Niveau 2
            case 1:
              $sujet="Validation de congés";
              $notifications='4';
              break;
            case -1:
              $sujet="Refus de congés";
              $notifications='4';
              break;
            // Validations Niveau 1
            case 2:
              $sujet = $lang['leave_subject_accepted_pending'];
              $notifications='3';
              break;
            case -2:
              $sujet = $lang['leave_subject_refused_pending'];
              $notifications='3';
              break;
            }

            // Choix des destinataires en fonction de la configuration
            if ($this->config('Absences-notifications-agent-par-agent')) {
                $a = new \absences();
                $a->getRecipients2(null, $perso_id, $notifications, 600, $debutSQL, $finSQL);
                $destinataires = $a->recipients;
            } else {
                $c = new \conges();
                $c->getResponsables($debutSQL, $finSQL, $perso_id);
                $responsables = $c->responsables;
                $a = new \absences();
                $a->getRecipients("-A$notifications", $responsables, $agent);
                $destinataires = $a->recipients;
            }

            // Message qui sera envoyé par email
            $fin = empty($fin) ? $debut : $fin;
            $message = $this->makeMail($sujet, "$prenom $nom", $debut, $fin, $hre_debut, $hre_fin, $commentaires, $refus, $valide, $id);

            // Envoi du mail
            $m=new \CJMail();
            $m->subject = $sujet;
            $m->message=$message;
            $m->to=$destinataires;
            $m->send();

        }

        $msg = 'La demande de congé a été enregistrée';

        return array(
            'msg'       => $msg,
            'msgType'   => 'success',
        );
    }

    private function update($request)
    {
        $post = $request->request->all();

        $perso_id = $request->get('perso_id');
        $id = $request->get('id');
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        list($hre_debut, $hre_fin) = HourHelper::StartEndFromRequest($request);
        $fin = $fin ? $fin : $debut;
        $debutSQL=dateSQL($debut);
        $finSQL=dateSQL($fin);
        $refus = $request->get('refus');
        $valide = $request->get('valide');
        $commentaires = $request->get('commentaires');
        $CSRFToken = $request->get('CSRFToken');

        $lang = $GLOBALS['lang'];

        // If halfday is checked, starting and
        // ending hours depends on agent's working hours
        if ($post['halfday']) {
            $holidayHelper = new HolidayHelper(array(
                'agent' => $perso_id,
                'start' => $debutSQL,
                'end' => $finSQL,
                'start_halfday' => $post['start_halfday'],
                'end_halfday' => $post['end_halfday'],
            ));
            list($hre_debut, $hre_fin) = $holidayHelper->halfDayStartEndHours();
        }

        $post['hre_debut']= $hre_debut;
        $post['hre_fin']= $hre_fin;

        // Enregistre la modification du congés
        $c=new \conges();
        $c->CSRFToken = $CSRFToken;
        $c->update($post);

        // Envoi d'une notification par email
        // Récupération des adresses e-mails de l'agent et des responsables pour m'envoi des alertes
        $agent = $this->entityManager->find(Agent::class, $perso_id);
        $nom = $agent->nom();
        $prenom = $agent->prenom();

        // Choix du sujet et des destinataires en fonction du degré de validation
        switch ($valide) {
        // Modification sans validation
        case 0:
          $sujet="Modification de congés";
          $notifications='2';
          break;
        // Validations Niveau 2
        case 1:
          $sujet="Validation de congés";
          $notifications='4';
          break;
        case -1:
          $sujet="Refus de congés";
          $notifications='4';
          break;
        // Validations Niveau 1
        case 2:
          $sujet = $lang['leave_subject_accepted_pending'];
          $notifications='3';
          break;
        case -2:
          $sujet = $lang['leave_subject_refused_pending'];
          $notifications='3';
          break;
        }

        // Choix des destinataires en fonction de la configuration
        if ($this->config('Absences-notifications-agent-par-agent')) {
            $a = new \absences();
            $a->getRecipients2(null, $perso_id, $notifications, 600, $debutSQL, $finSQL);
            $destinataires = $a->recipients;
        } else {
            $c = new \conges();
            $c->getResponsables($debutSQL, $finSQL, $perso_id);
            $responsables = $c->responsables;
            $a = new \absences();
            $a->getRecipients("-A$notifications", $responsables, $agent);
            $destinataires = $a->recipients;
        }

        // Message qui sera envoyé par email
        $message = $this->makeMail($sujet, "$prenom $nom", $debut, $fin, $hre_debut, $hre_fin, $commentaires, $refus, $valide, $id);

        // Envoi du mail
        $m=new \CJMail();
        $m->subject=$sujet;
        $m->message=$message;
        $m->to=$destinataires;
        $m->send();

        // Si erreur d'envoi de mail, affichage de l'erreur
        $msg2=null;
        $msg2Type=null;
        if ($m->error) {
            $msg2 = $m->error_CJInfo;
            $msg2Type="error";
        }

        $result = array(
            'msg'       => "Le congé a été modifié avec succès",
            'msg2'      => $msg2,
            'msg2Type'  => $msg2Type
        );

        $result['back_to'] = 'holiday';
        if ($this->config('Conges-Recuperations') and $post['debit'] == 'recuperation') {
            $result['back_to'] = 'recover';
            $result['msg'] = "La demande de récupération a été modifiée avec succès";
        }

        return $result;
    }

    /**
     * Make mail message
     */
    private function makeMail($subject, $name, $begin, $end, $begin_hour, $end_hour, $comment, $refusal, $status, $id) {
        $message  = "<b><u>$subject :</u></b><br/>";
        $message .= "<ul><li>Agent : <strong>$name</strong></li>";
        $message .= "<li>Début : <strong>$begin";
        if ($begin_hour!="00:00:00") {
            $message.=" ".heure3($begin_hour);
        }
        $message .="</strong></li>";
        $message .= "<li>Fin : <strong>$end";
        if ($end_hour!="23:59:59") {
            $message.=" ".heure3($end_hour);
        }
        $message .="</strong></li>";
        if ($comment) {
            $message.="<li>Commentaires :<br/>$comment</li>";
        }
        if ($refusal and $status == -1) {
            $message.="<li>Motif du refus :<br/>$refusal</li>";
        }
        $message .="</ul>";

        // ajout d'un lien permettant de rebondir sur la demande
        $url = $this->config('URL') . "/holiday/edit/$id";
        $message.="<p>Lien vers la demande de congé :<br/><a href='$url'>$url</a></p>";

        return $message;
    }
}
