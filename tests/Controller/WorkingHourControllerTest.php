<?php

use App\Model\Agent;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class WorkingHourControllerTest extends PLBWebTestCase
{
    public function testAccessWorkingHoursList() {
        $client = static::createClient();
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        // Anonymous user.
        $client->request('GET', '/workinghour');
        $response = $client->getResponse()->getContent();
        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            'Anonymous users get forbiden access'
        );

        // User without rights.
        $this->logInAgent($agent, array(100));
        $client->request('GET', '/workinghour');
        $response = $client->getResponse()->getContent();
        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            'Users without exepected rights get forbiden access'
        );

        // User with rights.
        $this->logInAgent($agent, array(1101));
        $client->request('GET', '/workinghour');
        $response = $client->getResponse()->getContent();
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            'Users with 1101 right successfuly access'
        );

        $this->logInAgent($agent, array(1201));
        $client->request('GET', '/workinghour');
        $response = $client->getResponse()->getContent();
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            'Users with 1201 right successfuly access'
        );
    }

    public function testWorkingHoursValidationRights() {
        $client = static::createClient();

        $builder = new FixtureBuilder();
        $greg = $builder->build(Agent::class, array('login' => 'greg'));
        $greg_workinghours = array(
            0 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            3 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            4 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            5 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
        );

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $greg_wh_id = $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $greg->id(),
                'debut' => '2021-01-01',
                'fin' => '2021-12-31',
                'temps' => json_encode($greg_workinghours),
                'valide_n1' => 0,
                'valide' => 0,
                'nb_semaine' => 1
            )
        );

        $GLOBALS['config']['PlanningHebdo-Agents'] = 0;

        $this->logInAgent($greg, array(100));
        $crawler = $client->request('GET', "/workinghour/$greg_wh_id");
        $status = $crawler
            ->filterXPath('//div[@id="content"]/div[@id="workhours"]/div[@class="admin-div"]/div[@id="working_hours"]/form/p/span')
            ->text();
        $this->assertEquals('Demandé', $status, 'User with right can see the status but cannot change it');

        // Agent that can validate level 1
        $joe = $builder->build(Agent::class, array('login' => 'joe'));
        $this->logInAgent($joe, array(100, 1101));
        $crawler = $client->request('GET', "/workinghour/$greg_wh_id");

        $statuses = array();
        foreach ($crawler->filterXPath('//select[@name="validation"]/option') as $option) {
            $statuses[] = $option->textContent;
        }
        $this->assertEquals(3, count($statuses), 'User with 1101 right can choose 3 statuses (level 1 only)');
        $this->assertEquals('Demandé', $statuses[0], 'User with 1101 right can choose status asked');
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses[1], 'User with 1101 right can choose status accepted level 1');
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses[2], 'User with 1101 right can choose status rejectes level 1');

        // Agent that can validate level 2
        $bob = $builder->build(Agent::class, array('login' => 'bob'));
        $this->logInAgent($bob, array(100, 1101, 1201));
        $crawler = $client->request('GET', "/workinghour/$greg_wh_id");

        $statuses = array();
        foreach ($crawler->filterXPath('//select[@name="validation"]/option') as $option) {
            $statuses[] = $option->textContent;
        }
        $this->assertEquals(5, count($statuses), 'User with 1201 right can choose 5 statuses (level 1 and 2)');
        $this->assertEquals('Demandé', $statuses[0], 'User with 1201 right can choose status asked');
        $this->assertEquals('Accepté (En attente de validation hiérarchique)', $statuses[1], 'User with 1201 right can choose status accepted level 1');
        $this->assertEquals('Refusé (En attente de validation hiérarchique)', $statuses[2], 'User with 1201 right can choose status rejected level 1');
        $this->assertEquals('Accepté', $statuses[3], 'User with 1201 right can choose status accepted level 2');
        $this->assertEquals('Refusé', $statuses[4], 'User with 1201 right can choose status rejected level 2');
    }

    public function testCreateOwnWorkingHours() {
        $client = static::createClient();
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $GLOBALS['config']['PlanningHebdo-Agents'] = 0;

        $agent = $builder->build(Agent::class, array('login' => 'test'));

        $this->logInAgent($agent, array(100));
        $client->request('GET', '/workinghour/add');
        $client->followRedirect();
        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            'With PlanningHebdo-Agents disabled, users without right cannot create own working hours'
        );
    }

    public function testEditOtherAgentsWorkingHours() {
        $client = static::createClient();
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $GLOBALS['config']['PlanningHebdo-Agents'] = 1;

        $loggedin_agent = $builder->build(Agent::class, array('login' => 'test'));
        $greg = $builder->build(Agent::class, array('login' => 'greg'));

        $greg_workinghours = array(
            0 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            3 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            4 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
            5 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
        );

        $_SESSION['oups']['CSRFToken'] = '00000';
        $db = new \db();
        $db->CSRFToken = '00000';
        $greg_wh_id = $db->insert(
            'planning_hebdo',
            array(
                'perso_id' => $greg->id(),
                'debut' => '2021-01-01',
                'fin' => '2021-12-31',
                'temps' => json_encode($greg_workinghours),
                'valide_n1' => 0,
                'valide' => 0,
                'nb_semaine' => 1
            )
        );

        $this->logInAgent($loggedin_agent, array(100));
        $client->request('GET', "/workinghour/$greg_wh_id");
        $client->followRedirect();
        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            'Users without right cannot access working hours of other agents'
        );
    }
}
