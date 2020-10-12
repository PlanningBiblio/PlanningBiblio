<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;

use App\Model\Agent;
use App\Model\StatedWeekJobTimes;
use App\Model\StatedWeekJob;
use App\Model\StatedWeekTimes;

use App\PlanningBiblio\Helper\TimeHelper;

include_once(__DIR__ . '/../../../public/include/function.php');

class StatedWeekHelper extends BaseHelper
{
    private $plannings = array();

    private $planning_ids = array();

    private $pl_key;

    private $current_day;

    private $dates = array();

    private $columns = array();

    private $fixed_breaktime = 0;

    public $planning_agents = array();

    public $CSRFToken;

    public function __construct($plannings)
    {
        parent::__construct();

        $this->plannings = $plannings;

        foreach ($plannings as $planning) {
            $this->planning_ids[] = $planning->id();
            $date = $planning->date()->format('Y-m-d');
            $this->dates[] = $date;
            $jobs = $planning->jobs();
            $columns = $planning->columns();
            $this->current_day = $this->dayIndexFor($date);

            foreach ($jobs as $job) {
                $times = $this->entityManager
                    ->getRepository(StatedWeekJobTimes::class)
                    ->findBy(array('job_id' => $job->id()));

                $this->extractTimes($times, 'job');
            }

            foreach ($columns as $column) {
                $this->columns[$column->id()] = array(
                    'from' => $column->starttime()->format('H:i:s'),
                    'to' => $column->endtime()->format('H:i:s'),
                );
                $times = $this->entityManager
                    ->getRepository(StatedWeekTimes::class)
                    ->findBy(array('column_id' => $column->id()));

                $this->extractTimes($times, 'column');
            }
        }

        if ($this->config('statedweek_3columns_breaktime')) {
            $this->fixed_breaktime = $this->config('statedweek_3columns_breaktime');
        }
    }

    public function saveToNormalPlanning()
    {
        $planning_agents = $this->planning_agents;
        foreach ($planning_agents as $agent_id => $planning) {
            foreach ($planning as $day_index => $elem) {
                if ($elem['type'] != 'job') {
                    continue;
                }

                $date = $this->dates[$day_index];
                $normal_planning = $this->getNormalPlanning($date);
                if (!$normal_planning) {
                    continue;
                }


                $times = $elem['times'];
                foreach ($times as $time) {
                    $job_id = $time->job_id();

                    if (!$time->starttime() && !$time->endtime()) {
                        continue;
                    }

                    $job = $this->entityManager
                        ->getRepository(StatedWeekJob::class)
                        ->find($job_id);

                    $normal_job_id = $this->getRelatedNormalJob($job);

                    // Retrieve planning's line for the job.
                    $line = $this->getTable($normal_planning['tableau'], $normal_job_id);
                    if (!$line) {
                        continue;
                    }

                    // Retrieve time slots
                    $time_slots = $this->getTimeSlots($line, $time);
                    $db = new \db();
                    $db->CSRFToken = $this->CSRFToken;
                    foreach ($time_slots as $time_slot) {
                        if ($this->normalJobExists($agent_id, $date,
                                        $normal_job_id, $time_slot['debut'],
                                        $time_slot['fin'],
                                        $this->config('statedweek_site_filter')))
                        {
                            continue;
                        }

                        $data = array(
                            'perso_id'      => $agent_id,
                            'date'          => $date,
                            'poste'         => $normal_job_id,
                            'chgt_login'    => $_SESSION['login_id'],
                            'debut'         => $time_slot['debut'],
                            'fin'           => $time_slot['fin'],
                            'site'          => $this->config('statedweek_site_filter')
                        );
                        $db->insert('pl_poste', $data);
                    }
                }
            }
        }
    }

    private function normalJobExists($agent_id, $date, $job_id, $start, $end, $site)
    {
        $filters = array(
            'perso_id'  => $agent_id,
            'date'      => $date,
            'poste'     => $job_id,
            'debut'     => $start,
            'fin'       => $end,
            'site'      => $site

        );

        $db = new \db();
        $db->select2("pl_poste", "*", $filters);

        if ($db->result) {
            return true;
        }

        return false;
    }

    private function getTimeSlots($line, $time)
    {
        // select * from pl_poste_horaires where fin >= '10:00:00' and debut <= '18:00:00' and tableau = 1 and numero = 1;
        $start = $time->starttime()->format('H:i:s');
        $end = $time->endtime()->format('H:i:s');
        $params = array(
            'tableau'   => $line['tableau'],
            'numero'    => $line['numero'],
            'fin'       => ">$start",
            'debut'     => "<$end"
        );

        $db = new \db();
        $db->select2('pl_poste_horaires', null, $params);

        $time_slots = $db->result;
        if (is_array($time_slots)) {
            return $time_slots;
        }
        return array();
    }

