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
            new TwigFilter('publicholiday', [$this, 'formatPublicHoliday']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('Config', [$this, 'getConfig']),
        ];
    }

    public function formatHour($hour)
    {
        return heure3($hour);
    }

    public function formatFullDate($date)
    {
        return dateAlpha($date);
    }

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
