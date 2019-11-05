<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;
use App\PlanningBiblio\Helper\DayPlanningHelper;

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

        $day_planning = $this->week_planning[$day_index];
        $day = new DayPlanningHelper($day_planning);

        if ($day->IsWorked()) {
            return true;
        }

        return false;
    }
}