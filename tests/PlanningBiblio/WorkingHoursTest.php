<?php

use App\PlanningBiblio\WorkingHours;
use PHPUnit\Framework\TestCase;

class WorkingHoursTest extends TestCase
{
    public function testOne() {
        $working_hours = array(
            0 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            1 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            2 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            3 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            4 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            5 => array('0' => '', '1' => '', '2' => '', '3' => ''),
        );

        $GLOBALS['config']['PlanningHebdo-Pause2'] = 0;

        $wh = new WorkingHours($working_hours);

        $times = $wh->hoursOf(0);
        $this->assertEmpty($times, 'Monday is empty');

        $times = $wh->hoursOf(1);
        $this->assertEmpty($times, 'Tuesday is empty');

        $times = $wh->hoursOf(2);
        $this->assertEquals('08:00:00', $times[0][0], 'Wednesday starts at 08:00');
        $this->assertEquals('17:00:00', $times[0][1], 'Wednesday ends at 17:00');

        $times = $wh->hoursOf(3);
        $this->assertEmpty($times, 'Thursday is empty');

        $times = $wh->hoursOf(4);
        $this->assertEmpty($times, 'Friday is empty');

        $times = $wh->hoursOf(5);
        $this->assertEmpty($times, 'Saturday is empty');
    }

    public function testTwo()
    {
        $working_hours = array(
            0 => array('0' => '09:00:00', '1' => '16:00:00', '2' => '', '3' => ''),
            1 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '14:00:00', '3' => '18:00:00'),
            2 => array('0' => '', '1' => '09:00:00', '2' => '', '3' => '17:00:00'),
            3 => array('0' => '', '1' => '', '2' => '13:00:00', '3' => '20:00:00'),
            4 => array('0' => '13:00:00', '1' => '', '2' => '20:00:00', '3' => '', '5' => '', '6' => ''),
            5 => array('0' => '', '1' => '', '2' => '', '3' => ''),
        );

        $GLOBALS['config']['PlanningHebdo-Pause2'] = 0;

        $wh = new WorkingHours($working_hours);

        $times = $wh->hoursOf(0);
        $this->assertEquals('09:00:00', $times[0][0], 'Monday starts at 09:00');
        $this->assertEquals('16:00:00', $times[0][1], 'Monday ends at 16:00');
        $this->assertArrayNotHasKey(1, $times, 'Monday second part does not exist');

        $times = $wh->hoursOf(1);
        $this->assertEquals('09:00:00', $times[0][0], 'Tuesday: first part starts at 09:00');
        $this->assertEquals('12:00:00', $times[0][1], 'Tuesday: first part ends at 12:00');
        $this->assertEquals('14:00:00', $times[1][0], 'Tuesday: second part starts at 14:00');
        $this->assertEquals('18:00:00', $times[1][1], 'Tuesday: second part ends at 18:00');

        $times = $wh->hoursOf(2);
        $this->assertEmpty($times, 'Wednesday is empty');

        $times = $wh->hoursOf(3);
        $this->assertEquals('13:00:00', $times[0][0], 'Thursday first part starts at 13:00');
        $this->assertEquals('20:00:00', $times[0][1], 'Thursday first part ends at 20:00');
        $this->assertArrayNotHasKey(1, $times, 'Thursday second part does not exist');

