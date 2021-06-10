<?php

use App\Model\Agent;

use App\PlanningBiblio\Helper\HolidayHelper;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

class HolidayHelperTest extends TestCase
{
    public function testgetManagedAgentMultiSitesNonAdmin() {
        $GLOBALS['config']['Multisites-nombre'] = 2;

        // Logged in user can manage holidays for agent in site 1.
        $GLOBALS['droits'] = array(
            23,6,9,701,3,4,21,1101,
            1201,22,5,17,1301,25,201,
            202,501,502,301,302,
            1001,1002,901,801,802,6,9,99,100,20
        );

        $builder = new FixtureBuilder();
        $luc_site1 = $builder->build(Agent::class, array('login' => 'luc', 'sites' => '["1"]'));
        $eric_site2 = $builder->build(Agent::class, array('login' => 'eric', 'sites' => '["2"]'));

        $helper = new HolidayHelper();
        $managed_agents = $helper->getManagedAgent(true, false);

        $this->assertArrayNotHasKey($luc_site1->id(), $managed_agents);
        $this->assertArrayNotHasKey($eric_site2->id(), $managed_agents);
    }

    public function testgetManagedAgentMultiSites() {
        $GLOBALS['config']['Multisites-nombre'] = 2;

        // Logged in user can manage holidays for agent in site 1.
        $GLOBALS['droits'] = array(
            23,6,9,701,3,4,21,1101,
            1201,22,5,17,1301,25,201,
            202,501,502,401,601,301,302,
            1001,1002,901,801,802,6,9,99,100,20
        );

        $builder = new FixtureBuilder();
        $bob_site1 = $builder->build(Agent::class, array('login' => 'bob', 'sites' => '["1"]'));
        $john_site2 = $builder->build(Agent::class, array('login' => 'john', 'sites' => '["2"]'));
        $olivia_all_site = $builder->build(Agent::class, array('login' => 'olivia', 'sites' => '["1","2"]'));
        $deleted_agent = $builder->build(Agent::class, array('login' => 'foo', 'sites' => '["1","2"]', 'supprime' => 1));

        $helper = new HolidayHelper();
        $managed_agents = $helper->getManagedAgent(true, false);

        $this->assertArrayHasKey($bob_site1->id(), $managed_agents, 'Bob is on site 1: managed');
        $this->assertArrayNotHasKey($john_site2->id(), $managed_agents, 'John is on site 2: not managed');
        $this->assertArrayHasKey($olivia_all_site->id(), $managed_agents, 'Olivia is on all site: managed');
        $this->assertArrayNotHasKey($deleted_agent->id(), $managed_agents, 'Agent deleted: not managed');

        // Call again getManagedAgent with deleted agents
        $managed_agents = $helper->getManagedAgent(true, true);
        $this->assertArrayHasKey($bob_site1->id(), $managed_agents, 'Bob is on site 1: managed');
        $this->assertArrayNotHasKey($john_site2->id(), $managed_agents, 'John is on site 2: not managed');
        $this->assertArrayHasKey($olivia_all_site->id(), $managed_agents, 'Olivia is on all sites: managed');
        $this->assertArrayHasKey($deleted_agent->id(), $managed_agents, 'Agent on all sites: managed');

    }

