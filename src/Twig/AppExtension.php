<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

include_once(__DIR__ . '/../../public/include/function.php');

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('hour', [$this, 'formatHour'])
        ];
    }

    public function formatHour($hour)
    {
        return heure3($hour);
    }
}
