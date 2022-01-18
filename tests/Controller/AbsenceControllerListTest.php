<?php

use App\Model\Agent;
use App\Model\Manager;
use App\Model\ConfigParam;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

require_once(__DIR__ . '/../../public/absences/class.absences.php');

class AbsenceControllerListTest extends PLBWebTestCase
{
    protected $builder;
    protected $entityManager;
    protected $CSRFToken;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';

        $this->builder = new FixtureBuilder();
        $this->builder->delete(Agent::class);

        $this->entityManager = $entityManager;
    }

    protected function setParam($name, $value)
    {
        $GLOBALS['config'][$name] = $value;
        $param = $this->entityManager
            ->getRepository(ConfigParam::class)
            ->findOneBy(['nom' => $name]);

        $param->valeur($value);
        $this->entityManager->persist($param);
        $this->entityManager->flush();
    }

    public function testList()
    {

        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('Multisites-nombre', 1);

        $client = static::createClient();

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100)
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'droits' => array(99,100)
        ));
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'droits' => array(201,501,99,100)
        ));

        $this->createAbsenceFor($jdevoe, 2);
        $this->createAbsenceFor($abreton, 2);
        $this->createAbsenceFor($kboivin, 2);

        $this->logInAgent($jdevoe, $jdevoe->droits());
        $crawler = $client->request('GET', '/absence?perso_id=0');

        $this->assertResponseIsSuccessful('Jdevoe can access to list page');
        $this->assertSelectorNotExists('select#perso_id');

        $tbody = $crawler->filter('table#tableAbsencesVoir tbody tr');
        $this->assertCount(1, $tbody, 'jdevoe see only one absence');
        $jdevoe_abs = $tbody->filter('td')->eq(3);
        $this->assertEquals('Devoe John', $jdevoe_abs->html());

        // Login with agent having rights for absences
        $this->logInAgent($kboivin, $kboivin->droits());
        $crawler = $client->request('GET', '/absence?perso_id=0');

        $this->assertResponseIsSuccessful('KBoivin can access to list page');
        $this->assertSelectorExists('select#perso_id');

        $agents_select = $crawler->filter('select#perso_id option');
        $this->assertCount(5, $agents_select, 'KBoivin can select 5 options in the list (All, Admin and 3 agents)');

        // Check available agents ordered by name
        $this->assertEquals('Tous', $agents_select->eq(0)->html());
        $this->assertEquals('Administrateur ', $agents_select->eq(1)->html());
        $this->assertEquals('Boivin Karel', $agents_select->eq(2)->html());
        $this->assertEquals('Breton Aubert', $agents_select->eq(3)->html());
        $this->assertEquals('Devoe John', $agents_select->eq(4)->html());

        // Check the selected agent by defaut.
        $this->assertEquals('selected', $agents_select->eq(0)->attr('selected'));

        // Check for absence list.
        $tbody = $crawler->filter('table#tableAbsencesVoir tbody tr');
        $this->assertCount(3, $tbody, 'kboivin see his own absences');
        $this->assertEquals('Boivin Karel', $tbody->eq(0)->filter('td')->eq(3)->html());
        $this->assertEquals('Breton Aubert', $tbody->eq(1)->filter('td')->eq(3)->html());
        $this->assertEquals('Devoe John', $tbody->eq(2)->filter('td')->eq(3)->html());
    }

    public function testListMultiSites()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('Multisites-nombre', 2);

        $client = static::createClient();

        $dailleboust = $this->builder->build(Agent::class, array(
            'login' => 'dailleboust', 'nom' => 'Ailleboust', 'prenom' => 'Denis',
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
            'sites' => '["2"]', 'droits' => array(202,502,99,100)
        ));

        $this->createAbsenceFor($dailleboust, 2);
        $this->createAbsenceFor($jdevoe, 2);
        $this->createAbsenceFor($abreton, 2);
        $this->createAbsenceFor($kboivin, 2);

        // Login with agent having rights for absences
        $this->logInAgent($kboivin, $kboivin->droits());
        $crawler = $client->request('GET', '/absence?perso_id=0');

        $agents_select = $crawler->filter('select#perso_id option');
        $this->assertCount(3, $agents_select, 'KBoivin can select 3 options in the list (All, Admin and 3 agents)');

        // Check available agents ordered by name
        $this->assertEquals('Tous', $agents_select->eq(0)->html());
        $this->assertEquals('Boivin Karel', $agents_select->eq(1)->html());
        $this->assertEquals('Devoe John', $agents_select->eq(2)->html());

        // Check for absence list.
        $tbody = $crawler->filter('table#tableAbsencesVoir tbody tr');
        $this->assertCount(2, $tbody, 'kboivin see his own absences');
        $this->assertEquals('Boivin Karel', $tbody->eq(0)->filter('td')->eq(3)->html());
        $this->assertEquals('Devoe John', $tbody->eq(1)->filter('td')->eq(3)->html());
    }

    public function testListWithAbsencesNotificationsAgentParAgent()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Multisites-nombre', 2);

        $client = static::createClient();

        $dailleboust = $this->builder->build(Agent::class, array(
            'login' => 'dailleboust', 'nom' => 'Ailleboust', 'prenom' => 'Denis',
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
            'sites' => '["2"]', 'droits' => array(202,502,99,100)
        ));

        $this->createAbsenceFor($dailleboust, 2);
        $this->createAbsenceFor($jdevoe, 2);
        $this->createAbsenceFor($abreton, 2);
        $this->createAbsenceFor($kboivin, 2);

        // Make kboivin manager of dailleboust
        $manager = new Manager();
        $manager->perso_id($dailleboust);
        $manager->notification_level1(0);
        $kboivin->addManaged($manager);

        // Make kboivin manager of abreton
        $manager = new Manager();
        $manager->perso_id($abreton);
        $manager->notification_level1(0);
        $kboivin->addManaged($manager);

        // Login with agent having rights for absences
        $this->logInAgent($kboivin, $kboivin->droits());
        $crawler = $client->request('GET', '/absence?perso_id=0');

        $agents_select = $crawler->filter('select#perso_id option');
        $this->assertCount(4, $agents_select, 'KBoivin can select 4 options in the list (All, Admin and 3 agents)');

        // Check available agents ordered by name
        $this->assertEquals('Tous', $agents_select->eq(0)->html());
        $this->assertEquals('Ailleboust Denis', $agents_select->eq(1)->html());
        $this->assertEquals('Boivin Karel', $agents_select->eq(2)->html());
        $this->assertEquals('Breton Aubert', $agents_select->eq(3)->html());

        // Check for absence list.
        $tbody = $crawler->filter('table#tableAbsencesVoir tbody tr');
        $this->assertCount(3, $tbody, 'kboivin see managed absences');
        $this->assertEquals('Ailleboust Denis', $tbody->eq(0)->filter('td')->eq(3)->html());
        $this->assertEquals('Boivin Karel', $tbody->eq(1)->filter('td')->eq(3)->html());
        $this->assertEquals('Breton Aubert', $tbody->eq(2)->filter('td')->eq(3)->html());
    }

    private function createAbsenceFor($agent, $status = 0)
    {
        $date = new DateTime('now + 3 day');

        $absence = new \absences();
        $absence->debut = $date->format('Y-m-d');
        $absence->fin = $date->format('Y-m-d');
        $absence->hre_debut = '00:00:00';
        $absence->hre_fin = '23:59:59';
        $absence->perso_ids = array($agent->id());
        $absence->commentaires = '';
        $absence->motif = 'Foo';
        $absence->CSRFToken = $this->CSRFToken;
        $absence->valide = $status;
        $absence->pj1 = '';
        $absence->pj2 = '';
        $absence->so = '';
        $absence->add();
    }
}
