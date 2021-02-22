<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;
use App\PlanningBiblio\Helper\DayPlanningHelper;
use App\PlanningBiblio\WorkingHours;

class WeekPlanningHelper extends BaseHelper
{
    private $week_planning;

    public function __construct($week_planning = array())
    {
        if (!empty($week_planning)) {
            $this->week_planning = $week_planning;
        }
        parent::__construct();
    }

    public function NumberWorkingDays()
    {
        $workings_days = 0;
        foreach ($this->week_planning as $day) {
            $day = new DayPlanningHelper($day);

            if ($day->IsWorked()) {
                $workings_days++;
            }
        }

        return $workings_days;
    }

    public function isWorkingDay(\DateTime $date) {
        $day_index = $date->format('N') - 1;

        if (!isset($this->week_planning[$day_index])) {
            return false;
        }

        $day_planning = $this->week_planning[$day_index];
        $day = new DayPlanningHelper($day_planning);

        if ($day->IsWorked()) {
            return true;
        }

        return false;
    }

    public function getTimes($date, $agent = null, $planning = null )
    {
        if (!$planning) {
            $planning = $this->getPlanning($date, $agent);
        }

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

    public function getPlanning($date, $agent)
    {
         $p =new \planningHebdo();
         $p->perso_id = $agent;
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
}
