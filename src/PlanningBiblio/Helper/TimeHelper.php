<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;

class TimeHelper extends BaseHelper
{
    private $seconds;

    /*
     * time is H:i:s format
     */
    public function __construct($time = '00:00:00')
    {
        $this->seconds = $this->toSeconds($time);
    }

    public function add($time = '00:00:00')
    {
        $this->seconds += $this->toSeconds($time);
    }

    public function getTime()
    {
        return $this->toTime($this->seconds);
    }

    public function getDecimal()
    {
        return $this->seconds / 3600;
    }

    private function toTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        if ($hours < 10) {
            $hours = "0$hours";
        }

        if ($minutes < 10) {
            $minutes = "0$minutes";
        }

        if ($seconds < 10) {
            $seconds = "0$seconds";
        }

        return $hours . ':' . $minutes . ':' . $seconds;
    }

    private function toSeconds($time)
    {
        $parts = explode(':', $time);

        return 3600 * $parts[0] + 60 * $parts[1] + $parts[2];
    }
}