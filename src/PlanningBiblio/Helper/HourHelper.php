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
