<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;
use App\PlanningBiblio\Helper\WeekPlanningHelper;
use App\PlanningBiblio\WorkingHours;
use App\Model\Agent;

include_once(__DIR__ . '/../../../public/joursFeries/class.joursFeries.php');
include_once __DIR__ . '/../../../public/planningHebdo/class.planningHebdo.php';
include_once(__diR__ . '/../../../public/include/function.php');

class HolidayHelper extends BaseHelper
{
    public $data;

    private $error = false;

    public function __construct($data = null)
    {
        if ($data) {
            $this->data = $data;
        }
        parent::__construct();
    }

    public function HumanReadableDuration($hours, $force = null)
    {
        if ($hours == '' || $hours == '0.00') {
          $hours = 0;
        }

        $mode = $this->config('Conges-Mode');
        if ($force && ($force == 'heures' || $force == 'jours')) {
            $mode = $force;
        }

        if ($mode == 'heures') {
            return heure4($hours, true);
        }

        // TODO: should be altered by a plugin.
        $days = $hours / 7;
        $days = round($days * 2) / 2;

        // Handle plurals
        if ($days > 1) {
            return "$days jours";
        }

        return "$days jour";
    }

    public function getCountedHours()
    {
        $debut = $this->data['start'];
        $hre_debut = $this->data['hour_start'];
        $fin = $this->data['end'];
        $hre_fin = $this->data['hour_end'];
        $perso_id = $this->data['perso_id'];

        // Calcul du nombre d'heures correspondant aux congés demandés
        $current = $debut;
        $result = array(
            'error'     => false,
            'minutes'   => 0,
            'hours'     => 0,
            'hr_hours'  => '0h00'
        );

        $per_week = array();
        // For each requested date.
        while ($current <= $fin) {

            $date_current = new \DateTime($current);
            $week_id = $date_current->format("W");

            // Check agent's planning.
            $planning = $this->getPlanning($current);
            if ($this->error) {
                $result['error'] = true;
                return $result;
            }

            $week_helper = new WeekPlanningHelper($planning['times']);
            $per_week[$week_id]['worked_days'] = $week_helper->NumberWorkingDays();

            if (!isset($per_week[$week_id]['requested_days'])) {
                $per_week[$week_id]['requested_days'] = 0;
                $per_week[$week_id]['times'] = 0;
            }

            if ($week_helper->isWorkingDay($date_current)) {
                $per_week[$week_id]['requested_days']++;
            }

            // We ignore closing day
            if ($this->isClosingDay($current)) {
                if ($week_helper->isWorkingDay($date_current)) {
                    $per_week[$week_id]['requested_days']--;
                }
                $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
                continue;
            }


            $debutConges = $current == $debut ? $hre_debut : "00:00:00";
            $finConges = $current == $fin ? $hre_fin : "23:59:59";
            $debutConges = strtotime($debutConges);
            $finConges = strtotime($finConges);

            $times = $this->getTimes($planning, $current);

            $today = 0;
            foreach ($times as $t) {
                $t0 = strtotime($t[0]);
                $t1 = strtotime($t[1]);

                $debutConges1 = $debutConges > $t0 ? $debutConges : $t0;
                $finConges1 = $finConges < $t1 ? $finConges : $t1;
                if ($finConges1 > $debutConges1) {
                    $today += $finConges1 - $debutConges1;
                }
            }

            if($today > 0 && $this->config('Conges-Mode') == 'jours' && !$this->data['is_recover']) {
                // 14400 = 4h, 12600 = 3,5h, 25200 = 7h
                $today = $today <= 14400 ? 12600 : 25200;
            }

            $per_week[$week_id]['times'] += number_format($today / 3600, 2, '.', '');

            $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
        }

        $total = 0;
        foreach ($per_week as $week) {
            $counted = $this->applyWeekTable($week);

            if ($counted > 0) {
                $total += $counted;
                continue;
            }

            $total += $week['times'];
        }

        $total = number_format($total, 2, '.', '');
        $hours_minutes = explode('.', $total);
        $result['hours'] = $hours_minutes[0];
        $result['minutes'] = isset($hours_minutes[1]) ? $hours_minutes[1] : 0;
        $result['hr_hours'] = heure4($total); // 2.5 => 2h30
        $result['days'] = round($total / 7, 2);

        return $result;
    }

    private function applyWeekTable($week)
    {
        if($this->config('Conges-Mode') == 'heures' || $this->data['is_recover']) {
            return 0;
        }

        // Staff member didn't request a full week.
        if ($week['requested_days'] < $week['worked_days']) {
            return 0;
        }

        $perso_id = $this->data['perso_id'];
        $agent = $this->entityManager->find(Agent::class, $perso_id);
        $annual_hours = $agent->conges_annuel() / 7;
        $counting_chart = $this->config('holiday_counting_chart');

        if (empty($counting_chart)) {
            return 0;
        }

        foreach ($counting_chart as $range => $hours) {
            list($from, $to) = explode('.', $range);

            if ( $annual_hours >= $from && $annual_hours <= $to ) {
                return $hours;
            }
        }

        return 0;
    }

    private function getTimes($planning, $date)
    {
        // Sinon, on calcule les heures d'absence
        $d = new \datePl($date);
        $week = $d->semaine3;
        $day = $d->position ? $d->position : 7;
        $day = $day + (($week - 1) * 7) - 1;


        if ($this->config('PlanningHebdo-PauseLibre')) {
            $wh = new WorkingHours($planning['times'], $planning['breaktimes']);
        } else {
            $wh = new WorkingHours($planning['times']);
        }
        return $wh->hoursOf($day);
    }

    private function getPlanning($date)
    {
         // On consulte le planning de présence de l'agent
         $p =new \planningHebdo();
         $p->perso_id = $this->data['perso_id'];
         $p->debut = $date;
         $p->fin = $date;
         $p->valide = true;
         $p->fetch();
         // Si le planning n'est pas validé pour l'une des dates, on affiche un message d'erreur et on arrête le calcul
         if (empty($p->elements)) {
            $this->error = true;
            $this->message = "Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
            return;
         }
         $times = $p->elements[0]['temps'];
         $breaktimes = $p->elements[0]['breaktime'];

         return array('times' => $times, 'breaktimes' => $breaktimes);
    }

    private function isClosingDay($date)
    {
        $j = new \joursFeries();
        $j->fetchByDate($date);
        if (!empty($j->elements)) {
            foreach ($j->elements as $elem) {
                if ($elem['fermeture']) {
                    return true;
                }
            }
        }

        return false;
    }
}