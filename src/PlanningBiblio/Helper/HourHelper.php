<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;

class HourHelper extends BaseHelper
{
    private static $start_default = '00:00:00';

    private static $end_default = '23:59:59';

    public static function decimalToHoursMinutes($decimal_duration): array
    {
        $result = array();

        $negative = false;
        if ($decimal_duration < 0) {
            $negative = true;
            $decimal_duration = abs($decimal_duration);
        }
        $result['hours'] = (int) floor($decimal_duration);
        if ($negative) {
            $result['hours'] = 0 - $result['hours'];
        }

        # Considering minutes only from now:
        $decimal_duration -= floor($decimal_duration);

        $result['minutes'] = round($decimal_duration * 60);

        if ($result['minutes'] == 60) {
            if ($negative) {
                $result['hours'] -= 1;
            } else {
                $result['hours'] += 1;
            }
            $result['minutes'] = 0;
        }

        if ($result['hours'] == 0 && $negative) {
            $result['hours'] = '-0';
        } else {
            $result['hours'] = (string) $result['hours'];
        }

        if ($result['hours'] == 0 && $result['minutes'] == 0) {
            $result['as_string'] = '';
        } else {
            $result['as_string'] = $result['hours'] . 'h' . str_pad($result['minutes'], 2, '0', STR_PAD_LEFT);
        }

        return $result;
    }

    public static function hoursMinutesToDecimal(string $hours, int $minutes): string
    {

        if (!is_int($minutes) || $minutes < 0 || $minutes > 59) {
            throw new \InvalidArgumentException('hoursMinutesToDecimal only accepts integers between 0 and 59 included for minutes. Input was: ' . $minutes);
        }

        $negative = false;
        if (strstr($hours, '-')) {
            $negative = true;
            $hours = abs($hours);
        }

        $result = $hours + round($minutes / 60, 9);

        if ($negative) {
            $result = 0 - $result;
        }

        # Working with 9 decimals is enough for minute precision
        return sprintf("%.9f", $result);
    }

    public static function StartEndFromRequest($request): array
    {
        $start = $request->get('hre_debut');
        $end = $request->get('hre_fin');
        $allday = $request->get('allday');

        if ($allday) {
            list($start, $end) = array('', '');
        }

        if (!$start) {
            $start = self::$start_default;
        }

        if (!$end || $end == '23:59') {
            $end = self::$end_default;
        }

        $start = self::toHis($start);
        $end = self::toHis($end);

        return array($start, $end);
    }

    public static function toHis($time)
    {
        if (!$time) {
            return '';
        }

        // $time is already H:i:s formated.
        // Return it.
        if(\DateTime::createFromFormat('H:i:s', $time)){
            return $time;
        }

        // As we sometimes loop on working hours array,
        // and so pass a non time value (i.e site's number,
        // see route [POST] /workinghour) we need to keep,
        // we don't return empty but the incoming one.
        if (!\DateTime::createFromFormat('H:i', $time)) {
            return $time;
        }

        // We we are sure the incoming value is a H:i formated one
        // that we can transform.
        if (!\DateTime::createFromFormat('H:i:s', $time)) {
            $time_dt = \DateTime::createFromFormat('H:i', $time);
            $time = $time_dt->format('H:i:s');
        }

        return $time;
    }
}
