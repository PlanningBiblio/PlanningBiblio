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
use App\Model\StatedWeekTemplate;
use App\Model\StatedWeekTimeTemplate;
use App\Model\Interchange;

use App\PlanningBiblio\Helper\StatedWeekHelper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

include_once(__DIR__ . '/../../public/include/function.php');
require_once(__DIR__ . '/../../public/absences/class.absences.php');
require_once(__DIR__ . '/../../public/conges/class.conges.php');

class StatedWeekController extends BaseController
{
    private $CSRFToken;

    /**
     * @Route("/ajax/statedweek/lock", name="statedweek.lock", methods={"GET", "POST"})
     */
    public function lockPlanning(Request $request)
    {
        $response = new Response();

        $this->CSRFToken = $request->get('CSRFToken');
        $date = $request->get('date');
        if (!$date) {
            $response->setContent('Missing date');
            $response->setStatusCode(400);
            return $response;
        }

        $date_pl = new \datePl($date);
        $dates = $date_pl->dates;
        if (!$this->config('Dimanche')) {
            unset($dates[6]);
        }

        $lock = false;
        if ($request->get('lock')) {
            $lock = true;
        }

        $plannings = array();
        foreach ($dates as $d) {
            $planning = $this->getPlanningOn($d);
            $plannings[] = $planning;
            $planning->locked($lock);
            if ($lock) {
                $planning->locker_id($_SESSION['login_id']);
                $planning->locked_on(new \DateTime('now'));
            }
            $this->entityManager->persist($planning);
            $this->entityManager->flush();
        }

        if ($lock) {
            $statedweekHelper = new StatedWeekHelper($plannings);
            $statedweekHelper->CSRFToken = $this->CSRFToken;
            $statedweekHelper->saveToWeeklyPlannings();
            $statedweekHelper->saveToNormalPlanning();
        }

        $current_planning = $this->getPlanningOn($date);
        $return = array();
        if ($current_planning->locked()) {
            $locker = $this->entityManager->getRepository(Agent::class)->find($current_planning->locker_id());
            $return['locker'] = $locker->nom() . ' ' . $locker->prenom();
            $return['locked_on'] = $current_planning->locked_on()->format('d/m/Y H:i');
        }

        return $this->json($return);
    }

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

        $canEdit = 0;
        $droits = $GLOBALS['droits'];
        if (in_array(1401, $droits)) {
            $canEdit = 1;
        }

        // Absences
        $a = new \absences();
        $a->valide = false;
        $a->agents_supprimes = array(0,1,2);
        $a->fetch(null, null, $date, $date);
        $absences = $this->filterAgents($a->elements);

        // Check if the absence is partial
        $today_start = strtotime($date . ' 00:00:00');
        $today_end = strtotime($date . ' 23:59:59');
        foreach ($absences as $index => $absence) {
            $start_absence = strtotime($absence['debut']);
            if ($start_absence < $today_start) {
                $absences[$index]['debut'] = '00:00:00';
            } else {
                $absences[$index]['debut'] = date('H:i:s', $start_absence);
            }

            $end_absence = strtotime($absence['fin']);
            if ($end_absence > $today_end) {
                $absences[$index]['fin'] = '23:59:59';
            } else {
                $absences[$index]['fin'] = date('H:i:s', $end_absence);
            }
        }

        // Holidays
        $c = new \conges();
        $holidays = $this->filterAgents($c->all($date.' 00:00:00', $date.' 23:59:59'));

        $templates = $this->entityManager->getRepository(StatedWeekTemplate::class)->findAll();

        $this->templateParams(array(
            'planning'      => $planning,
            'templates'     => $templates,
            'absences'      => $absences,
            'holidays'      => $holidays,
            'date'          => $date,
            'week_number'   => $date_pl->semaine,
            'week_days'     => $date_pl->dates,
            'pause2'        => $this->Config('PlanningHebdo-Pause2') ? 1 : 0,
            'CSRFSession'   => $GLOBALS['CSRFSession'],
            'canEdit'       => $canEdit,
        ));
        if ($planning->locked()) {
            $locker = $this->entityManager->getRepository(Agent::class)->find($planning->locker_id());
            $this->templateParams(array('locker' => $locker));
        }

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
        $job_name = $request->get('job_name');

