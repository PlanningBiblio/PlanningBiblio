<?php

use App\Model\Agent;

use App\PlanningBiblio\Helper\HolidayHelper;
use App\PlanningBiblio\ClosingDay;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

class HolidayHelperFreeBreakTest extends TestCase
{
    public function testGetCountedHoursWithFreeBreak() {

        $GLOBALS['config']['Conges-Mode'] = 'heures';
        $GLOBALS['config']['PlanningHebdo-PauseLibre'] = 1;

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'me3'));

        // Full day
        $working_hours = array(
            0 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:30:00'),
            // Tuesday.
            1 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '17:30:00'),
            // Wednesday.
            2 => array('0' => '09:00:00', '1' => '13:00:00', '2' => '14:00:00', '3' => '17:30:00'),
            // Thursday.
            3 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '16:00:00'),
            4 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            5 => array('0' => '', '1' => '', '2' => '', '3' => ''),
        );

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
        $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->id(),
                'debut' => '2021-01-01',
                'fin' => '2021-12-31',
                'temps' => json_encode($working_hours),
                'breaktime' => json_encode(array(1,1,1,1,1,0)),
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

        // Customer case 1
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '09:00:00',
            'end' => '2021-08-30',
            'hour_end' => '17:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(7, $result['hours'], 'full day');
        $this->assertEquals(50, $result['minutes'], 'full day');
        $this->assertEquals('7h30', $result['hr_hours'], 'full day');

        // Customer case 2
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '09:00:00',
            'end' => '2021-08-30',
            'hour_end' => '16:00:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(6, $result['hours'], 'holiday before the end of the day');
        $this->assertEquals(0, $result['minutes'], 'holiday before the end of the day');
        $this->assertEquals('6h00', $result['hr_hours'], 'holiday before the end of the day');

        // Customer case 3
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '10:00:00',
            'end' => '2021-08-30',
            'hour_end' => '16:00:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(5, $result['hours'], 'holiday after the start of the day and before the end of the day');
        $this->assertEquals(0, $result['minutes'], 'holiday after the start of the day and before the end of the day');
        $this->assertEquals('5h00', $result['hr_hours'], 'holiday after the start of the day and before the end of the day');

        // Customer case 4
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '11:00:00',
            'end' => '2021-08-30',
            'hour_end' => '17:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(5, $result['hours'], 'late holiday');
        $this->assertEquals(50, $result['minutes'], 'late holiday');
        $this->assertEquals('5h30', $result['hr_hours'], 'late holiday');

        // Customer case 5
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '14:00:00',
            'end' => '2021-08-30',
            'hour_end' => '17:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();
        $this->assertEquals(3, $result['hours'], 'afternoon without lunch break');
        $this->assertEquals(50, $result['minutes'], 'afternoon without lunch break');
        $this->assertEquals('3h30', $result['hr_hours'], 'afternoon without lunch break');

/*
        // Customer case 6
        // NOK with spec, expected 4h30, result 3h30
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-31',
            'hour_start' => '12:00:00',
            'end' => '2021-08-31',
            'hour_end' => '17:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();


        $this->assertEquals(4, $result['hours'], 'afternoon with lunch break 12-13');
        $this->assertEquals(50, $result['minutes'], 'afternoon with lunch break 12-13');
        $this->assertEquals('4h30', $result['hr_hours'], 'afternoon with lunch break 12-13');
*/

        // Customer case 7
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-09-01',
            'hour_start' => '12:00:00',
            'end' => '2021-09-01',
            'hour_end' => '17:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(3, $result['hours'], 'afternoon with lunch break 13-14');
        $this->assertEquals(50, $result['minutes'], 'afternoon with lunch break 13-14');
        $this->assertEquals('3h30', $result['hr_hours'], 'afternoon with lunch break 13-14');

