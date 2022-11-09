<?php

use App\Model\Agent;
use App\Model\ConfigParam;
use App\Model\Manager;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AbsenceControllerNotificationTest extends PLBWebTestCase
{
    protected $builder;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $this->CSRFToken = '00000';

        $this->builder = new FixtureBuilder();
        $this->builder->delete(Agent::class);

        $this->entityManager = $entityManager;

        $GLOBALS['config']['Absences-validation'] = 1;
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

    private function createAbsenceFor($agent, $status = 0)
    {
        // Function absence->add has not access to session.
        $_SESSION['login_id'] = 1;

        $date = new DateTime('now + 3 day');

        $absence = new \absences();
        $absence->debut = $date->format('Y-m-d');
        $absence->fin = $date->format('Y-m-d');
        $absence->hre_debut = '00:00:00';
        $absence->hre_fin = '23:59:59';
        $absence->perso_ids = array($agent->id());
        $absence->commentaires = '';
        $absence->motif = 'AbsenceControllerAbsenceStatusesTest';
        $absence->valide = $status;
        $absence->CSRFToken = $this->CSRFToken;
        $absence->pj1 = '';
        $absence->pj2 = '';
        $absence->so = '';

        $absence->add();

        return $absence->id;
    }

    public function testAbsenceList()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 1);
        $this->setParam('Multisites-nombre', 1);

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
            'sites' => '["2"]', 'droits' => array(202,502,99,100)
        ));

        $this->createAbsenceFor($jdupont, 2);
        $this->createAbsenceFor($jdevoe, 2);
        $this->createAbsenceFor($abreton, 2);
        $this->createAbsenceFor($kboivin, 2);

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

        // Login with agent without rights for absences
        $this->logInAgent($jdupont, $jdupont->droits());
        $crawler = $client->request('GET', '/absence?perso_id=0');

        $this->assertSelectorNotExists('select#perso_id');

        $tbody = $crawler->filter('table#tableAbsencesVoir tbody tr');
        $this->assertCount(1, $tbody, 'jdupont see only one absence');
        $result = $crawler->filterXPath('//table[@id="tableAbsencesVoir"]');
        $this->assertStringContainsString('Dupont Jean', $result->text());

        // Login with agent having rights for absences
        $this->logInAgent($kboivin, $kboivin->droits());
        $crawler = $client->request('GET', '/absence?perso_id=0');

        $agents_select = $crawler->filter('select#perso_id option');
        $this->assertCount(4, $agents_select, 'KBoivin can select 4 options in the list (All, Admin and 3 agents)');

        // Check available agents ordered by name
        $this->assertEquals('Tous', $agents_select->eq(0)->html());
        $this->assertEquals('Boivin Karel', $agents_select->eq(1)->html());
        $this->assertEquals('Breton Aubert', $agents_select->eq(2)->html());
        $this->assertEquals('Dupont Jean', $agents_select->eq(3)->html());

        // Check for absence list.
        $tbody = $crawler->filter('table#tableAbsencesVoir tbody tr');
        $this->assertCount(3, $tbody, 'kboivin see managed absences');
        $this->assertEquals('Boivin Karel', $tbody->eq(0)->filter('td')->eq(3)->html());
        $this->assertEquals('Breton Aubert', $tbody->eq(1)->filter('td')->eq(3)->html());
        $this->assertEquals('Dupont Jean', $tbody->eq(2)->filter('td')->eq(3)->html());
    }
}