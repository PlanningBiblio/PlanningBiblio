<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\Agent;
use App\Model\AbsenceReason;
use App\Model\PlanningPosition;
use App\Model\PlanningPositionHistory;
use App\Model\Position;

use App\PlanningBiblio\WorkingHours;
use App\PlanningBiblio\Helper\PlanningJobHelper;
use App\PlanningBiblio\Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once(__DIR__ . '/../../public/conges/class.conges.php');
require_once(__DIR__ . '/../../public/include/function.php');
require_once(__DIR__ . '/../../public/include/horaires.php');
require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/personnel/class.personnel.php');
require_once(__DIR__ . '/../../public/planning/poste/fonctions.php');
require_once(__DIR__ . '/../../public/planning/poste/class.planning.php');
require_once(__DIR__ . '/../../public/planning/volants/class.volants.php');
require_once(__DIR__ . '/../../public/planningHebdo/class.planningHebdo.php');

class PlanningJobController extends BaseController
{
    private Array $droits;

    #[Route(path: '/planningjob/autofill', name: 'planningjob.autofill', methods: ['POST'])]
    public function autoFill(Request $request)
    {

        if (!$this->csrf_protection($request)) {
            return new Response(json_encode(['error' => 'CSRF Token Error']));
        }

        $session = $request->getSession();
        $date = $request->get('date');
        $site = $request->get('site');
        $CSRFToken = $request->get('CSRFToken');
        $perso_nom = $request->get('perso_nom');
        $perso_id = $request->get('perso_id');

        $datetime = \Datetime::createFromFormat('Y-m-d', $date);
        $p = $this->entityManager->getRepository(PlanningPosition::class)->findBy(array(
            'date' => $datetime,
            'site' => $site,
        ));

        if (!empty($p)) {
            return new Response(json_encode(['error' => 'Le planning n\'est pas vide']));
        }

        $f = new Framework();
        $framework = $f->getFromDate($date, $site);

        // Hours and positions for Planno
        $hours = array();
        $positions = array();

        foreach ($framework as $f) {

            foreach ($f['lignes'] as $ligne) {

                $poste = $ligne['poste'];
                $lastid = null;
                $max_consecutive_positions = 2;
                $consecutive_positions_count = 1;
                foreach ($f['horaires'] as $horaire) {
                    $consecutive_placement_possible = false;
                    $planningJobHelper = new PlanningJobHelper();
                    $results = $planningJobHelper->getContextMenuInfos($site, $date, $horaire['debut'], $horaire['fin'], $perso_id, $perso_nom, $poste, $CSRFToken, $session);
                    $id = null;
                    $available_agents = array();

                    // Available agents
                    if ($results['menu1']['agents']) {
                        foreach ($results['menu1']['agents'] as $available_agent) {
                            if ($lastid != $available_agent['id']) {
                                array_push($available_agents, $available_agent['id']);
                            } else {
                                $consecutive_placement_possible = true;
                            }
                        }
                    }

                    # Do we have to place the same agent consecutively?
                    if (sizeof($available_agents) == 0 &&
                        $consecutive_placement_possible &&
                        $consecutive_positions_count <= $max_consecutive_positions) {
                        array_push($available_agents, $lastid);
                        error_log("Adding agent $lastid $consecutive_positions_count times consecutively ($max_consecutive_positions max)\n");
                        $consecutive_positions_count++;
                    }
                    if (sizeof($available_agents) > 0) {
                        error_log(print_r($available_agents, 1));
                        $id = $available_agents[array_rand($available_agents)];
                    }
                    if ($id != $lastid) {
                        $consecutive_positions_count = 1;
                    }

                    // Unavailable agents
                    #TODO: Add a second button to trigger filling also with unavailable agents ?
                    /*
                    if (!$id) {
                        if ($results['menu2']['agents']) {
                            foreach ($results['menu2']['agents'] as $unavailable_agent) {
                                if ($lastid != $unavailable_agent['id']) {
                                    $id = $unavailable_agent['id'];
                                }
                            }
                        }
                    }
                    */

                    if ($id) {
                        $lastid = $id;
                        $start = \Datetime::createFromFormat('H:i:s', $horaire['debut']);
                        $end = \Datetime::createFromFormat('H:i:s', $horaire['fin']);
                        error_log("Setting agent $id on position $poste from ". $horaire['debut'] . " to " . $horaire['fin'] . " on date $date\n");
                        $datetime = \Datetime::createFromFormat('Y-m-d', $date);

                        #TODO: Do not fill if already in position
                        # Or add a button to replace existing positions
                        $p = new PlanningPosition();
                        $p->date($datetime);
                        $p->perso_id($id);
                        $p->poste($poste);
                        $p->absent(0);     // TODO Add default value on model
                        $p->chgt_login('admin');
                        $p->chgt_time(new \DateTime()); // TODO Add default value on model
                        $p->debut($start);
                        $p->fin($end);
                        $p->site($site);

                        $this->entityManager->persist($p);
                        $this->entityManager->flush();
                    }
                }
            }

        }
        return new Response('{}');
    }