    private function getTable($planning_id, $job_id)
    {
        $db = new \db();
        $db->select2('pl_poste_lignes', null, array('numero' => $planning_id, 'poste' => $job_id));

        $tables = $db->result;
        if (is_array($tables)) {
            return $tables[0];
        }
        return null;
    }

    public function saveToWeeklyPlannings()
    {
        $planning_agents = $this->planning_agents;
        $planning_ids = $this->planning_ids;

        $this->pl_key = join('_', $this->planning_ids);

        foreach ($planning_agents as $agent_id => $planning) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($agent_id);
            $workingHours = $this->getWorkingHours($agent);

            if (!empty($workingHours)) {
                $this->updateWorkingHours($workingHours, $planning);
                continue;
            }

            $this->addWorkingHours($planning, $agent);
        }

        $db = new \db();
        $fin = end($this->dates);
        $agent_ids = implode(',', array_keys($planning_agents));
        $query = "SELECT id FROM {$GLOBALS['config']['dbprefix']}planning_hebdo ";
        $query .= "WHERE debut = '{$this->dates[0]}' AND fin = '$fin' ";
        $query .= "AND cle like '{$this->pl_key}%' ";
        $query .= "AND perso_id NOT IN ($agent_ids);";
        $db->query($query);
        $pl_ids = $db->result;

