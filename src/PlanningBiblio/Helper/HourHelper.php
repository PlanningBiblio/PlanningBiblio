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

        if (!\DateTime::createFromFormat('H:i:s', $start)) {
            $start_dt = \DateTime::createFromFormat('H:i', $start);
            $start = $start_dt->format('H:i:s');
        }

        if (!\DateTime::createFromFormat('H:i:s', $end)) {
            $end_dt = \DateTime::createFromFormat('H:i', $end);
            $end = $end_dt->format('H:i:s');
        }

        return array($start, $end);
    }
}
