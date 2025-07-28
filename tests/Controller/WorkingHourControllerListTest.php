<?php

use App\Model\Agent;
use App\Model\Manager;
use App\Model\WorkingHour;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class WorkingHourControllerListTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);

        $GLOBALS['config']['Absences-validation'] = 1;
    }

    private function createWorkingHoursFor($agent, $status = 0)
    {
        $_SESSION['login_id'] = 1;

        $date = new DateTime('now + 3 day');
        $end = new DateTime('now + 1 year');

        $week_planning = $this->builder->build(
            WorkingHour::class,
            array(
                'perso_id' => $agent->id(),
                'debut' => $date,
                'fin' => $end,
                'valide_n1' => 1,
                'valide' => 0,
                'remplace' => 0,
                'temps' => json_encode(
                    array(
                        0 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
                        1 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
                        2 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
                        3 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
                        4 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
                        5 => array('0' => '09:00:00', '1' => '', '2' => '', '3' => '17:00:00'),
                    )
                ),
            )
        );

        return $week_planning->id();
    }

    public function testWeekPlanningList()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Multisites-nombre', 1);
        $this->setParam('PlanningHebdo', 1);
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',1);

        $client = static::createClient();


        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean',
            'sites' => '', 'droits' => array(99,100)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'sites' => '["1","2"]', 'droits' => array(99,100)
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'sites' => '["1"]', 'droits' => array(99,100)
        ));
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'sites' => '["2"]', 'droits' => array(3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301)
        ));

        $this->createWorkingHoursFor($jdupont, 2);
        $this->createWorkingHoursFor($jdevoe, 2);
        $this->createWorkingHoursFor($abreton, 2);
        $this->createWorkingHoursFor($kboivin, 2);

        // Make kboivin manager of jdupont
        $manager = new Manager();
        $manager->setUser($jdupont);
        $manager->setLevel1Notification(0);
        $kboivin->addManaged($manager);

        // Make kboivin manager of abreton
        $manager = new Manager();
        $manager->setUser($abreton);
        $manager->setLevel1Notification(0);
        $kboivin->addManaged($manager);

        // Login with agent without rights for WeekPlannings
        $this->logInAgent($jdupont, $jdupont->getACL());
        $crawler = $client->request('GET', '/workinghour');

        $this->assertSelectorNotExists('select#perso_id');

        $result = $crawler->filterXPath('//table[@id="tablePlanningHebdo"]');
        $this->assertStringContainsString('Dupont', $result->text(null,false));
        $this->assertStringContainsString('Jean', $result->text(null,false));

        // Login with agent having rights for WeekPlanning
        $this->logInAgent($kboivin, $kboivin->getACL());
        $crawler = $client->request('GET', '/workinghour');

        // Check available agents ordered by name
        $result = $crawler->filterXPath('//table[@id="tablePlanningHebdo"]/tbody/tr');
        $this->assertStringContainsString('Boivin', $result->eq(0)->text(null,false));
        $this->assertStringContainsString('Karel', $result->eq(0)->text(null,false));
        $this->assertStringContainsString('Breton', $result->eq(1)->text(null,false));
        $this->assertStringContainsString('Aubert', $result->eq(1)->text(null,false));
        $this->assertStringContainsString('Dupont', $result->eq(2)->text(null,false));
        $this->assertStringContainsString('Jean', $result->eq(2)->text(null,false));
    }

    public function testStatuses()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Multisites-nombre', 1);
        $this->setParam('PlanningHebdo', 1);
        $this->setParam('PlanningHebdo-notifications-agent-par-agent',1);

        $this->setUpPantherClient();


        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean',
            'sites' => '', 'droits' => array(99,100)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'sites' => '["1","2"]', 'droits' => array(99,100)
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'sites' => '["1"]', 'droits' => array(99,100)
        ));
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'sites' => '["2"]', 'droits' => array(3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301)
        ));

        $this->createWorkingHoursFor($jdupont, 2);
        $this->createWorkingHoursFor($jdevoe, 2);
        $this->createWorkingHoursFor($abreton, 2);
        $this->createWorkingHoursFor($kboivin, 2);

        // Make kboivin manager of jdupont
        $manager = new Manager();
        $manager->setUser($jdupont);
        $manager->setLevel1Notification(0);
        $kboivin->addManaged($manager);

        // Make kboivin manager of abreton
        $manager = new Manager();
        $manager->setUser($abreton);
        $manager->setLevel1Notification(0);
        $kboivin->addManaged($manager);

        // Login
        $this->login($kboivin);

        $crawler = $this->client->request('GET', '/workinghour/add');

        $agents_list = $this->getSelectValues('perso_id');
        $this->assertCount(4, $agents_list);
        $this->assertTrue(in_array($jdupont->id(), $agents_list), 'jdupont');
        $this->assertTrue(in_array($abreton->id(), $agents_list), 'abreton');
        $this->assertTrue(in_array($kboivin->id(), $agents_list), 'kboivin');

        $agent_select = $this->getSelect('perso_id');
        $agent_select->selectByValue($kboivin->id());

        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());

        $result = $crawler->filterXPath('//span[@id="validation"]');
        $this->assertStringContainsString('Demandé', $result->text());
        $this->assertStringNotContainsString('Accepté', $result->text());

        //with abreton selected
        $id = $abreton->id();
        $crawler = $this->client->request('GET', "/workinghour/add/$id");

        $result = $crawler->filterXPath('//select[@id="validation"]');
        $this->assertStringContainsString('Demandé', $result->text());
        $this->assertStringContainsString('Accepté', $result->text());
        $this->assertStringContainsString('Refusé', $result->text());
    }
}
