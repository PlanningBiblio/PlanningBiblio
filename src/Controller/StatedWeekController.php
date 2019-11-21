<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\Agent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/include/feries.php');

class StatedWeekController extends BaseController
{
    /**
     * @Route("/statedweek", name="statedweek.index", methods={"GET"})
     */
    public function index(Request $request)
    {

        $date = $request->get('date');
        if (!$date) {
            $date = date('Y-m-d');
        }

        $date_pl = new \datePl($date);

        $this->templateParams(array(
            'CSRFSession'           => $GLOBALS['CSRFSession'],
            'plcolumns'             => $this->config('statedweek_times_range'),
            'sunday_enabled'        => $this->config('Dimanche'),
            'date'                  => $date,
            'week_number'           => $date_pl->semaine,
            'week_days'             => $date_pl->dates,
            'date_text'             => dateAlpha($date),
            'public_holiday'        => jour_ferie($date),
        ));

        return $this->output('statedweek/index.html.twig');
    }

    /**
     * @Route("/ajax/statedweek/availables", name="statedweek.availables", methods={"POST"})
     */
    public function availableAgent(Request $request)
    {
        $date = $request->get('date');
        $from = $date . ' ' . $request->get('from');
        $to = $date . ' ' . $request->get('to');

        $availables = array();
        $agents = $this->entityManager->getRepository(Agent::class)->findAll();

        foreach ($agents as $agent) {

            if ($agent->id() == 1 || $agent->id() == 2) {
                continue;
            }

            $available = array(
                'fullname'          => $agent->nom() . ' ' . $agent->prenom(),
                'id'                => $agent->id(),
                'absent'            => 0,
                'partially_absent'  => 0,
            );

            if ($agent->isAbsentOn($from, $to)) {
                $available['absent'] = 1;
            }

            if ($absences = $agent->isPartiallyAbsentOn($from, $to)) {
                $available['absent'] = 0;
                $absence_times = array();
                foreach ($absences as $absence) {
                    $start = substr($absence['from'], -8);
                    $end = substr($absence['to'], -8);
                    $absence_times[] = array('from' => $start, 'to' => $end);
                }
                $available['partially_absent'] = $absence_times;
            }

            $availables[] = $available;
        }

        return $this->json($availables);
    }

    /**
     * @Route("/ajax/statedweek/add", name="statedweek.add", methods={"POST"})
     */
    public function addWorkingHours(Request $request)
    {
        $response = new Response();

        $agent_id = $request->get('agent_id');
        $from = $request->get('from');
        $to = $request->get('to');
        $date = $request->get('date');
        $CSRFToken = $request->get('CSRFToken');

        if (!$agent_id) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        if (!$from || !$to) {
            $response->setContent('Missing hours');
            $response->setStatusCode(400);
            return $response;
        }

        if (!$date) {
            $response->setContent('Missing date');
            $response->setStatusCode(400);
            return $response;
        }

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        $workingHours = $agent->getWorkingHoursOn($date);

        $date_pl = new \datePl($date);
        $day_index = $date_pl->position -1;

        // Edit current working hours
        if (!empty($workingHours)) {
            $workingHours['temps'][$day_index] = array($from, '', '', $to);
            $workingHours['CSRFToken'] = $CSRFToken;
            $p = new \planningHebdo();
            $p->update($workingHours);
            if ($p->error) {
                $response->setContent('An error occured');
                $response->setStatusCode(424);
                return $response;
            }
            $response->setContent('Working hours updated');
            $response->setStatusCode(200);
            return $response;
        }

        // Adding a new working hours for the current week.
        $p=new \planningHebdo();
        $hours = array(
            array('', '', '', ''),
            array('', '', '', ''),
            array('', '', '', ''),
            array('', '', '', ''),
            array('', '', '', ''),
            array('', '', '', ''),
        );

        if ($this->config('Dimanche')) {
            $hours[] = array('', '', '', '');
        }

        $hours[$day_index] = array($from, '', '', $to);

        $workingHours = array(
            'perso_id'  => $agent_id,
            'debut'     => $date_pl->dates[0],
            'fin'       => end($date_pl->dates),
            'CSRFToken' => $CSRFToken,
            'temps'     => $hours
        );

        $p->add($workingHours);

        if ($p->error) {
            $response->setContent('An error occured');
            $response->setStatusCode(424);
            return $response;
        }

        $response->setContent('Working hours updated');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweek/remove", name="statedweek.remove", methods={"POST"})
     */
    public function removeWorkingHours(Request $request)
    {
        $response = new Response();

        $agent_id = $request->get('agent_id');
        $date = $request->get('date');
        $CSRFToken = $request->get('CSRFToken');

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        $workingHours = $agent->getWorkingHoursOn($date);

        $date_pl = new \datePl($date);
        $day_index = $date_pl->position -1;

        if (empty($workingHours)) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $workingHours['temps'][$day_index] = array('', '', '', '');
        $workingHours['CSRFToken'] = $CSRFToken;
        $p = new \planningHebdo();
        $p->update($workingHours);
        if ($p->error) {
            $response->setContent('An error occured');
            $response->setStatusCode(424);
            return $response;
        }
        $response->setContent('Working hours updated');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweek/placed", name="statedweek.placed", methods={"GET", "POST"})
     */
    public function placedWorkingHours(Request $request)
    {
        $date = $request->get('date');

        $placed = array();
        $agents = $this->entityManager->getRepository(Agent::class)->findAll();
        $date_pl = new \datePl($date);
        $day_index = $date_pl->position -1;

        foreach($agents as $agent) {
            // Exclude admin and everybody.
            if ($agent->id() == 1 || $agent->id() == 2) {
                continue;
            }

            $workingHours = $agent->getWorkingHoursOn($date);

            // Agent is not placed.
            if (empty($workingHours)) {
                continue;
            }

            $placed[] = array(
                'id'        => $agent->id(),
                'name'      => $agent->nom() . ' ' .$agent->prenom(),
                'from'      => $workingHours['temps'][$day_index][0],
                'to'        => end($workingHours['temps'][$day_index]),
                'absent'    => $agent->isAbsentOn($date, $date) ? 1 : 0,
            );

        }

        return $this->json($placed);
    }
}