    public function testgetCountedHoursWithCongesFulldayReferenceTime() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-fullday-switching-time'] = 4.25;
        $GLOBALS['config']['Conges-fullday-reference-time'] = 7.5;

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'me'));

        // No model for workinghours yet. Use db function.
        $working_hours = array(
            0 => array('0' => '09:00:00', '1' => '12:30:00', '2' => '', '3' => ''),
            // Agent is working 9 hours on Tuesday.
            1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '18:00:00'),
            // Agent is working 6 hours on Wednesday.
            2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '15:00:00'),
            3 => array('0' => '', '1' => '', '2' => '', '3' => ''),
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
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

        // Request holiday on a Tuesday for all the day (9h of work).
        // A complete day is 7h30 (7.5)
        // rest is -1.5 (-1h30) to debit
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-06-01',
            'hour_start' => '00:00:00',
            'end' => '2021-06-01',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(7, $result['hours'], 'request 9h on 1 day');
        $this->assertEquals(50, $result['minutes'], 'request 9h on 1 day');
        $this->assertEquals('7h30', $result['hr_hours'], 'request 9h on 1 day');
        $this->assertEquals(-1.5, $result['rest'], 'request 9h on 1 day');
        $this->assertEquals('1h30', $result['hr_rest'], 'request 9h on 1 day');
        $this->assertEquals(1, $result['days'], 'request 9h on 1 day');

        // Request holiday on a Monday (3h30 of work).
        // Half is day 7h30 (7.5) / 2 = 3.75
        // Debit half a day (3.75).
        // rest is 0.25 (0h15) to credit
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-05-31',
            'hour_start' => '00:00:00',
            'end' => '2021-05-31',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(3, $result['hours'], 'request 3h30 on 1 day');
        $this->assertEquals(75, $result['minutes'], 'request 3h30 on 1 day');
        $this->assertEquals('3h45', $result['hr_hours'], 'request 3h30 on 1 day');
        $this->assertEquals(0.25, $result['rest'], 'request 3h30 on 1 day');
        $this->assertEquals('0h15', $result['hr_rest'], 'request 3h30 on 1 day');
        $this->assertEquals(0.5, $result['days'], 'request 3h30 on 1 day');

        // Request holiday on Wednesday (6h of work).
        // A complete day is 7h30 (7.5)
        // Debit a day 7h30 (7.5)
        // Rest is 1.5 (1h30) to credit.
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-06-02',
            'hour_start' => '00:00:00',
            'end' => '2021-06-02',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(7, $result['hours'], 'request 6h on 1 day');
        $this->assertEquals(50, $result['minutes'], 'request 6h on 1 day');
        $this->assertEquals('7h30', $result['hr_hours'], 'request 6h on 1 day');
        $this->assertEquals(1.5, $result['rest'], 'request 6h on 1 day');
        $this->assertEquals('1h30', $result['hr_rest'], 'request 6h on 1 day');
        $this->assertEquals(1, $result['days'], 'request 6h on 1 day');

        // Request holiday from Monday to Wednesday.
        // 9h + 3h30 + 6h of work
        // Debit 2.5 days ( 1 + 0.5 + 1)
        // Rest is 0.25 (-1.5 + 0.25 + 1.5).
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-05-31',
            'hour_start' => '00:00:00',
            'end' => '2021-06-02',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(18, $result['hours'], 'request 18h30 on 3 day');
        $this->assertEquals(75, $result['minutes'], 'request 18h30 on 3 day');
        $this->assertEquals('18h45', $result['hr_hours'], 'request 18h30 on 3 day');
        $this->assertEquals(0.25, $result['rest'], 'request 18h30 on 3 day');
        $this->assertEquals('0h15', $result['hr_rest'], 'request 18h30 on 3 day');
        $this->assertEquals(2.5, $result['days'], 'request 18h30 on 3 day');
    }

    public function testgetCountedHoursHolidayModeDays() {
        $GLOBALS['config']['Conges-Mode'] = 'jours';
        $GLOBALS['config']['Conges-fullday-switching-time'] = '';
        $GLOBALS['config']['Conges-fullday-reference-time'] = '';

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'roger'));

        // No model for workinghours yet. Use db function.
        $working_hours = array(
            0 => array('0' => '09:00:00', '1' => '12:30:00', '2' => '', '3' => ''),
            // Agent is working 9 hours on Tuesday.
            1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '18:00:00'),
            // Agent is working 6 hours on Wednesday.
            2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '15:00:00'),
            3 => array('0' => '', '1' => '', '2' => '', '3' => ''),
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
                'valide' => 1,
                'nb_semaine' => 1
            )
        );

        // Request holiday on a Tuesday for all the day (9h of work).
        $holidayHlper = new HolidayHelper(array(
            'start' => '2021-06-01',
            'hour_start' => '00:00:00',
            'end' => '2021-06-01',
            'hour_end' => '23:59:59',
            'perso_id' => $agent->id(),
            'is_recover' => 0,
        ));
        $result = $holidayHlper->getCountedHours();

        $this->assertEquals(7, $result['hours'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals(00, $result['minutes'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals('7h00', $result['hr_hours'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals(0, $result['rest'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals('', $result['hr_rest'], 'request 9h on 1 day (default reference time)');
        $this->assertEquals(1, $result['days'], 'request 9h on 1 day (default reference time)');

    }
}