    #[Route(path: '/planningjob/contextmenu', name: 'planningjob.contextmenu', methods: ['GET'])]
    public function contextmenu(Request $request)
    {
        $session = $request->getSession();

        $site = $request->get('site');
        $date = $request->get('date');
        $debut = $request->get('debut');
        $fin = $request->get('fin');
        $perso_id = $request->get('perso_id');
        $perso_nom = $request->get('perso_nom');
        $poste = $request->get('poste');
        $CSRFToken = $request->get('CSRFToken');

        $planningJobHelper = new PlanningJobHelper();
        $tableaux = $planningJobHelper->getContextMenuInfos($site, $date, $debut, $fin, $perso_id, $perso_nom, $poste, $CSRFToken, $session);

        return $this->json($tableaux);
    }

    #[Route(path: '/ajax/planningjob/checkcopy', name: 'ajax.planningjobcheckcopy', methods: ['GET'])]
    public function checkCopy(Request $request)
    {
        // Initilisation des variables
        $date = $request->get('date');
        $start = $request->get('from');
        $end = $request->get('to');
        $agents = json_decode($request->get('agents'));

        $availables = array();
        $unavailables = array();
        $errors = array();

        foreach ($agents as $agent_id) {

            try {
                $agent = $this->entityManager->find(Agent::class, $agent_id);
                $fullname = $agent->prenom() . ' ' . $agent->nom();
                $available = true;

                if ($agent->isAbsentOn("$date $start", "$date $end")) {
                    $available = false;
                }

                if ($available and $agent->isOnVacationOn("$date $start", "$date $end")) {
                    $available = false;
                }

                if ($available) {
                    $d = new \datePl($date);
                    $working_hours = $agent->getWorkingHoursOn($date);
                    $day = $d->planning_day_index_for($agent_id, $working_hours['nb_semaine']);

                    if (!calculSiPresent($start, $end, $working_hours['temps'], $day)) {
                        $available = false;
                    }
                }

                if ($available and $agent->isBlockedOn($date, $start, $end)) {
                    $available = false;
                }

                if ($available) {
                    $availables[] = $agent_id;
                } else {
                    $unavailables[] = $fullname;
                }
            } catch(Exception $e) {
                $errors[] = $e;
            }
        }

        $unavailables_string = !empty($unavailables) ? "\n - " . implode("\n - ", $unavailables) : null;

        $result = array(
            'availables' => $availables,
            'unavailables' => $unavailables_string,
            'errors' => $errors,
        );

        return $this->json($result);
    }

