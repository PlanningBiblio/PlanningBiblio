<?php

namespace PlanningBiblio;

class LegacyCodeChecker {
    private $twigized = array(
    );

    public function isTwigized($page) {
        if (in_array($page, $this->twigized)) {
            return true;
        }

        return false;
    }
}

?>