	$params = array('supprime' => 0);
	if ($job_name != 'concierges') {
            $params['service'] = $this->config('statedweek_service_filter');
	}

        $availables = array();
        $agents = $this->entityManager
            ->getRepository(Agent::class)
            ->findBy($params, array('nom' => 'ASC'));

        $required_skills = array();
        $jobs_conf = $this->config('statedweek_times_job');
        if ($job_name) {
            foreach ($jobs_conf as $job_conf) {
                if ($job_conf['name'] == $job_name) {
                    $required_skills = $job_conf['skills'];
                }
            }
        }

        foreach ($agents as $agent) {

            if ($agent->id() == 1 || $agent->id() == 2) {
                continue;
            }

            if (!$agent->isInSite($this->config('statedweek_site_filter'))) {
                continue;
            }

            if (!$agent->hasSkills($required_skills)) {
                continue;
            }

            $available = array(
                'fullname'          => $agent->nom() . ' ' . $agent->prenom(),
                'id'                => $agent->id(),
                'absent'            => 0,
                'partially_absent'  => 0,
                'holiday'           => 0,
                'status'            => strtolower(removeAccents(str_replace(' ', '_', $agent->statut()))),
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
                    $absence_times[] = array('from' => heure3($start), 'to' => heure3($end));
                }
                $available['partially_absent'] = $absence_times;
            }

            if ($agent->isOnVacationOn($from, $to)) {
                $available['holiday'] = 1;
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
        $from = \DateTime::createFromFormat('H:i', $request->get('from'));
        $to = \DateTime::createFromFormat('H:i', $request->get('to'));
        $break = \DateTime::createFromFormat('H:i', $request->get('breaktime'));

        $from = $from ? $from : null;
        $to = $to ? $to : null;
        $break = $break ? $break : null;

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
        $times->starttime($from);
        $times->endtime($to);
        $times->breaktime($break);

        $this->entityManager->persist($times);
        $this->entityManager->flush();
        $id = $times->id();

        return $this->json($id);
    }

