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
#        $GLOBALS['config']['Conges-fullday-switching-time'] = 4.25;
#        $GLOBALS['config']['Conges-fullday-reference-time'] = 7.5;


        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'me3'));

        // No model for workinghours yet. Use db function.
        $working_hours = array(
            0 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:30:00'),
            // Agent is working 9 hours on Tuesday.
            1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '18:00:00'),
            // Agent is working 6 hours on Wednesday.
            2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '15:00:00'),
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
                'breaktime' => json_encode(array(1,1,1,1,1,0)),
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-08-30',
            'hour_start' => '09:00:00',
            'end' => '2021-08-30',
            'hour_end' => '16:00:00',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(6, $result['hours'], 'test 1');
        $this->assertEquals(0, $result['minutes'], 'test 1');
        $this->assertEquals('6h00', $result['hr_hours'], 'test 1');

    }

}
