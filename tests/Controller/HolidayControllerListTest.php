<?php

use App\Model\Agent;
use App\Model\Holiday;
use App\Model\Manager;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class HolidayControllerListTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);

        $GLOBALS['config']['Absences-validation'] = 1;
    }

    private function createHolidayFor($agent, $status = 0)
    {
        $_SESSION['login_id'] = 1;

        $date = new DateTime('now + 3 day');

        $holiday1 = $this->builder->build(Holiday::class, array(
            'perso_id' => 0, 'debut' => $date, 'fin' => $date,
            'commentaires' => 'pop','origin_id' => 0, 'regul_id' => 0,
        ));

        $holiday = $this->builder->build(Holiday::class, array(
            'perso_id' => $agent->id(), 'debut' => $date, 'fin' => $date,
            'commentaires' => 'pop','origin_id' => $holiday1->id(), 'regul_id' => $holiday1->id(),
        ));

        return $holiday->id();
    }

    public function testHolidayList()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Multisites-nombre', 1);
        $this->setParam('PlanningHebdo', 0);

        $client = static::createClient();

        $workinghours = array(
            0 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '17:00:00'),
            1 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '17:00:00'),
            2 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '17:00:00'),
            3 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '17:00:00'),
            4 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '17:00:00'),
            5 => array('0' => '09:00:00', '1' => '12:00:00', '2' => '13:00:00', '3' => '17:00:00'),
        );

        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean',
            'sites' => '', 'droits' => array(99,100), 'temps' => json_encode($workinghours)
        ));
        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'sites' => '["1","2"]', 'droits' => array(99,100), 'temps' => json_encode($workinghours)
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'sites' => '["1"]', 'droits' => array(99,100), 'temps' => json_encode($workinghours)
        ));
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'sites' => '["2"]', 'droits' => array(202,502,99,100), 'temps' => json_encode($workinghours)
        ));

        $this->createHolidayFor($jdupont, 2);
        $this->createHolidayFor($jdevoe, 2);
        $this->createHolidayFor($abreton, 2);
        $this->createHolidayFor($kboivin, 2);

        // Make kboivin manager of jdupont
        $manager = new Manager();
        $manager->perso_id($jdupont);
        $manager->notification_level1(0);
        $kboivin->addManaged($manager);

        // Make kboivin manager of abreton
        $manager = new Manager();
        $manager->perso_id($abreton);
        $manager->notification_level1(0);
        $kboivin->addManaged($manager);

        $date = new DateTime();
        $year = intval($date->format('Y'));
        $thisMonth = $date->format('n');
        $date = new DateTime('now + 3 days');
        $holidayMonth = $date->format('n');
        if ($thisMonth == 8 and $holidayMonth == 9) {
            $year++;
        }

        // Login with agent without rights for holiday
        $this->logInAgent($jdupont, $jdupont->getACL());
        $crawler = $client->request('GET', "/holiday/index?annee=$year");

        $this->assertSelectorNotExists('select#perso_id');

        $result = $crawler->filterXPath('//table[@id="tableConges"]');
        $this->assertStringNotContainsString('Nom', $result->text(null,false));

        // Login with agent having rights for holiday
        $this->logInAgent($kboivin, $kboivin->getACL());
        $crawler = $client->request('GET', "/holiday/index?annee=$year");

        $agents_select = $crawler->filter('select#perso_id option');
        $this->assertCount(4, $agents_select, 'KBoivin can select 4 options in the list (All, Admin and 3 agents)');

        // Check available agents ordered by name
        $this->assertEquals('Tous', $agents_select->eq(0)->html());
        $this->assertEquals('Boivin Karel', $agents_select->eq(1)->html());
        $this->assertEquals('Breton Aubert', $agents_select->eq(2)->html());
        $this->assertEquals('Dupont Jean', $agents_select->eq(3)->html());

        // Check for absence list.
        $result = $crawler->filterXPath('//table[@id="tableConges"]');
        $this->assertStringContainsString('Dupont J', $result->text(null,false));
    }

    public function testStatuses()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Multisites-nombre', 1);
        $this->setParam('PlanningHebdo', 1);
        $this->setParam('Multisites-nombre', 1);
        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('PlanningHebdo', 0);
        $this->setParam('Conges-Enable', 1);
        $this->setParam('Conges-Mode', 'heures');
        $this->setParam('Conges-Heures', 0);
        $this->setParam('Conges-validation', 1);
        $this->setParam('Conges-Validation-N2', 0);
        $this->setParam('Conges-Recuperations', 1);
        $this->setParam('Conges-tous', 0);
        $this->setParam('Conges-Rappels-Jours', 14);
        $this->setParam('Conges-demi-journees', 1);
        $this->setParam('Conges-fullday-switching-time', 4);
        $this->setParam('Conges-fullday-reference-time', '');
        $this->setParam('Conges-planningVide', 1);
        $this->setParam('Conges-apresValidation', 1);
        $this->setParam('Recup-Uneparjour', 1);
        $this->setUpPantherClient();

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

        $this->createHolidayFor($jdupont, 2);
        $this->createHolidayFor($jdevoe, 2);
        $this->createHolidayFor($abreton, 2);
        $this->createHolidayFor($kboivin, 2);

        // Make kboivin manager of jdupont
        $manager = new Manager();
        $manager->perso_id($jdupont);
        $manager->notification_level1(0);
        $kboivin->addManaged($manager);

        // Make kboivin manager of abreton
        $manager = new Manager();
        $manager->perso_id($abreton);
        $manager->notification_level1(0);
        $kboivin->addManaged($manager);

        // Login
        $this->login($kboivin);

        $crawler = $this->client->request('GET', '/holiday/new');

        $agents_selected = $this->getElementsText('ul#perso_ul1 li');
        $this->assertCount(1, $agents_selected, 'jdupont is the only default selected agent');
        $this->assertTrue(in_array('Boivin Karel', $agents_selected), 'KBoivin is selected');

        $result = $crawler->filterXPath('//td[@id="validation-statuses"]');
        $this->assertStringContainsString('Demandé', $result->text());

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(6, $agents_list);
        $this->assertTrue(in_array(0, $agents_list), '-- Ajoutez un agent --');
        $this->assertTrue(in_array(1, $agents_list), 'Admin');
        $this->assertTrue(in_array($jdupont->id(), $agents_list), 'jdevoe');
        $this->assertTrue(in_array($abreton->id(), $agents_list), 'abreton');
        $this->assertTrue(in_array($kboivin->id(), $agents_list), 'kboivin');

        $agent_select = $this->getSelect('perso_ids');
        $agent_select->selectByValue($abreton->id());

        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());

        $result = $crawler->filterXPath('//td[@id="validation-statuses"]');
        $this->assertStringContainsString('Demandé', $result->text());
        $this->assertStringContainsString('Accepté', $result->text());
        $this->assertStringContainsString('Refusé', $result->text());
    }
}