    #[Route(path: '/ajax/planningjob/undo', name: 'planningjob.undo', methods: ['POST'])]
    public function undo(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $session = $request->getSession();

        $date = $request->get('date');
        $site = $request->get('site');

        if (!$this->canManagePlanning($session, $site)) {
            return $this->json('forbiden');
        }

        if (!$date || !$site) {
            $response = new Response();
            $response->setContent('Bad request');
            $response->setStatusCode(400);
            return $response;
        }

        $history = $this->entityManager
            ->getRepository(PlanningPositionHistory::class)
            ->undoable($date, $site);

        // Nothing to cancel.
        if (empty($history)) {
            return $this->json('');
        }

        $action = array_shift($history);
        $action_before = null;
        $response = array(
            'remaining_undo' => 1,
            'actions' => array()
        );

        // Transform full DateTime to sql date or time.
        $action = $this->convertActionDates($action);

        if ($action['play_before'] == 1) {
            $action_before = array_shift($history);
        }

        // Means that after this undo,
        // there will be nothing more to undo,
        // because there is nothing left in history or because the last action is not made by the logged in agent
        if (empty($history) or $history[0]['updated_by'] != $session->get('loginId')) {
            $response['remaining_undo'] = 0;
        }

        $a1 = $this->entityManager
            ->getRepository(PlanningPositionHistory::class)->find($action['id']);
        $a1->undone(1);
        $this->entityManager->persist($a1);

        if ($action_before) {
            $a2 = $this->entityManager
                ->getRepository(PlanningPositionHistory::class)->find($action_before['id']);
            $a2->undone(1);
            $this->entityManager->persist($a2);
            $action_before = $this->convertActionDates($action_before);
            $response['actions'][] = $action_before;
        }

        $this->entityManager->flush();

        $response['actions'][] = $action;

        return $this->json($response);
    }

    #[Route(path: '/ajax/planningjob/redo', name: 'planningjob.redo', methods: ['POST'])]
    public function redo(Request $request)
    {
        if (!$this->csrf_protection($request)) {
            return $this->redirectToRoute('access-denied');
        }

        $session = $request->getSession();

        $date = $request->get('date');
        $site = $request->get('site');
        $CSRFToken = $request->get('CSRFToken');

        if (!$this->canManagePlanning($session, $site)) {
            return $this->json('forbiden');
        }

        if (!$date || !$site) {
            $response = new Response();
            $response->setContent('Bad request');
            $response->setStatusCode(400);
            return $response;
        }

        $history = $this->entityManager
             ->getRepository(PlanningPositionHistory::class)
             ->redoable($date, $site);

        // Nothing to cancel.
        if (empty($history)) {
            return $this->json('');
        }

        $action = array_shift($history);
        $action_after = null;
        $response = array(
            'remaining_redo' => 1,
            'actions' => array()
        );

        // Transform full DateTime to sql date or time.
        $action = $this->convertActionDates($action);

        $response['actions'][] = $action;

        // As we play redo in reverse order,
        // the second element history could
        // be an action to play after.
        if (isset($history[0]) && $history[0]['play_before'] == 1) {
            $action_after = array_shift($history);
        }

        // Means that after this undo,
        // there will be nothing more to undo.
        if (empty($history)) {
            $response['remaining_redo'] = 0;
        }

        $a1 = $this->entityManager
           ->getRepository(PlanningPositionHistory::class)->find($action['id']);
        $a1->undone(0);
        $this->entityManager->persist($a1);

        if ($action_after) {
            $a2 = $this->entityManager
                ->getRepository(PlanningPositionHistory::class)->find($action_after['id']);
            $a2->undone(0);
            $this->entityManager->persist($a2);
            $action_after = $this->convertActionDates($action_after);
            $response['actions'][] = $action_after;
        }

        $this->entityManager->flush();

        return $this->json($response);
    }

    private function canManagePlanning($session, $site)
    {
        if (!$session->get('loginId')) {
            return false;
        }

        $droits = $GLOBALS['droits'];

        if (!in_array((300 + $site), $droits) and !in_array((1000 + $site), $droits)) {
            return false;
        }

        return true;
    }

    private function convertActionDates($action)
    {
        //$date = new \DateTime($action['date']);
        $action['date'] = $action['date']->format('Y-m-d');

        //$beginning = new \DateTime($action['beginning']);
        $action['beginning'] = $action['beginning']->format('H:i:s');

        //$end = new \DateTime($action['end']);
        $action['end'] = $action['end']->format('H:i:s');

        return $action;
    }
}
