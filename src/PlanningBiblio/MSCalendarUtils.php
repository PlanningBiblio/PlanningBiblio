<?php

namespace App\PlanningBiblio;

use App\Model\Agent;
use App\PlanningBiblio\OAuth;
use App\PlanningBiblio\Logger;
use App\PlanningBiblio\CalendarUtils;
use Unirest\Request;

class MSCalendarUtils
{
    // Returns an RRule ICS string based on an MS Graph event recurrence object
    public function recurrenceToRRule($recurrence) {
        // See, for reference:
        // https://docs.microsoft.com/fr-fr/graph/api/resources/recurrencepattern?view=graph-rest-1.0
        // https://www.kanzaki.com/docs/ical/rrule.html
        // https://www.textmagic.com/free-tools/rrule-generator
        $rrule = '';
        //var_dump($recurrence);
        switch ($recurrence->pattern->type) {
            case "daily":
                $rrule = 'FREQ=DAILY';
                $rrule .= ';INTERVAL=' . $recurrence->pattern->interval;
                break;

            case "weekly":
                $rrule = 'FREQ=WEEKLY';
                $rrule .= ';INTERVAL=' . $recurrence->pattern->interval;
                $rrule .= ";WKST=" . $this->convertWeekDay($recurrence->pattern->firstDayOfWeek);
                $rrule .= ";BYDAY=" . implode(",", array_map(array($this, 'convertWeekDay'), $recurrence->pattern->daysOfWeek));
                break;

            case "absoluteMonthly":
                $rrule .= 'FREQ=MONTHLY';
                $rrule .= ';INTERVAL=' . $recurrence->pattern->interval;
                $rrule .= ';BYMONTHDAY=' . $recurrence->pattern->dayOfMonth;
                break;

            case "relativeMonthly":
                $rrule .= 'FREQ=MONTHLY';
                $rrule .= ';INTERVAL=' . $recurrence->pattern->interval;
                $rrule .= ';BYDAY=' . $this->convertPosition($recurrence->pattern->index) . $this->convertWeekDay($recurrence->pattern->daysOfWeek[0]);
                break;

            case "absoluteYearly":
                $rrule .= 'FREQ=YEARLY';
                $rrule .= ';INTERVAL=' . $recurrence->pattern->interval;
                $rrule .= ';BYMONTH=' . $recurrence->pattern->month;
                $rrule .= ';BYMONTHDAY=' . $recurrence->pattern->dayOfMonth;
                break;

            case "relativeYearly":
                $rrule .= 'FREQ=YEARLY';
                $rrule .= ';INTERVAL=' . $recurrence->pattern->interval;
                $rrule .= ';BYDAY=' . $this->convertWeekDay($recurrence->pattern->daysOfWeek[0]);
                $rrule .= ';BYSETPOS=' . $this->convertPosition($recurrence->pattern->index); 
                $rrule .= ';BYMONTH=' . $recurrence->pattern->month;
                break;
        }
        $rrule .= $this->returnEnd($recurrence);
        //echo $rrule . "\n";
        return $rrule;
    }

    private function returnStart($recurrence) {
        $start = '';
        if ($recurrence->range->startDate) {
            $start = 'DTSTART;TZID=' . $this->convertTimeZone($recurrence->range->recurrenceTimeZone) . ':' . $this->convertStartDate($recurrence->range->startDate);
        }
        return $start;
    }

    private function returnEnd($recurrence) {
        $rrule = '';
        if ($recurrence->range->type == "endDate") {
            $rrule = ";UNTIL=" . $this->convertUntilDate($recurrence->range->endDate);
        } elseif ($recurrence->range->type == "numbered") {
            // Can be returned by the API, but not used in web-based Outlook?
            $rrule = ";COUNT=" . $recurrence->range->numberOfOccurrences;
        }
        return $rrule;
    }

    // Returns MO for monday and so on.
    private function convertWeekDay($weekday) {
        return (substr(strtoupper($weekday), 0, 2));
    }

    private function convertStartDate($date) {
        return (str_replace('-', '', $date) . 'T000000');
    }

    private function convertEndDate($date) {
        return (str_replace('-', '', $date) . 'T235959');
    }

    private function convertUntilDate($date) {
        return (str_replace('-', '', $date) . 'T215959Z');
    }

    private function convertPosition($position) {
        switch ($position) {
            case "first": return 1; break;
            case "second": return 2; break;
            case "third": return 3; break;
            case "fourth": return 4; break;
            case "last": return -1; break;
        }
    }

    private function convertTimeZone($timezone) {
        // This needs to be completed if other timezones are used
        switch ($timezone) {
            case "Romance Standard Time": 
                return "Europe/Paris";
                //return "Europe/Berlin";
                break;
        } 
    }
}
