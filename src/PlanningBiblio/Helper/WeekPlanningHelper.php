<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;
use App\PlanningBiblio\Helper\DayPlanningHelper;

 include_once(__DIR__ . '/../../../public/include/function.php');

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

    public static function emptyPlanning()
    {
        $config = $GLOBALS['config'];
        $day = DayPlanningHelper::emptyDay();
        $week = array();

        $days_of_week = 6;
        if (!$config['Dimanche']) {
            $days_of_week = 5;
        }

        foreach (range(0, $days_of_week) as $index) {
            if ($config['nb_semaine'] == 3) {
                $week[$index] = $day;
                $week[$index + 7] = $day;
                $week[$index + 14] = $day;
                continue;
            }

            if ($config['nb_semaine'] == 2) {
                $week[$index] = $day;
                $week[$index + 7] = $day;
                continue;
            }

            $week[] = $day;
        }

        return $week;
    }

    public static function emptyBreaktimes()
    {
        $config = $GLOBALS['config'];
        $week_breaktimes = array();

        $days_of_week = 6;
        if (!$config['Dimanche']) {
            $days_of_week = 5;
        }

        foreach (range(0, $days_of_week) as $index) {
            if ($config['nb_semaine'] == 3) {
                $week_breaktimes[$index] = 0;
                $week_breaktimes[$index + 7] = 0;
                $week_breaktimes[$index + 14] = 0;
                continue;
            }

            if ($config['nb_semaine'] == 2) {
                $week_breaktimes[$index] = 0;
                $week_breaktimes[$index + 7] = 0;
                continue;
            }

            $week_breaktimes[] = $day;
        }

        return $week_breaktimes;
    }
}