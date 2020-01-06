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
        foreach (array(0, 1, 2, 3, 4, 5, 6) as $index) {
            $week[] = $day;
        }

        if (!$config['Dimanche']) {
            unset($week[6]);
        }

        return $week;
    }
}