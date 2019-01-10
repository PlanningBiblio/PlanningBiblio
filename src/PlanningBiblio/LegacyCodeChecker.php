<?php

namespace PlanningBiblio;

class LegacyCodeChecker
{
    private $twigized = array(
        'absences/infos.php',
        'admin/config.php',
    );

    public function isTwigized($page)
    {
        if (in_array($page, $this->twigized)) {
            return true;
        }

        return false;
    }
}
