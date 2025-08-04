<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Agent;
use App\Entity\PlanningPositionHistory;

use App\PlanningBiblio\WorkingHours;

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
    use \App\Traits\PlanningJobTrait;

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

        $tableaux = $this->getContextMenuInfos($site, $date, $debut, $fin, $perso_id, $perso_nom, $poste, $CSRFToken, $session);

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
                $fullname = $agent->getFirstname() . ' ' . $agent->getLastname();
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
        $a1->setUndone(true);
        $this->entityManager->persist($a1);

        if ($action_before) {
            $a2 = $this->entityManager
                ->getRepository(PlanningPositionHistory::class)->find($action_before['id']);
            $a2->setUndone(true);
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
        $a1->setUndone(false);
        $this->entityManager->persist($a1);

        if ($action_after) {
            $a2 = $this->entityManager
                ->getRepository(PlanningPositionHistory::class)->find($action_after['id']);
            $a2->setUndone(false);
            $this->entityManager->persist($a2);
            $action_after = $this->convertActionDates($action_after);
            $response['actions'][] = $action_after;
        }

        $this->entityManager->flush();

        return $this->json($response);
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
