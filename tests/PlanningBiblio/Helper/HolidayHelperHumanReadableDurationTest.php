<?php

use App\PlanningBiblio\Helper\HolidayHelper;
use PHPUnit\Framework\TestCase;

class HolidayHelperHumanReadableDurationTest extends TestCase
{
    public function testHumanReadableDuration() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';

        $holiday_helper = new HolidayHelper();

        // Days.
        $this->assertEquals('1 jour', $holiday_helper->HumanReadableDuration(7));
        $this->assertEquals('2 jours', $holiday_helper->HumanReadableDuration(14));
        $this->assertEquals('2.5 jours', $holiday_helper->HumanReadableDuration(17.5));
        $this->assertEquals('2.5 jours', $holiday_helper->HumanReadableDuration(19));
        $this->assertEquals('0 jour', $holiday_helper->HumanReadableDuration(''));

        // Hours.
        $this->assertEquals('0h45', $holiday_helper->HumanReadableDuration(0.75, 'heures'));
        $this->assertEquals('1h45', $holiday_helper->HumanReadableDuration(1.75, 'heures'));
        $this->assertEquals('2h00', $holiday_helper->HumanReadableDuration(2, 'heures'));
        $this->assertEquals('0h00', $holiday_helper->HumanReadableDuration(0, 'heures'));
        $this->assertEquals('0h00', $holiday_helper->HumanReadableDuration(0.00, 'heures'));
        $this->assertEquals('0h00', $holiday_helper->HumanReadableDuration('', 'heures'));

        // Negatives hours.
        $this->assertEquals('-0h45', $holiday_helper->HumanReadableDuration(-0.75, 'heures'));
        $this->assertEquals('-1h45', $holiday_helper->HumanReadableDuration(-1.75, 'heures'));
        $this->assertEquals('-2h00', $holiday_helper->HumanReadableDuration(-2, 'heures'));
    }
}