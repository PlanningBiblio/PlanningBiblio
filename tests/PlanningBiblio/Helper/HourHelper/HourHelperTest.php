<?php

use PHPUnit\Framework\TestCase;

use App\PlanningBiblio\Helper\HourHelper;

class HourHelperTest extends TestCase
{


    public function testDecimalToHoursMinutes(): void
    {
        $hh = new HourHelper();

        # Note:
        # We use the same constant as select_holiday_minutes.html.twig for conversion
        # 0.966666667 = 58 minutes (58 * 0.016666666666667)
        # 0.983333333 = 59 minutes (59 * 0.016666666666667)

        # A value right on a minute stays on this minute
        $result = $hh->decimalToHoursMinutes(8.966666667);
        $this->assertSame('8', $result['hours'], '8.966666667 gives 8 hours');
        $this->assertEquals(58, $result['minutes'], '8.966666667 gives 58 minutes (a value exactly on a minute stays on this minute)');

        # A value right on a minute stays on this minute
        $result = $hh->decimalToHoursMinutes(8.983333333);
        $this->assertSame('8', $result['hours'], '8.983333333 gives 8 hours');
        $this->assertEquals(59, $result['minutes'], '8.983333333 gives 59 minutes (a value exactly on a minute stays on this minute)');

        # 0 gives 0
        $result = $hh->decimalToHoursMinutes(0);
        $this->assertSame('0', $result['hours'], '0 gives 0 hours');
        $this->assertEquals(0, $result['minutes'], '0 gives 0 minutes');
        $this->assertEquals('', $result['as_string'], '0 hours 0 minutes gives an empty string');

        # Negative duration within an hour gives a negative hour value
        $result = $hh->decimalToHoursMinutes(-0.25);
        $this->assertSame('-0', $result['hours'], '-0.25 gives -0 hours');
        $this->assertEquals(15, $result['minutes'], '-0.25 gives 15 minutes');
        $this->assertEquals('-0h15', $result['as_string'], 'minus sign within an hour is kept in string representation');

        # In case we have values inbetween minutes in the database:
        # A value betweeen minutes goes to the closest minute
        # Between 8h59 and 9h
        $result = $hh->decimalToHoursMinutes(8.9967);
        $this->assertSame('9', $result['hours'], '8.9967 gives 9 hours (a value betweeen minutes goes to the closest minute, positive test)');
        $this->assertEquals(00, $result['minutes'], '8.9967 gives 00 minutes (a value between minutes goes to the closest minute, positive test)');
        $this->assertEquals('9h00', $result['as_string'], '0 minutes are shown as 00 in string representation');

        # Between -8h59 and -9h
        $result = $hh->decimalToHoursMinutes(-8.9967);
        $this->assertSame('-9', $result['hours'], '-8.9967 gives -9 hours (a value betweeen minutes goes to the closest minute, negative test)');
        $this->assertEquals(00, $result['minutes'], '-8.9967 gives 00 minutes (a value between minutes goes to the closest minute, negative test)');
        $this->assertEquals('-9h00', $result['as_string'], 'minus sign is kept in string representation');

        # Between 8h00 and 8h01, closer to 01
        $result = $hh->decimalToHoursMinutes(8.015);
        $this->assertSame('8', $result['hours'], '8.015 gives 8 hours (a value betweeen minutes goes to the closest minute, closer to the next minute)');
        $this->assertEquals(01, $result['minutes'], '8.015 gives 01 minutes (a value between minutes goes to the closest minute, closer to the next minute)');
        $this->assertEquals('8h01', $result['as_string'], 'minutes under 10 are zero left-padded');

        # Between 8h00 and 8h01, closer to 00
        $result = $hh->decimalToHoursMinutes(8.0015);
        $this->assertSame('8', $result['hours'], '8.0015 gives 8 hours (a value betweeen minutes goes to the closest minute, closer to the previous minute)');
        $this->assertEquals(0, $result['minutes'], '8.0015 gives 00 minutes (a value between minutes goes to the closest minute, closer to the previous minute)');

    }

    public function testHourMinutesToDecimal(): void
    {
        $hh = new HourHelper();

        $result = $hh->hoursMinutesToDecimal('8', 59);
        $this->assertEquals(8.983333333, $result, '8h59 gives 8.983333333');

        $result = $hh->hoursMinutesToDecimal('-8', 59);
        $this->assertEquals(-8.983333333, $result, '8h59 gives -8.983333333');

        $result = $hh->hoursMinutesToDecimal('0', 0);
        $this->assertEquals(0, $result, '0 hours 0 minutes gives 0');

        $result = $hh->hoursMinutesToDecimal('-0', 15);
        $this->assertEquals(-0.250000000, $result, 'Negative duration within an hour gives negative result');

        $this->expectException(TypeError::class);
        $result = $hh->hoursMinutesToDecimal('-0', 'aaa');

        $this->expectException(InvalidArgumentException::class);
        $result = $hh->hoursMinutesToDecimal('-0', -1);

        $this->expectException(InvalidArgumentException::class);
        $hh->hoursMinutesToDecimal('-0', 60);
    }

    public function testHoursMinutesToDecimalAndDecimalToHoursMinutesBijection(): void
    {
        $hh = new HourHelper();

        # For every minute of an hour, we test that we get the same result going from hours minutes to decimal, and vice-versa.
        $hours = array('-1', '-0', '0', '1');
        foreach ($hours as $hour) {
            for ($i = 0; $i < 60; $i++) {

                if ($hour === '-0' && $i == 0) {
                    # There is no point in testing -0.0, as it is stricly equal to 0.0
                    continue;
                }

                $result = $hh->decimalToHoursMinutes($hh->hoursMinutesToDecimal($hour, $i));
                $this->assertSame($hour, $result['hours'], "Hours and minutes to decimal to hours and minutes give the same input result (hour = $hour)");
                $this->assertEquals($i, $result['minutes'], "Hours and minutes to decimal to hours and minutes give the same input result (minutes = $i)");
            }
        }
    }

    public function testtoHis(): void
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
        $hh->toHis();
    }
}