    /**
     * @Route("/ajax/statedweekjob/update", name="statedweekjob.update", methods={"GET", "POST"})
     */
    public function updateJobHours(Request $request)
    {
        $response = new Response();

        $time_id = $request->get('jobtimeid');
        $from = \DateTime::createFromFormat('H:i', $request->get('from'));
        $to = \DateTime::createFromFormat('H:i', $request->get('to'));
        $break = \DateTime::createFromFormat('H:i', $request->get('breaktime'));
        $date = $request->get('date');
        $CSRFToken = $request->get('CSRFToken');

        $from = $from ? $from : null;
        $to = $to ? $to : null;
        $break = $break ? $break : null;

        $job_agent = $this->entityManager
            ->getRepository(StatedWeekJobTimes::class)
            ->find($time_id);

        if (!$job_agent) {
            $response->setContent('Time not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job_agent->starttime($from);
        $job_agent->endtime($to);
        $job_agent->breaktime($break);

        $this->entityManager->persist($job_agent);
        $this->entityManager->flush();

        $return = array('absence' => 0);
        $from = $from->format('H:i:s');
        $to = $to->format('H:i:s');
        $agent = $this->entityManager->getRepository(Agent::class)->find($job_agent->agent_id());
        if ($absences = $agent->isPartiallyAbsentOn("$date $from", "$date $to")) {
            $return['absence'] = 1;
            $absence_times = array();
            foreach ($absences as $absence) {
                $start = substr($absence['from'], -8);
                $end = substr($absence['to'], -8);
                $absence_times[] = array('from' => heure3($start), 'to' => heure3($end));
            }
            $return['partially_absent'] = $absence_times;
        }

        // Remove related job
        // in normal planning.
        $job = $this->entityManager
            ->getRepository(StatedWeekJob::class)
            ->find($job_agent->job_id());

        $normal_job_id = 0;
        foreach ($this->config('statedweek_times_job') as $normal_job) {
            if ($normal_job['name'] != $job->name()) {
                continue;
            }

            if (isset($normal_job['related_to']) && $normal_job['related_to']) {
                $normal_job_id = $normal_job['related_to'];
            }
        }

        if ($normal_job_id) {
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $delete_params = array(
                'perso_id'  => $job_agent->agent_id(),
                'date'      => $date,
                'poste'     => $normal_job_id,
                'site'      => $this->config('statedweek_site_filter')
            );
            $db->delete("pl_poste", $delete_params);
        }

        return $this->json($return);
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
        $time_id = $request->get('time_id');
        $date = $request->get('date');
        $CSRFToken = $request->get('CSRFToken');

        $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
        if (!$agent) {
            $response->setContent('Agent not found');
            $response->setStatusCode(404);
            return $response;
        }

        $job_agent = $this->entityManager
            ->getRepository(StatedWeekJobTimes::class)
            ->find($time_id);

        if (!$job_agent) {
            $response->setContent('Time not found');
            $response->setStatusCode(404);
            return $response;
        }

        $this->entityManager->remove($job_agent);
        $this->entityManager->flush();

        // Remove related job
        // in normal planning.
        $job = $this->entityManager
            ->getRepository(StatedWeekJob::class)
            ->find($job_agent->job_id());

        $normal_job_id = 0;
        foreach ($this->config('statedweek_times_job') as $normal_job) {
            if ($normal_job['name'] != $job->name()) {
                continue;
            }

            if (isset($normal_job['related_to']) && $normal_job['related_to']) {
                $normal_job_id = $normal_job['related_to'];
            }
        }

        if ($normal_job_id) {
            $db=new \db();
            $db->CSRFToken = $CSRFToken;
            $delete_params = array(
                'perso_id'  => $agent_id,
                'date'      => $date,
                'poste'     => $normal_job_id,
                'site'      => $this->config('statedweek_site_filter')
            );
            $db->delete("pl_poste", $delete_params);
        }

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

                $p = array(
                    'place'         => 'planning',
                    'id'            => $agent->id(),
                    'name'          => $agent->nom() . ' ' .$agent->prenom(),
                    'from'          => $from,
                    'to'            => $to,
                    'absent'        => $agent->isAbsentOn($date, $date) ? 1 : 0,
                    'status'        => strtolower(removeAccents(str_replace(' ', '_', $agent->statut()))),
                    'interchange'   => 0,
                );

                $asked_interchange = $this->entityManager
                    ->getRepository(Interchange::class)
                    ->findOneBy(array('requester' => $agent->id(), 'planning' => $planning->id()));

                if ($asked_interchange && $asked_interchange->status() == 'VALIDATED') {
                    $asked = $this->entityManager
                        ->getRepository(Agent::class)
                        ->find($asked_interchange->asked());

                    $p['interchange'] = $asked->prenom() . ' ' . $asked->nom();
                }

                $interchange = $this->entityManager
                    ->getRepository(Interchange::class)
                    ->findOneBy(array('asked' => $agent->id(), 'planning' => $planning->id()));

                if ($interchange && $interchange->status() == 'VALIDATED') {
                    $requester = $this->entityManager
                        ->getRepository(Agent::class)
                        ->find($interchange->requester());

                    $p['interchange'] = $requester->prenom() . ' ' . $requester->nom();
                }

                if ($absences = $agent->isPartiallyAbsentOn("$date $from", "$date $to")) {
                    $p['absent'] = 0;
                    $absence_times = array();
                    foreach ($absences as $absence) {
                        $start = substr($absence['from'], -8);
                        $end = substr($absence['to'], -8);
                        $absence_times[] = array('from' => heure3($start), 'to' => heure3($end));
                    }
                    $p['partially_absent'] = $absence_times;
                }

                if ($agent->isOnVacationOn("$date $from", "$date $to")) {
                    $p['holiday'] = 1;
                }

                $placed[] = $p;
            }
        }

        foreach ($planning->jobs() as $job) {
            $times = $this->entityManager
                ->getRepository(StatedWeekJobTimes::class)
                ->findBy(array('job_id' => $job->id()));

            foreach ($times as $t) {
                $agent = $this->entityManager->getRepository(Agent::class)->find($t->agent_id());
                $from = $t->starttime() ? $t->starttime()->format('H:i') : '';
                $to = $t->endtime() ? $t->endtime()->format('H:i') : '';
                $break = $t->breaktime() ? $t->breaktime()->format('H:i') : '';

                $p = array(
                    'place'     => 'job',
                    'id'        => $agent->id(),
                    'jobtimeid'    => $t->id(),
                    'name'      => $agent->nom() . ' ' .$agent->prenom(),
                    'job_name'  => $job->name(),
                    'from'      => $from,
                    'to'        => $to,
                    'breaktime' => $break,
                    'absent'    => $agent->isAbsentOn($date, $date) ? 1 : 0,
                    'status'    => strtolower(removeAccents(str_replace(' ', '_', $agent->statut()))),
                );

                if ($absences = $agent->isPartiallyAbsentOn("$date $from", "$date $to")) {
                    $p['absent'] = 0;
                    $absence_times = array();
                    foreach ($absences as $absence) {
                        $start = substr($absence['from'], -8);
                        $end = substr($absence['to'], -8);
                        $absence_times[] = array('from' => heure3($start), 'to' => heure3($end));
                    }
                    $p['partially_absent'] = $absence_times;
                }

                if ($agent->isOnVacationOn("$date $from", "$date $to")) {
                    $p['holiday'] = 1;
                }

                $placed[] = $p;
            }
        }

        foreach ($planning->pauses() as $pause) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($pause->agent_id());

                $placed[] = array(
                    'place'     => 'pause',
                    'id'        => $agent->id(),
                    'name'      => $agent->nom() . ' ' .$agent->prenom(),
                    'status'    => strtolower(removeAccents(str_replace(' ', '_', $agent->statut()))),
                );
        }

