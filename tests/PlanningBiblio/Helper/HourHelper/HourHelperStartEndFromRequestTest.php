<?php

use Symfony\Component\HttpFoundation\Request;
use PHPUnit\Framework\TestCase;

use App\PlanningBiblio\Helper\HourHelper;

class HourHelperStartEndFromRequestTest extends TestCase
{
    public function testEmptyHours()
    {
        $request = new Request(array('hre_debut' => '', 'hre_fin' => '', 'allday' => ''));

        list($start, $end) = HourHelper::StartEndFromRequest($request);

        $this->assertEquals('00:00:00', $start);
        $this->assertEquals('23:59:59', $end);
    }

    public function testEmptyEndHour()
    {
        $request = new Request(array('hre_debut' => '10:30:00', 'hre_fin' => '', 'allday' => ''));

        list($start, $end) = HourHelper::StartEndFromRequest($request);

        $this->assertEquals('10:30:00', $start);
        $this->assertEquals('23:59:59', $end);
    }

    public function testEmptyStartHour()
    {
        $request = new Request(array('hre_debut' => '', 'hre_fin' => '15:30:00', 'allday' => ''));

        list($start, $end) = HourHelper::StartEndFromRequest($request);

        $this->assertEquals('00:00:00', $start);
        $this->assertEquals('15:30:00', $end);
    }

    public function testAllday()
    {
        $request = new Request(array('hre_debut' => '09:00:00', 'hre_fin' => '15:00:00', 'allday' => 'on'));

        list($start, $end) = HourHelper::StartEndFromRequest($request);

        $this->assertEquals('00:00:00', $start);
        $this->assertEquals('23:59:59', $end);
    }

    public function testHiHour()
    {
        $request = new Request(array('hre_debut' => '09:30', 'hre_fin' => '15:00', 'allday' => ''));

        list($start, $end) = HourHelper::StartEndFromRequest($request);

        $this->assertEquals('09:30:00', $start);
        $this->assertEquals('15:00:00', $end);
    }
}
