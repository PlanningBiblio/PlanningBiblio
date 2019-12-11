<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Model\Agent;
use App\Model\StatedWeek;
use App\Model\StatedWeekColumn;
use App\Model\StatedWeekJob;
use App\Model\StatedWeekTimes;
use App\Model\StatedWeekJobTimes;
use App\Model\StatedWeekPause;

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

        $planning = $this->getPlanningOn($date);

        $this->templateParams(array(
            'CSRFSession'           => $GLOBALS['CSRFSession'],
            'planning'              => $planning,
            'date'                  => $date,
            'week_number'           => $date_pl->semaine,
            'week_days'             => $date_pl->dates,
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
     * @Route("/ajax/statedweekpause/add", name="statedweekpause.add", methods={"POST"})
     */
    public function addPause(Request $request)
    {
        $response = new Response();

        $agent_id = $request->get('agent_id');
        $date = $request->get('date');

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $planning = $this->getPlanningOn($date);
        if (!$planning) {
            $response->setContent('Planning not found');
            $response->setStatusCode(404);
            return $response;
        }

        $pause = new StatedWeekPause();
        $pause->agent_id($agent_id);

        $planning->addPause($pause);

        $this->entityManager->persist($planning);
        $this->entityManager->flush();

        $response->setContent('Pause added');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweekpause/remove", name="statedweekpause.remove", methods={"POST"})
     */
    public function removePause(Request $request)
    {
        $response = new Response();

        $agent_id = $request->get('agent_id');
        $date = $request->get('date');

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $planning = $this->getPlanningOn($date);
        if (!$planning) {
            $response->setContent('Planning not found');
            $response->setStatusCode(404);
            return $response;
        }

        $planning_id = $planning->id();
        $pause = $this->entityManager
            ->getRepository(StatedWeekPause::class)
            ->findOneBy(array('agent_id' => $agent_id, 'planning_id' => $planning_id));

        $this->entityManager->remove($pause);
        $this->entityManager->flush();

        $response->setContent('Pause deleted');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweekjob/add", name="statedweekjob.add", methods={"POST"})
     */
    public function addJobHours(Request $request)
    {
        $response = new Response();

        $agent_id = $request->get('agent_id');
        $date = $request->get('date');
        $job_name = $request->get('job_name');

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job = $this->getJob($date, $job_name);
        if (!$job) {
            $response->setContent('Job not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job_id = $job->id();

        $times = new StatedWeekJobTimes();
        $times->agent_id($agent->id());
        $times->job_id($job->id());
        $times->times('');

        $this->entityManager->persist($times);
        $this->entityManager->flush();

        $response->setContent('Job added');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweekjob/update", name="statedweekjob.update", methods={"POST"})
     */
    public function updateJobHours(Request $request)
    {
        $response = new Response();

        $agent_id = $request->get('agent_id');
        $times = $request->get('times');
        $date = $request->get('date');
        $job_name = $request->get('job_name');

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job = $this->getJob($date, $job_name);
        if (!$job) {
            $response->setContent('Job not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job_id = $job->id();

        $job_agent = $this->entityManager
            ->getRepository(StatedWeekJobTimes::class)
            ->findOneBy(array('agent_id' => $agent_id, 'job_id' => $job_id));

        if (!$job_agent) {
            $response->setContent('Time not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job_agent->times($times);

        $this->entityManager->persist($job_agent);
        $this->entityManager->flush();

        $response->setContent('job hours updated');
        $response->setStatusCode(200);
        return $response;
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

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $column = $this->getColumn($date, $from, $to);
        if (!$column) {
            $response->setContent('Planning times not found');
            $response->setStatusCode(404);
            return $response;
        }

        $column_id = $column->id();

        $times = new StatedWeekTimes();
        $times->agent_id($agent->id());
        $times->column_id($column->id());

        $this->entityManager->persist($times);
        $this->entityManager->flush();

        $response->setContent('Working hours updated');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweekjob/remove", name="statedweekjob.remove", methods={"POST"})
     */
    public function removeJobHours(Request $request)
    {
        $response = new Response();

        $agent_id = $request->get('agent_id');
        $job_name = $request->get('job_name');
        $date = $request->get('date');

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job = $this->getJob($date, $job_name);
        if (!$job) {
            $response->setContent('Job not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job_id = $job->id();

        $job_agent = $this->entityManager
            ->getRepository(StatedWeekJobTimes::class)
            ->findOneBy(array('agent_id' => $agent_id, 'job_id' => $job_id));

        if (!$job_agent) {
            $response->setContent('Time not found');
            $response->setStatusCode(404);
            return $response;
        }

        $this->entityManager->remove($job_agent);
        $this->entityManager->flush();

        $response->setContent('job hours deleted');
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

        $columns = $this->getColumns($date);
        $column_ids = array();
        foreach ($columns as $column) {
            $column_ids[] = $column->id();
        }

        $agent = $this->entityManager
            ->getRepository(StatedWeekTimes::class)
            ->findOneBy(array(
                'agent_id'  => $agent_id,
                'column_id' => $column_ids
            ));

        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $this->entityManager->remove($agent);
        $this->entityManager->flush();

        $response->setContent('Working hours deleted');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweek/placed", name="statedweek.placed", methods={"GET", "POST"})
     */
    public function placedWorkingHours(Request $request)
    {
        $date = $request->get('date');

        $planning = $this->getPlanningOn($date);

        //$columns = $this->getColumns($date);
        //if (empty($columns)) {
        //    $response->setContent('Planning not found');
        //    $response->setStatusCode(404);
        //    return $response;
        //}
        if (!$planning) {
            $response->setContent('Planning not found');
            $response->setStatusCode(404);
            return $response;
        }

        $placed = array();
        foreach ($planning->columns() as $column) {
            $from = $column->starttime()->format('H:i:s');
            $to = $column->endtime()->format('H:i:s');

            $times = $this->entityManager
                ->getRepository(StatedWeekTimes::class)
                ->findBy(array('column_id' => $column->id()));

            foreach ($times as $t) {
                $agent = $this->entityManager->getRepository(Agent::class)->find($t->agent_id());

                $placed[] = array(
                    'place'     => 'planning',
                    'id'        => $agent->id(),
                    'name'      => $agent->nom() . ' ' .$agent->prenom(),
                    'from'      => $from,
                    'to'        => $to,
                    'absent'    => $agent->isAbsentOn($date, $date) ? 1 : 0,
                );
            }
        }

        foreach ($planning->jobs() as $job) {
            $times = $this->entityManager
                ->getRepository(StatedWeekJobTimes::class)
                ->findBy(array('job_id' => $job->id()));

            foreach ($times as $t) {
                $agent = $this->entityManager->getRepository(Agent::class)->find($t->agent_id());

                $placed[] = array(
                    'place'     => 'job',
                    'id'        => $agent->id(),
                    'name'      => $agent->nom() . ' ' .$agent->prenom(),
                    'job_name'  => $job->name(),
                    'times'     => $t->times(),
                    'absent'    => $agent->isAbsentOn($date, $date) ? 1 : 0,
                );
            }
        }

        foreach ($planning->pauses() as $pause) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($pause->agent_id());

                $placed[] = array(
                    'place'     => 'pause',
                    'id'        => $agent->id(),
                    'name'      => $agent->nom() . ' ' .$agent->prenom()
                );
        }

        return $this->json($placed);
    }

    private function updateWeeklyPlanning() {
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

    private function createPlanning($date) {

        $planning = new StatedWeek();
        $planning->date($date);

        $times_ranges = $this->config('statedweek_times_range');
        foreach ($times_ranges as $range) {
            $from = \DateTime::createFromFormat('H:i:s', $range['from']);
            $to = \DateTime::createFromFormat('H:i:s', $range['to']);

            $column = new StatedWeekColumn();
            $column->starttime($from);
            $column->endtime($to);
            $planning->addColumn($column);
        }

        $jobs = $this->config('statedweek_times_job');
        foreach ($jobs as $j) {
            $job = new StatedWeekJob();
            $job->name($j['name']);
            $job->description($j['description']);
            $planning->addJob($job);
        }

        $this->entityManager->persist($planning);
        $this->entityManager->flush();

        return $planning;
    }

    private function getPlanningOn($date)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);

        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->findOneBy(array('date' => $date));

        if (empty($planning)) {
            $planning = $this->createPlanning($date);
        }

        return $planning;
    }

    private function getColumn($date, $from, $to)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->findOneBy(array('date' => $date));

        if (!$planning) {
            return null;
        }


        $from = \DateTime::createFromFormat('H:i:s', $from);
        $to = \DateTime::createFromFormat('H:i:s', $to);
        $column = $this->entityManager
            ->getRepository(StatedWeekColumn::class)
            ->findOneBy(array(
                'planning_id'   => $planning->id(),
                'starttime'     => $from,
                'endtime'       => $to
            ));

        return $column;
    }

    private function getColumns($date)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->findOneBy(array('date' => $date));

        if (!$planning) {
            return array();
        }

        return $planning->columns();
    }

    private function getJob($date, $name)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->findOneBy(array('date' => $date));

        if (!$planning) {
            return null;
        }


        $job = $this->entityManager
            ->getRepository(StatedWeekJob::class)
            ->findOneBy(array(
                'planning_id'   => $planning->id(),
                'name'          => $name
            ));

        return $job;
    }

    private function getJobs($date)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->findOneBy(array('date' => $date));

        if (!$planning) {
            return array();
        }

        return $planning->jobs();
    }
}