        $names  = array_column($placed, 'name');
        array_multisort($names, SORT_ASC, $placed);

        return $this->json($placed);
    }

    /**
     * @Route("/ajax/statedweek/emptyplanning", name="statedweek.empty", methods={"GET", "POST"})
     */
    public function checkWeekPlanning(Request $request)
    {
        $response = new Response();

        $date = $request->get('date');
        if (!$date) {
            $response->setContent('Missing date');
            $response->setStatusCode(400);
            return $response;
        }

        $date_pl = new \datePl($date);
        $dates = $date_pl->dates;
        if (!$this->config('Dimanche')) {
            unset($dates[6]);
        }

        $empty_plannings = array();
        foreach ($dates as $d) {
            $planning = $this->getPlanningOn($d, false);
            if (empty($planning)) {
                $empty_plannings[] = dateAlpha($d);
            }
        }

        return $this->json($empty_plannings);
    }

    /**
     * @Route("/ajax/statedweek/template/load", name="statedweek.template.load", methods={"POST"})
     */
    public function loadTemplate(Request $request)
    {
        $response = new Response();

        $date = $request->get('date');
        $template_id = $request->get('template');
        $template = $this->entityManager->getRepository(StatedWeekTemplate::class)->find($template_id);

        $date_pl = new \datePl($date);
        $dates = $date_pl->dates;
        foreach ($dates as $day_index => $d) {
            if ($template->type() == 'day' && $d != $date) {
                continue;
            }

            if ($template->type() == 'day') {
                $day_index = 0;
            }

            $planning = $this->getPlanningOn($d);
            $this->emptyPlanning($planning);

            $times = $template->times();
            $this->templateToPlanning($times, $planning, $day_index);
        }

        $response->setContent('Template loaded');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweek/template", name="statedweek.template.add", methods={"POST"})
     */
    public function addTemplate(Request $request)
    {
        $response = new Response();

        $date = $request->get('date');
        $name = $request->get('name');
        $isweek = $request->get('week');

        $existing_template = $this->entityManager
          ->getRepository(StatedWeekTemplate::class)
          ->findOneBy(array('name' => $name));

        if ($existing_template) {
          $response->setContent('Template exists');
          $response->setStatusCode(200);
          return $response;
        }

        $template = new StatedWeekTemplate();
        $template->name($name);
        $template->type($isweek ? 'week' : 'day');

        $date_pl = new \datePl($date);
        $dates = $date_pl->dates;
        foreach ($dates as $day_index => $d) {
            if ($isweek == 0 && $d != $date) {
                continue;
            }

            if ($isweek == 0) {
                $day_index = 0;
            }

            $planning = $this->getPlanningOn($d, false);
            if (!$planning) {
                continue;
            }

            $columns = $planning->columns();
            $this->columnsToTemplate($columns, $template, $day_index);

            $jobs = $planning->jobs();
            $this->jobsToTemplate($jobs, $template, $day_index);

            $pauses = $planning->pauses();
            $this->pausesToTemplate($pauses, $template, $day_index);
        }

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        $response->setContent('Template saved');
        $response->setStatusCode(200);
        return $response;
    }

    /**
     * @Route("/ajax/statedweek/slothours", name="statedweek.slothours", methods={"GET", "POST"})
     */
    public function updateSlotHours(Request $request)
    {
        $response = new Response();

        $date = $request->get('date');
        $type = $request->get('type');
        $from = $request->get('from');
        $to = $request->get('to');

        $planning = $this->getPlanningOn($date, false);
        if (!$planning) {
            $response->setContent('Planning not found');
            $response->setStatusCode(404);
            return $response;
        }

        $column = $this->entityManager
          ->getRepository(StatedWeekColumn::class)
          ->findOneBy(array('planning_id' => $planning->id(), 'type' => $type));

        $column->starttime(\DateTime::createFromFormat('H:i', $from));
        $column->endtime(\DateTime::createFromFormat('H:i', $to));

        $this->entityManager->persist($column);
        $this->entityManager->flush();

        $new_from = $column->starttime()->format('H:i');
        $new_to = $column->endtime()->format('H:i');

        return $this->json(array('from' => heure3($new_from), 'to' => heure3($new_to)));
    }

    private function templateToPlanning($times, $planning, $day)
    {
        $columns_map = array();
        foreach ($planning->columns() as $column) {
            $columns_map[$column->type()] = $column->id();
        }

        $jobs_map = array();
        foreach ($planning->jobs() as $job) {
            $jobs_map[$job->type()] = $job->id();
        }

        foreach ($times as $time) {
            if ($time->day_index() != $day) {
                continue;
            }

            if ($time->job() == 'pause') {
                $pause = new StatedWeekPause();
                $pause->agent_id($time->agent_id());
                $planning->addPause($pause);
            }

            if (substr($time->job(), -4) == 'slot') {
                $c_time = new StatedWeekTimes();
                $c_time->agent_id($time->agent_id());
                $c_time->column_id($columns_map[$time->job()]);
                $this->entityManager->persist($c_time);
            }

            if (substr($time->job(), -3) == 'job') {
                $c_time = new StatedWeekJobTimes();
                $c_time->agent_id($time->agent_id());
                $c_time->job_id($jobs_map[$time->job()]);
                $c_time->starttime($time->starttime());
                $c_time->endtime($time->endtime());
                $c_time->breaktime($time->breaktime());
                $this->entityManager->persist($c_time);
            }
        }

        $this->entityManager->persist($planning);
        $this->entityManager->flush();
    }

    private function emptyPlanning($planning)
    {
        $columns = $planning->columns();
        foreach ($columns as $column) {
            $times = $this->entityManager
                ->getRepository(StatedWeekTimes::class)
                ->findBy(array('column_id' => $column->id()));

            foreach ($times as $time) {
                $this->entityManager->remove($time);
            }

        }

        $jobs = $planning->jobs();
        foreach ($jobs as $job) {
            $times = $this->entityManager
                ->getRepository(StatedWeekJobTimes::class)
                ->findBy(array('job_id' => $job->id()));

            foreach ($times as $time) {
                $this->entityManager->remove($time);
            }
        }

        $pauses = $planning->pauses();
        foreach ($pauses as $pause) {
            $this->entityManager->remove($pause);
        }

        $this->entityManager->flush();
    }

    private function pausesToTemplate($pauses, $template, $day)
    {
        foreach ($pauses as $pause) {
            $time_template = new StatedWeekTimeTemplate();
            $time_template->day_index($day);
            $time_template->job('pause');
            $time_template->agent_id($pause->agent_id());
            $template->addTime($time_template);
        }
    }

    private function jobsToTemplate($jobs, $template, $day)
    {
        foreach ($jobs as $job) {
            $type = $job->type();
            $times = $this->entityManager
                ->getRepository(StatedWeekJobTimes::class)
                ->findBy(array('job_id' => $job->id()));

            foreach ($times as $time) {
                $time_template = new StatedWeekTimeTemplate();
                $time_template->day_index($day);
                $time_template->job($type);
                $time_template->agent_id($time->agent_id());
                $time_template->starttime($time->starttime());
                $time_template->endtime($time->endtime());
                $time_template->breaktime($time->breaktime());
                $template->addTime($time_template);
            }
        }
    }

    private function columnsToTemplate($columns, $template, $day)
    {
        foreach ($columns as $column) {
            $type = $column->type();
            $times = $this->entityManager
                ->getRepository(StatedWeekTimes::class)
                ->findBy(array('column_id' => $column->id()));

            foreach ($times as $time) {
                $time_template = new StatedWeekTimeTemplate();
                $time_template->day_index($day);
                $time_template->job($type);
                $time_template->agent_id($time->agent_id());
                $template->addTime($time_template);
            }
        }
    }

    private function createPlanning($date) {

        $planning = new StatedWeek();
        $planning->date($date);

        $times_ranges = $this->config('statedweek_times_range');
        foreach ($times_ranges as $index => $range) {
            $from = \DateTime::createFromFormat('H:i:s', $range['from']);
            $to = \DateTime::createFromFormat('H:i:s', $range['to']);

            $type = '';
            switch($index) {
                case 0:
                    $type = 'first-slot';
                    break;
                case 1:
                    $type = 'second-slot';
                    break;
                case 2:
                    $type = 'third-slot';
                    break;
            }

            $column = new StatedWeekColumn();
            $column->starttime($from);
            $column->endtime($to);
            $column->type($type);
            $planning->addColumn($column);
        }

        $jobs = $this->config('statedweek_times_job');
        foreach ($jobs as $index => $j) {
            $type = '';
            switch($index) {
                case 0:
                    $type = 'first-job';
                    break;
                case 1:
                    $type = 'second-job';
                    break;
                case 2:
                    $type = 'third-job';
                    break;
            }

            $job = new StatedWeekJob();
            $job->name($j['name']);
            $job->description($j['description']);
            $job->type($type);
            $planning->addJob($job);
        }

        $this->entityManager->persist($planning);
        $this->entityManager->flush();

        return $planning;
    }

    private function getPlanningOn($date, $create = true)
    {
        $date = \DateTime::createFromFormat('Y-m-d', $date);

        $planning = $this->entityManager
            ->getRepository(StatedWeek::class)
            ->findOneBy(array('date' => $date));

        if (empty($planning) && $create) {
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

    private function filterAgents($elements)
    {
        $filtered = array();
        foreach ($elements as $elem) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($elem['perso_id']);

            if ($agent->supprime() == 1) {
                continue;
            }

            if ($agent->service() != $this->config('statedweek_service_filter')) {
                continue;
            }

            if (!$agent->isInSite($this->config('statedweek_site_filter'))) {
                continue;
            }

            $elem['status'] = strtolower(removeAccents(str_replace(' ', '_', $agent->statut())));

            $filtered[] = $elem;
        }

        return $filtered;
    }
}
