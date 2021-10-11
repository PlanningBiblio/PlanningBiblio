<?php

use App\Model\Agent;

use App\PlanningBiblio\Helper\HolidayHelper;
use PHPUnit\Framework\TestCase;
use Tests\Utils;
use Tests\FixtureBuilder;

class HolidayHelperTest extends TestCase
{
    public function testgetManagedAgentMultiSitesNonAdmin() {
        $GLOBALS['config']['Multisites-nombre'] = 2;

        // Logged in user can't manage holidays on any site.
        $GLOBALS['droits'] = array(
            23,6,9,701,3,4,21,1101,
            1201,22,5,17,1301,25,201,
            202,501,502,301,302,
            1001,1002,901,801,802,6,9,99,100,20
        );

        $luc_site1 = Utils::createAgent(array('login' => 'luc', 'sites' => '["1"]'));
        $eric_site2 = Utils::createAgent(array('login'=> 'eric', 'sites' => '["2"]'));

        $helper = new HolidayHelper();
        $managed_agents = $helper->getManagedAgent(true, false);

        $this->assertArrayNotHasKey($luc_site1->id(), $managed_agents);
        $this->assertArrayNotHasKey($eric_site2->id(), $managed_agents);
    }

    public function testgetManagedAgentMonoSitesNonAdmin() {
        $GLOBALS['config']['Multisites-nombre'] = 1;

        // Logged in user can't manage holidays.
        $GLOBALS['droits'] = array(
            23,6,9,701,3,4,21,1101,
            1201,22,5,17,1301,25,201,
            202,501,502,301,302,
            1001,1002,901,801,802,6,9,99,100,20
        );

        $builder = new FixtureBuilder();
        $dupont = $builder->build(Agent::class, array('login' => 'a.dupont', 'sites' => ''));

        $helper = new HolidayHelper();
        $managed_agents = $helper->getManagedAgent(true, false);

        $this->assertArrayNotHasKey($dupont->id(), $managed_agents, 'Dupont is not a managed agent');
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

        $bob_site1 = Utils::createAgent(array('login' => 'bob', 'sites' => '["1"]'));
        $john_site2 = Utils::createAgent(array('login'=> 'john', 'sites' => '["2"]'));
        $olivia_all_site = Utils::createAgent(array('login' => 'olivia', 'sites' => '["1","2"]'));
        $deleted_agent = Utils::createAgent(array('login' => 'foo', 'sites' => '["1","2"]', 'supprime' => 1));

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
}