        if (!empty($pl_ids)) {
            $ids = array();
            foreach ($pl_ids as $pl_id) {
                $ids[] = $pl_id['id'];
            }
            $ids = implode(',', $ids);
            $db = new \db();
            $db->query("DELETE FROM {$GLOBALS['config']['dbprefix']}planning_hebdo WHERE id IN ($ids);");
        }
    }

    private function updateWorkingHours($workingHours, $planning)
    {
        foreach ($this->dates as $day_index => $d) {
            $today = DayPlanningHelper::emptyDay();
            $break = 0;

            if (isset($planning[$day_index]) && $planning[$day_index]['type'] == 'job') {
                list($jobs, $break) = $this->prepareJobsForPlanning($planning[$day_index]['times']);
                foreach ($jobs as $job) {
                    $today[$job['start_index']] = $job['from'];
                    $today[$job['end_index']] = $job['to'];
                }
            }

            if (isset($planning[$day_index]) && $planning[$day_index]['type'] == 'column') {
                // Only one column by day is permitted.
                $time = $planning[$day_index]['times'][0];
                $today[0] = $this->columns[$time->column_id()]['from'];
                $today[1] = $this->columns[$time->column_id()]['to'];
                $break = $this->fixed_breaktime;
            }

            $today[4] = $this->config('statedweek_site_filter');
            $this->addDay($workingHours['temps'], $today, $day_index);
            $this->addDay($workingHours['breaktime'], $break, $day_index);
        }

        $workingHours['validation'] = 2;
        $workingHours['cle'] = $this->pl_key;
        $workingHours['CSRFToken'] = $this->CSRFToken;
        $p = new \planningHebdo();
        $p->update($workingHours);

        // Set cle here because the add() method
        // doesn't permit that.
        $id = $workingHours['id'];
        $cle = $this->pl_key . "_$id";
        $dbprefix = $GLOBALS['dbprefix'];

        $db = new \dbh();
        $db->CSRFToken = $this->CSRFToken;
        $db->prepare("UPDATE `{$dbprefix}planning_hebdo` SET `cle` = :cle WHERE `id` = :id");
        $db->execute(array(':cle' => $cle, ':id' => $id));
    }

    private function addWorkingHours($planning, $agent)
    {
        $ph = new \planningHebdo();
        $hours = WeekPlanningHelper::emptyPlanning();
        $breaktimes = WeekPlanningHelper::emptyBreaktimes();

        foreach ($planning as $day_index => $p) {
            $today = DayPlanningHelper::emptyDay();
            $break = $this->fixed_breaktime;

            if ($p['type'] == 'job') {
                list($jobs, $break) = $this->prepareJobsForPlanning($p['times']);
                foreach ($jobs as $job) {
                    $today[$job['start_index']] = $job['from'];
                    $today[$job['end_index']] = $job['to'];
                }
            }

            if ($p['type'] == 'column') {
                $time = $p['times'][0];
                $today[0] = $this->columns[$time->column_id()]['from'];
                $today[1] = $this->columns[$time->column_id()]['to'];
            }

            $today[4] = $this->config('statedweek_site_filter');
            $this->addDay($hours, $today, $day_index);
            $this->addDay($breaktimes, $break, $day_index);
        }

        $workingHours = array(
            'perso_id'      => $agent->id(),
            'debut'         => $this->dates[0],
            'fin'           => end($this->dates),
            'CSRFToken'     => $this->CSRFToken,
            'temps'         => $hours,
            'breaktime'     => $breaktimes,
            'validation'    => 2,
        );

        $ph->add($workingHours);

        // Set cle here because the add() method
        // doesn't permit that.
        $ph = new \planningHebdo();
        $ph->perso_id = $agent->id();
        $ph->debut = $this->dates[0];
        $ph->fin = end($this->dates);
        $ph->valide = true;
        $ph->fetch();

        if (!empty($ph->elements)) {
            $id = $ph->elements[0]['id'];
            $cle = $this->pl_key . "_$id";
            $dbprefix = $GLOBALS['dbprefix'];

            $db = new \dbh();
            $db->CSRFToken = $this->CSRFToken;
            $db->prepare("UPDATE `{$dbprefix}planning_hebdo` SET `cle` = :cle WHERE `id` = :id");
            $db->execute(array(':cle' => $cle, ':id' => $id));
        }
    }

    private function prepareJobsForPlanning($times)
    {
        $jobs = array();
        $total_break = '';
        foreach ($times as $t) {
            $from = $t->starttime() ? $t->starttime()->format('H:i:s') : '';
            $to = $t->endtime() ? $t->endtime()->format('H:i:s') : '';
            $break = $t->breaktime() ? $t->breaktime()->format('H:i:s') : '00:00:00';

            if ($break) {
                if (empty($total_break)) {
                    $total_break = new TimeHelper($break);
                } else {
                    $total_break->add($break);
                }
            }

            $jobs[] = array(
                'from'      => $from,
                'to'        => $to,
                'break'     => $break,
                'agent'     => $t->agent_id()
            );
        }

        usort($jobs, function($a, $b) {
            return ($a['from'] < $b['from']) ? -1 : 1;
        });

        $break = $total_break->getDecimal();

        // 3 (maximum) jobs occupied this day.
        if (count($jobs) == 3) {
            $jobs[2]['start_index'] = 6;
            $jobs[2]['end_index'] = 3;

            $jobs[1]['start_index'] = 2;
            $jobs[1]['end_index'] = 5;

            $jobs[0]['start_index'] = 0;
            $jobs[0]['end_index'] = 1;

            return array($jobs, $break);
        }

        // 2 jobs this day.
        if (count($jobs) == 2) {
            $jobs[1]['start_index'] = 2;
            $jobs[1]['end_index'] = 3;

            $jobs[0]['start_index'] = 0;
            $jobs[0]['end_index'] = 1;

            return array($jobs, $break);
        }

        $jobs[0]['start_index'] = 0;
        $jobs[0]['end_index'] = 3;

        return array($jobs, $break);
    }

    private function getWorkingHours($agent)
    {
        // Search for a weekly planning
        // in the week date range.
        foreach ($this->dates as $date) {
            $workingHours = $agent->getWorkingHoursOn($date);
            if (!empty($workingHours)) {
                return $workingHours;
            }
        }

        return array();
    }

    private function extractTimes($times, $type)
    {
        foreach ($times as $t) {
            $agent_id = $t->agent_id();
            if (!isset($this->planning_agents[$agent_id])) {
                $this->planning_agents[$agent_id] = array();
            }

            if (!isset($this->planning_agents[$agent_id][$this->current_day])) {
                $this->planning_agents[$agent_id][$this->current_day] = array();
                $this->planning_agents[$agent_id][$this->current_day]['type'] = $type;
                $this->planning_agents[$agent_id][$this->current_day]['times'] = array();
            }

            $this->planning_agents[$agent_id][$this->current_day]['times'][] = $t;
        }
    }

    private function dayIndexFor($date)
    {
        $date_pl = new \datePl($date);
        $day_index = $date_pl->position -1;

        return $day_index;
    }

    private function addDay(&$target, $day, $index)
    {

        $target[$index] = $day;

        if ($this->config('nb_semaine') > 1) {
            $target[$index + 7] = $day;
        }

        if ($this->config('nb_semaine') > 2) {
            $target[$index + 14] = $day;
        }
    }

    private function getRelatedNormalJob($job)
    {
        foreach ($this->config('statedweek_times_job') as $normal_job) {
            if ($normal_job['name'] != $job->name()) {
                continue;
            }

            if (isset($normal_job['related_to']) && $normal_job['related_to']) {
                return $normal_job['related_to'];
            }
        }

        return 0;
    }

    private function getNormalPlanning($date)
    {
        $site = $this->config('statedweek_site_filter') ?? 1;

        $db = new \db();
        $db->select2('pl_poste_tab_affect', null, array('date' => $date, 'site' => $site));

        $normal_plannings = $db->result;
        if (is_array($normal_plannings)) {
            return $normal_plannings[0];
        }
        return $normal_plannings;
    }
}
