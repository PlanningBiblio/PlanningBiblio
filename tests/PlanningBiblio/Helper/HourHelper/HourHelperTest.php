<?php

use PHPUnit\Framework\TestCase;

use App\PlanningBiblio\Helper\HourHelper;

class HourHelperTest extends TestCase
{


    public function testDecimalToHoursMinutes()
    {
        $hh = new HourHelper();

        # Note:
        # We use the same constant as select_holiday_minutes.html.twig for conversion
        # 0.966666667 = 58 minutes (58 * 0.016666666666667)
        # 0.983333333 = 59 minutes (59 * 0.016666666666667)

        # A value right on a minute stays on this minute
        $result = $hh->decimalToHoursMinutes(8.966666667);
        $this->assertEquals(8, $result['hours'], '8.966666667 gives 8 hours');
        $this->assertEquals(58, $result['minutes'], '8.966666667 gives 58 minutes (a value exactly on a minute stays on this minute)');

        # A value right on a minute stays on this minute
        $result = $hh->decimalToHoursMinutes(8.983333333);
        $this->assertEquals(8, $result['hours'], '8.983333333 gives 8 hours');
        $this->assertEquals(59, $result['minutes'], '8.983333333 gives 59 minutes (a value exactly on a minute stays on this minute)');

        # 0 gives 0
        $result = $hh->decimalToHoursMinutes(0);
        $this->assertEquals(0, $result['hours'], '0 gives 0 hours');
        $this->assertEquals(0, $result['minutes'], '0 gives 0 minutes');

        # In case we have values inbetween minutes in the database:
        # A value betweeen minutes goes to the closest minute
        # Between 8h59 and 9h
        $result = $hh->decimalToHoursMinutes(8.9967);
        $this->assertEquals(9, $result['hours'], '8.9967 gives 9 hours (a value betweeen minutes goes to the closest minute, positive test)');
        $this->assertEquals(00, $result['minutes'], '8.9967 gives 00 minutes (a value between minutes goes to the closest minute, positive test)');

        # Between -8h59 and -9h
        $result = $hh->decimalToHoursMinutes(-8.9967);
        $this->assertEquals(-9, $result['hours'], '-8.9967 gives -9 hours (a value betweeen minutes goes to the closest minute, negative test)');
        $this->assertEquals(00, $result['minutes'], '-8.9967 gives 00 minutes (a value between minutes goes to the closest minute, negative test)');

        # Between 8h00 and 8h01, closer to 01
        $result = $hh->decimalToHoursMinutes(8.015);
        $this->assertEquals(8, $result['hours'], '8.015 gives 8 hours (a value betweeen minutes goes to the closest minute, closer to the next minute)');
        $this->assertEquals(01, $result['minutes'], '8.015 gives 01 minutes (a value between minutes goes to the closest minute, closer to the next minute)');

        # Between 8h00 and 8h01, closer to 00
        $result = $hh->decimalToHoursMinutes(8.0015);
        $this->assertEquals(8, $result['hours'], '8.0015 gives 8 hours (a value betweeen minutes goes to the closest minute, closer to the previous minute)');
        $this->assertEquals(0, $result['minutes'], '8.0015 gives 00 minutes (a value between minutes goes to the closest minute, closer to the previous minute)');
    }

    public function testHourMinutesToDecimal()
    {
        $hh = new HourHelper();

        $result = $hh->hoursMinutesToDecimal(8, 59);
        $this->assertEquals(8.983333333, $result, '8h59 gives 8.983333333');

        $result = $hh->hoursMinutesToDecimal(-8, 59);
        $this->assertEquals(-8.983333333, $result, '8h59 gives -8.983333333');

        $result = $hh->hoursMinutesToDecimal(0, 0);
        $this->assertEquals(0, $result, '0 hours 0 minutes gives 0');
    }

    public function testHoursMinutesToDecimalAndDecimalToHoursMinutesBijection()
    {
        $hh = new HourHelper();

        # For every minute of an hour, we test that we get the same result going from hours minutes to decimal, and vice-versa.
        for ($i = 0; $i < 60; $i++) {
            $result = $hh->decimalToHoursMinutes($hh->hoursMinutesToDecimal(8, $i));
            $this->assertEquals(8, $result['hours'], 'Hours and minutes to decimal to hours and minutes give the same input result (hours)');
            $this->assertEquals($i, $result['minutes'], "Hours and minutes to decimal to hours and minutes give the same input result (minutes = $i)");
        }
    }

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
