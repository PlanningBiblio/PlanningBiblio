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

        $result = $hh->toHis('Site number 3');
        $this->assertEquals('Site number 3', $result, 'Site number 3 return Site number 3');

        $result = $hh->toHis('3');
        $this->assertEquals('3', $result, '3 return 3');

        $this->expectException('ArgumentCountError');
        $result = $hh->toHis();
    }
}