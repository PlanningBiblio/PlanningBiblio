<?php

use PHPUnit\Framework\TestCase;

use App\PlanningBiblio\Helper\HourHelper;

class HourHelperTest extends TestCase
{
    public function testtoHis()
    {
        $hh = new HourHelper();

        $result = $hh->toHis('2:30');
        $this->assertEquals('02:30:00', $result, '2:30 is transformed to 02:30:00');

        $result = $hh->toHis('02:30');
        $this->assertEquals('02:30:00', $result, '02:30 is transformed to 02:30:00');

        $result = $hh->toHis('');
        $this->assertEquals('', $result, 'empty return empty ');

        $result = $hh->toHis('10:30:00');
        $this->assertEquals('10:30:00', $result, '10:30:00is transformed to 10:30:00');

        $result = $hh->toHis('I am not a valid time');
        $this->assertEquals('', $result, 'I am not a valid time return empty');
        
        $this->expectException('ArgumentCountError');
        $result = $hh->toHis();

        $result = $hh->toHis('3');
        $this->assertEquals('', $result, '3 return empty ');
    }
}