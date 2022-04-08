<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;

class HourHelper extends BaseHelper
{
    private static $start_default = '00:00:00';

    private static $end_default = '23:59:59';

    public static function StartEndFromRequest($request)
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

        if (!$end) {
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

        if (!\DateTime::createFromFormat('H:i', $time)) {
            return $time;
        }

        if (!\DateTime::createFromFormat('H:i:s', $time)) {
            $time_dt = \DateTime::createFromFormat('H:i', $time);
            $time = $time_dt->format('H:i:s');
        }

        return $time;
    }
}
