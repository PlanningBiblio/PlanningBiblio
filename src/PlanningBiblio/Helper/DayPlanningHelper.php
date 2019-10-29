<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;

class DayPlanningHelper extends BaseHelper
{
    private $day_planning;

    public function __construct($day_planning = array())
    {
        if (!empty($day_planning)) {
            $this->day_planning = $day_planning;
        }
        parent::__construct();
    }

    public function IsWorked()
    {
        // At least 2 values should be
        // given for this to be a range
        if ($this->day_planning[0] && $this->day_planning[1]) {
            return true;
        }

        if ($this->day_planning[2] && $this->day_planning[3]) {
            return true;
        }

        if ($this->day_planning[0] && $this->day_planning[3]) {
            return true;
        }

        return false;
    }
}