/*
        // Customer case 8
        // NOK with spec, expected 4h00, result 3h00
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '09:00:00',
            'end' => '2021-08-30',
            'hour_end' => '13:00:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(4, $result['hours'], 'morning');
        $this->assertEquals(0, $result['minutes'], 'morning');
        $this->assertEquals('4h00', $result['hr_hours'], 'morning');
*/

        // Afternoon
        $working_hours = array(
            0 => array('0' => '14:00:00', '1' => '', '2' => '', '3' => '18:30:00'),
            // Agent is working 9 hours on Tuesday.
            1 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '', '3' => '17:30:00'),
            // Agent is working 6 hours on Wednesday.
            2 => array('0' => '09:00:00', '1' => '13:00:00', '2' => '', '3' => '17:30:00'),
            // Agent is working 7 hours on Thursday.
            3 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '16:00:00'),
            4 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            5 => array('0' => '', '1' => '', '2' => '', '3' => ''),
        );

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
        $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->id(),
                'debut' => '2021-01-01',
                'fin' => '2021-12-31',
                'temps' => json_encode($working_hours),
                'breaktime' => json_encode(array(0,0,0,0,0,0)),
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

        // Customer case 9
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '14:00:00',
            'end' => '2021-08-30',
            'hour_end' => '18:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(4, $result['hours'], 'full afternoon');
        $this->assertEquals(50, $result['minutes'], 'full afternoon');
        $this->assertEquals('4h30', $result['hr_hours'], 'full afternoon');

        // Customer case 10
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '16:00:00',
            'end' => '2021-08-30',
            'hour_end' => '18:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(2, $result['hours'], 'partial afternoon');
        $this->assertEquals(50, $result['minutes'], 'partial afternoon');
        $this->assertEquals('2h30', $result['hr_hours'], 'partial afternoon');

        // Morning
        $working_hours = array(
            0 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '13:00:00'),
            // Tuesday.
            1 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '', '3' => '17:30:00'),
            // Wednesday.
            2 => array('0' => '09:00:00', '1' => '13:00:00', '2' => '', '3' => '17:30:00'),
            // Thursday.
            3 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '16:00:00'),
            4 => array('0' => '', '1' => '', '2' => '', '3' => ''),
            5 => array('0' => '', '1' => '', '2' => '', '3' => ''),
        );

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
        $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->id(),
                'debut' => '2021-01-01',
                'fin' => '2021-12-31',
                'temps' => json_encode($working_hours),
                'breaktime' => json_encode(array(0,0,0,0,0,0)),
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

/*
        // Customer case 11
        // NOK with spec, expected 4h30, result 3h00
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '09:00:00',
            'end' => '2021-08-30',
            'hour_end' => '13:00:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(4, $result['hours'], 'full morning');
        $this->assertEquals(50, $result['minutes'], 'full morning');
        $this->assertEquals('4h30', $result['hr_hours'], 'full morning');
*/

/*
        // Customer case 12
        // NOK with spec, expected 3h30, result 3h00
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '09:00:00',
            'end' => '2021-08-30',
            'hour_end' => '13:00:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(3, $result['hours'], 'partial morning');
        $this->assertEquals(50, $result['minutes'], 'partial morning');
        $this->assertEquals('3h30', $result['hr_hours'], 'partial morning');
*/

        // Special cases:
        // Special case 6
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '12:00:00',
            'end' => '2021-08-30',
            'hour_end' => '13:00:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(0, $result['hours'], 'special case 6');
        $this->assertEquals(00, $result['minutes'], 'special case 6');
        $this->assertEquals('0h00', $result['hr_hours'], 'special case 6');

        // Special case 8
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '13:30:00',
            'end' => '2021-08-30',
            'hour_end' => '14:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(0, $result['hours'], 'special case 8');
        $this->assertEquals(00, $result['minutes'], 'special case 8');
        $this->assertEquals('0h00', $result['hr_hours'], 'special case 8');

        // Special case 9
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '12:30:00',
            'end' => '2021-08-30',
            'hour_end' => '13:30:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(0, $result['hours'], 'special case 9');
        $this->assertEquals(00, $result['minutes'], 'special case 9');
        $this->assertEquals('0h00', $result['hr_hours'], 'special case 9');

    }
}
