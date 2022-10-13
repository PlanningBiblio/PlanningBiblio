<?php

use App\Model\Agent;

use App\PlanningBiblio\Helper\HolidayHelper;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

class HolidayHelperWeeklyRotation extends TestCase
{
    public function testgetCountedHoursWithTwoWeekRotation()
    {
        $GLOBALS['config']['PlanningHebdo'] = 1;
        $GLOBALS['config']['nb_semaine'] = 2;

        $working_hours = array(
            0 => array('09:00:00','','','17:30:00',''),
            1 => array('09:00:00','','','17:30:00',''),
            2 => array('09:00:00','','','17:30:00',''),
            3 => array('09:00:00','','','17:30:00',''),
            4 => array('09:00:00','','','17:30:00',''),
            5 => array('','','','',''),
            7 => array('08:00:00','','','17:30:00',''),
            8 => array('08:00:00','','','17:30:00',''),
            9 => array('08:00:00','','','17:30:00',''),
            10 => array('08:00:00','','','17:30:00',''),
            11 => array('08:00:00','','','17:30:00',''),
            12 => array('','','','','')
        );

        //$breatimes = array(1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 0);

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'twoweekrotation'));

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $db->delete('planning_hebdo');
        $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $agent->id(),
                'debut' => '2022-06-01',
                'fin' => '2022-12-31',
                'temps' => json_encode($working_hours),
                'valide' => 1,
                'nb_semaine' => 2
            )
        );

        // Request holiday on even week.
        // The week this agent works 9h30.
        $holidayHlper = new HolidayHelper(array(
            'start' => '2022-06-17',
            'hour_start' => '00:00:00',
            'end' => '2022-06-17',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(9, $result['hours'], 'request 7h on 1 day');
        $this->assertEquals(50, $result['minutes'], 'request 30 minutes on 1 day');
    }
}
