<?php

namespace PlanningBiblio\Traits;

Trait HolidayTest {

    public function halfday($data)
    {
        if ($this->config('Conges-demi-journees')) {
            $data .= ' Conges-demi-journees';
        }

        return $data;
    }
}
