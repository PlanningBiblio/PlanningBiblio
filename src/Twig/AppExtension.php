<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

include_once(__DIR__ . '/../../public/include/function.php');
include_once(__DIR__ . '/../../public/include/feries.php');

class AppExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('hour', [$this, 'formatHour']),
            new TwigFilter('fulldate', [$this, 'formatFullDate']),
            new TwigFilter('datehour', [$this, 'formatDateHour']),
            new TwigFilter('publicholiday', [$this, 'formatPublicHoliday']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('Config', [$this, 'getConfig']),
        ];
    }

    /**
     * formatHour($hour)
     * 08:00:00 => 8h
     * 11:30:00 => 11h30
     **/
    public function formatHour($hour)
    {
        return heure3($hour);
    }

    /**
     * formatFullDate($date)
     * 2019-07-08 => Lundi 8 juillet 2019
     **/
    public function formatFullDate($date)
    {
        return dateAlpha($date);
    }

    /**
     * formatDateHour($date)
     * 2019-11-20 08:30:00 => 20/11/2019 8h30
     * 2019-11-20 00:00:00 => 20/11/2019
     * 2019-11-20 23:59:59 => 20/11/2019
     **/
    public function formatDateHour($date)
    {
        list($date, $hour) = explode(' ', $date);
        $dt = new \DateTime($date);
        $date = $dt->format('d/m/Y');

        if ($hour == '00:00:00' || $hour == '23:59:59') {
            return $date;
        }

        $hour = heure3($hour);

        return "$date $hour";
    }

    /**
     * formatPublicHoliday($date)
     * 2019-11-11 => Armistice
     **/
    public function formatPublicHoliday($date)
    {
        return jour_ferie($date);
    }

    public function getConfig($key)
    {
        $config = $GLOBALS['config'];

        if ( !isset($key) ) {
            return null;
        }

        if ( !isset($config[$key]) ) {
            return null;
        }

        return $config[$key];
    }
}