        $times = $wh->hoursOf(4);
        $this->assertEmpty($times, 'Friday is empty');
    }

    public function testWithPause2()
    {
        $working_hours = array(
            0 => array('0' => '11:00:00', '1' => '', '2' => '', '3' => '', '5' => '19:00:00', '6' => ''),
            1 => array('0' => '', '1' => '', '2' => '06:00:00', '3' => '', '5' => '', '6' => '14:00:00'),
            2 => array('0' => '', '1' => '', '2' => '06:00:00', '3' => '', '5' => '14:00:00', '6' => ''),
            3 => array('0' => '08:00:00', '1' => '11:00:00', '2' => '12:00:00', '3' => '16:00:00', '5' => '14:00:00', '6' => '15:00:00'),
            4 => array('0' => '', '1' => '', '2' => '', '3' => '', '5' => '', '6' => ''),
            5 => array('0' => '', '1' => '', '2' => '', '3' => '', '5' => '', '6' => ''),
        );

        $GLOBALS['config']['PlanningHebdo-Pause2'] = 1;

        $wh = new WorkingHours($working_hours);

        $times = $wh->hoursOf(0);
        $this->assertEquals('11:00:00', $times[0][0], 'Monday starts at 11:00');
        $this->assertEquals('19:00:00', $times[0][1], 'Monday ends at 19:00');
        $this->assertArrayNotHasKey(1, $times, 'Monday second part does not exist');

        $times = $wh->hoursOf(1);
        $this->assertEmpty($times, 'Tuesday times are not valid');

        $times = $wh->hoursOf(2);
        $this->assertEquals('06:00:00', $times[0][0], 'Wednesday starts at 11:00');
        $this->assertEquals('14:00:00', $times[0][1], 'Wednesday ends at 19:00');
        $this->assertArrayNotHasKey(1, $times, 'Wednesday second part does not exist');

        $times = $wh->hoursOf(3);
        $this->assertEquals('08:00:00', $times[0][0], 'Thursday first part starts at 08:00');
        $this->assertEquals('11:00:00', $times[0][1], 'Thursday first part ends at 11:00');
        $this->assertEquals('12:00:00', $times[1][0], 'Thursday second part starts at 12:00');
        $this->assertEquals('14:00:00', $times[1][1], 'Thursday second part ends at 14:00');
        $this->assertEquals('15:00:00', $times[2][0], 'Thursday third part starts at 15:00');
        $this->assertEquals('16:00:00', $times[2][1], 'Thursday third part ends at 16:00');
    }

    //TODO: Check me
    public function testWithFreeBreaks()
    {
        $working_hours = array(
            0 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '16:00:00'),
            1 => array('0' => '08:00:00', '1' => '12:15:00', '2' => '', '3' => ''),
            2 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '10:00:00'),
            3 => array('0' => '08:00:00', '1' => '10:00:00', '2' => '11:00:00', '3' => '13:00:00'),
            4 => array('0' => '08:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '14:00:00'),
            5 => array('0' => '08:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '14:00:00'),
        );

        $breaktimes = array(0 => 0.75, 1 => 1.25, 2 => 2, 3 => 0.5, 4 => 1.5, 5 => 3);

        $GLOBALS['config']['PlanningHebdo-Pause2'] = 0;

        $wh = new WorkingHours($working_hours, $breaktimes);

        $times = $wh->hoursOf(0);
        $this->assertEquals('08:00:00', $times[0][0], 'Monday starts at 08:00');
        $this->assertEquals('15:15:00', $times[0][1], 'Monday ends at 15:15');

        $times = $wh->hoursOf(1);
        $this->assertEquals('08:00:00', $times[0][0], 'Tuesday starts at 08:00');
        $this->assertEquals('11:00:00', $times[0][1], 'Tuesday ends at 11:00');

        $times = $wh->hoursOf(2);
        $this->assertEmpty($times, 'Wednesday times are null');

        $times = $wh->hoursOf(3);
        $this->assertEquals('08:00:00', $times[0][0], 'Thursday first part starts at 08:00');
        $this->assertEquals('10:00:00', $times[0][1], 'Thursday first part ends at 10:00');
        $this->assertEquals('11:00:00', $times[1][0], 'Thursday second part starts at 11:00');
        $this->assertEquals('12:30:00', $times[1][1], 'Thursday second part ends at 12:30');

        $times = $wh->hoursOf(4);
        $this->assertEquals('08:00:00', $times[0][0], 'Friday first part starts at 08:00');
        $this->assertEquals('12:00:00', $times[0][1], 'Friday first part ends at 12:00');
        $this->assertArrayNotHasKey(1, $times, 'Friday second part does not exist');

        $times = $wh->hoursOf(5);
        $this->assertEquals('08:00:00', $times[0][0], 'Saturday first part starts at 08:00');
        $this->assertEquals('11:00:00', $times[0][1], 'Saturday first part ends at 11:00');
        $this->assertArrayNotHasKey(1, $times, 'Saturday second part does not exist');

        $working_hours = array(
            0 => array('0' => '08:00:00', '1' => '10:00:00', '2' => '11:00:00', '3' => '17:00:00', 5 => '12:00:00', 6 => '14:00:00'),
            1 => array('0' => '08:00:00', '1' => '10:00:00', '2' => '11:00:00', '3' => '17:00:00', 5 => '12:00:00', 6 => '14:00:00'),
            2 => array('0' => '08:00:00', '1' => '10:00:00', '2' => '11:00:00', '3' => '17:00:00', 5 => '12:00:00', 6 => '14:00:00'),
            3 => array('0' => '08:00:00', '1' => '10:00:00', '2' => '11:00:00', '3' => '14:00:00', 5 => '12:00:00', 6 => '13:00:00'),
        );

        $breaktimes = array(0 => 0.25, 1 => 3.5, 2 => 5.5, 3 => 3);

        $GLOBALS['config']['PlanningHebdo-Pause2'] = 1;

        $wh = new WorkingHours($working_hours, $breaktimes);

        $times = $wh->hoursOf(0);
        $this->assertEquals('08:00:00', $times[0][0], 'Monday first part starts at 08:00');
        $this->assertEquals('10:00:00', $times[0][1], 'Monday first part ends at 10:00');
        $this->assertEquals('11:00:00', $times[1][0], 'Monday second part starts at 11:00');
        $this->assertEquals('12:00:00', $times[1][1], 'Monday second part ends at 12:00');
        $this->assertEquals('14:00:00', $times[2][0], 'Monday third part starts at 14:00');
        $this->assertEquals('16:45:00', $times[2][1], 'Monday third part ends at 16:45');

        $times = $wh->hoursOf(1);
        $this->assertEquals('08:00:00', $times[0][0], 'Tuesday first part starts at 08:00');
        $this->assertEquals('10:00:00', $times[0][1], 'Tuesday first part ends at 10:00');
        $this->assertEquals('11:00:00', $times[1][0], 'Tuesday second part starts at 11:00');
        $this->assertEquals('12:00:00', $times[1][1], 'Tuesday second part ends at 12:00');
        $this->assertArrayNotHasKey(2, $times, 'Tuesday third part does not exist');

        $times = $wh->hoursOf(2);
        $this->assertEquals('08:00:00', $times[0][0], 'Wednesday first part starts at 08:00');
        $this->assertEquals('10:00:00', $times[0][1], 'Wednesday first part ends at 10:00');
        $this->assertEquals('11:00:00', $times[1][0], 'Wednesday second part starts at 11:00');
        $this->assertEquals('11:30:00', $times[1][1], 'Wednesday second part ends at 11:30');
        $this->assertArrayNotHasKey(2, $times, 'Wednesday third part does not exist');

        $times = $wh->hoursOf(3);
        $this->assertEquals('08:00:00', $times[0][0], 'Thursday first part starts at 08:00');
        $this->assertEquals('10:00:00', $times[0][1], 'Wednesday first part ends at 10:00');
        $this->assertArrayNotHasKey(1, $times, 'Wednesday second part does not exist');
        $this->assertArrayNotHasKey(2, $times, 'Wednesday third part does not exist');
    }

    public function testWithInvalidTime()
    {
        $wh = new WorkingHours(array());
        $times = $wh->hoursOf(0);
        $this->assertEquals(array(), $times, 'Empty times array return empty array');

        $wh = new WorkingHours('foo');
        $times = $wh->hoursOf(0);
        $this->assertEquals(array(), $times, 'Non array times return empty array');

        $working_hours = array(
            1 => array('0' => '08:00:00', '1' => '12:15:00', '2' => '', '3' => ''),
            2 => array('0' => '08:00:00', '1' => '', '2' => '', '3' => '10:00:00'),
            3 => array('0' => '08:00:00', '1' => '10:00:00', '2' => '11:00:00', '3' => '13:00:00'),
            4 => array('0' => '08:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '14:00:00'),
            5 => array('0' => '08:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '14:00:00'),
        );
        $wh = new WorkingHours($working_hours);
        $times = $wh->hoursOf(0);
        $this->assertEquals(array(), $times, 'Non existing day index return empty array');
    }
}
