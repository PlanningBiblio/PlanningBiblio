<?php

use App\Entity\Agent;
use App\Entity\Absence;
use Tests\FixtureBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Tests\PLBWebTestCase;

class AbsenceControllerEditTest extends PLBWebTestCase
{

    public static function setUpBeforeClass(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Absence::class);

        // Agents

        $jdoe = new Agent();
        $jdoe->setLogin('jdoe');
        $jdoe->setLastname('Doe');
        $jdoe->setFirstname('John');
        $jdoe->setACL([99, 100]);
        $jdoe->setSites([1]);
        $entityManager->persist($jdoe);

        $bmarley = new Agent();
        $bmarley->setLogin('bmarley');
        $bmarley->setLastname('Marley');
        $bmarley->setFirstname('Bob');
        $bmarley->setACL([99, 100]);
        $bmarley->setSites([1]);
        $entityManager->persist($bmarley);

        $entityManager->flush();

    }

    protected function setUp(): void
    {
        parent::setUp();

    }

    public function testUniqueAgentSelection():void 
    {
        global $entityManager;
        $jdoe = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdoe']);

        $this->setUpPantherClient();
        $this->login($jdoe);

        $crawler = $this->client->request('GET', '/absence/add');

        $this->assertSelectorTextContains('h3', 'Ajouter une absence');

        $agentLabel = $crawler->filter('label[for=perso_ul1]');
        $this->assertEquals('Agent :', $agentLabel->text(), 'Form agent label incorrect');

        $agentValue = $crawler->filter('input#perso_ul1.form-control-plaintext');
        $this->assertEquals('Doe John', $agentValue->attr('value'), 'Form agent value incorrect');

        $this->assertSelectorNotExists('select#perso_ids.form-select');

    }


    public function testMultipleAgentSelectionWithForcedPreselection():void 
    {
        global $entityManager;
        $jdoe = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdoe']);
        $bmarley = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'bmarley']);

        // Add the right : Registering absences for multiple employees
        $this->setUpPantherClient();
        $jdoe->setACL([9, 99, 100]);
        $this->login($jdoe);

        $jdoeId = $jdoe->getId();
        $bmarleyId = $bmarley->getId();

        $crawler = $this->client->request('GET', '/absence/add');

        $agentLabel = $crawler->filter('legend[for=perso_ul1]');
        $this->assertEquals('Agent(s) :', $agentLabel->text(), 'Form agent label incorrect');

        $agentValue = $crawler->filter('ul li.perso_ids_li');
        $this->assertCount(1, $agentValue, 'There should be only 1 selected agent');
        $this->assertEquals('Doe John', $agentValue->text(), 'Form agent value incorrect');

        $this->assertSelectorNotExists('li.perso_ids_li#li'. $jdoeId . ' button.perso-drop', 'There should not be a close icon next to the agent name');
        $this->assertSelectorExists('select#perso_ids.form-select');

        $jdoeSelect = $crawler->filter('select#perso_ids option#option' . strval($jdoeId) );
        $this->assertStringContainsString('display:none',$jdoeSelect->attr('style'),'The option should have style="display:none".');

        // Select second agent
        $agentOption = $crawler->filterXPath(".//select[@id='perso_ids']//option[@value='" . $bmarleyId  . "']");
        $agentOption->click();

        $agentsValue = $crawler->filter('ul li.perso_ids_li');
        $this->assertCount(2, $agentsValue, 'There should be 2 selected agents');
        $this->assertEquals('Marley Bob', $agentsValue->eq(1)->text(), 'Form agent value incorrect');

        $this->assertSelectorExists('li.perso_ids_li#li'. $bmarleyId . ' button.perso-drop', 'There should be a close icon next to the agent name');
        
        $form = $crawler->filter('#absence-form')->form();
        $form->setValues(['debut' => '26/06/2026', 'motif' => 'Réunion' ]);

        // Validate form
        $submitForm = $crawler->filter("input.btn-primary[type=submit]");
        $submitForm->click();

    }

    public function testNoModificationRights():void 
    {
        global $entityManager;
        $bmarley = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'bmarley']);
        $bmarleyId = $bmarley->getId();

        $this->setUpPantherClient();
        $this->login($bmarley);

        $abs1 = $entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $bmarleyId]);
        
        $abs1Id = $abs1->getId();
        $crawler = $this->client->request('GET', "/absence/$abs1Id");

        $this->assertSelectorTextContains('h3', 'Modification de l\'absence');

        $link = $crawler->filter('a#back-link');
        $this->assertEquals('Retour à la liste des absences', $link->text(), 'The agent should not be autorized to edit the absence');

        $this->assertSelectorNotExists('#absence-form');
    }

    public function testMultipleAgentDisplay():void 
    {
        global $entityManager;
        $bmarley = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'bmarley']);
        $bmarleyId = $bmarley->getId();

        $this->setUpPantherClient();
        $bmarley->setACL([6, 99, 100]);
        $this->login($bmarley);

        $abs1 = $entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $bmarleyId]);
        $abs1Id = $abs1->getId();
        $crawler = $this->client->request('GET', "/absence/$abs1Id");

        $this->assertSelectorTextContains('h3', 'Modification de l\'absence');

        $agentLabel = $crawler->filter('label[for=perso_ul1]');
        $this->assertEquals('Agents :', $agentLabel->text(), 'Form agent label incorrect');

        $agentValue = $crawler->filter('ul#perso_ul1 li');
        $this->assertCount(2, $agentValue, 'There should be 2 selected agents');

        // $link = $crawler->filter('a.btn-primary');
        // $this->assertEquals('Retour', $link->text(), 'The agent should not be autorized to edit the absence');

    }

     public function testMultipleAgentSelectionWithPreselection():void 
     {
        global $entityManager;
        $admin = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'admin']);

        $this->setUpPantherClient();
        $this->login($admin);

        $crawler = $this->client->request('GET', '/absence/add');

        $agentLabel = $crawler->filter('legend[for=perso_ul1]');
        $this->assertEquals('Agent(s) :', $agentLabel->text(), 'Form agent label incorrect');

        $agentValue = $crawler->filter('ul li.perso_ids_li');
        $this->assertCount(1, $agentValue, 'There should be 1 pre selected agent');
        $this->assertEquals('Administrateur', $agentValue->text(), 'Form agent value incorrect');
        $this->assertSelectorExists('li.perso_ids_li#li1 button.perso-drop', 'There should be a close icon next to the admin name');

    }

    public function testMultipleAgentSelectionWithNoPreselection():void 
    {
        global $entityManager;
        $admin = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'admin']);

        $this->setUpPantherClient();
        $this->config->setParam('Absences-agent-preselection', 0);

        $crawler = $this->client->request('GET', '/absence/add');

        $agentLabel = $crawler->filter('legend[for=perso_ul1]');
        $this->assertEquals('Agent(s) :', $agentLabel->text(), 'Form agent label incorrect');

        $agentValue = $crawler->filter('ul li.perso_ids_li');
        $this->assertCount(0, $agentValue, 'There should be no pre selected agents');

    }

    public static function setUpAfterClass(): void
    {
    
        $this->config->setParam('Absences-agent-preselection', 1);

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Absence::class);

    }

    // public function testAdd(): void
    // {
    //     $this->config->setParam('Absences-notifications-agent-par-agent', 0);
    //     $this->config->setParam('Multisites-nombre', 1);

    //     $this->setUpPantherClient();

    //     $jdevoe = $this->builder->build(Agent::class, array(
    //         'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
    //         'droits' => array(99,100)
    //     ));
    //     $abreton = $this->builder->build(Agent::class, array(
    //         'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
    //         'droits' => array(99,100)
    //     ));
    //     $kboivin = $this->builder->build(Agent::class, array(
    //         'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
    //         'droits' => array(201,501,99,100)
    //     ));

    //     // Login with agent having rights for absences
    //     $this->login($kboivin);

    //     $this->client->request('GET', '/absence/add');

    //     $this->assertSelectorTextContains('h3', 'Ajouter une absence');

    //     $agents_selected = $this->getElementsText('ul#perso_ul1 li');
    //     $this->assertCount(1, $agents_selected, 'KBoivin is the only default selected agent');
    //     $this->assertTrue(in_array('Boivin Karel', $agents_selected), 'KBoivin is selected');

    //     $this->assertSelectorExists('select#perso_ids');

    //     $agents_list = $this->getSelectValues('perso_ids');
    //     $this->assertCount(5, $agents_list);

    //     $this->assertTrue(in_array(0, $agents_list), '-- Ajoutez un agent --');
    //     $this->assertTrue(in_array(1, $agents_list), 'Admin');
    //     $this->assertTrue(in_array($jdevoe->getId(), $agents_list), 'jdevoe');
    //     $this->assertTrue(in_array($abreton->getId(), $agents_list), 'abreton');
    //     $this->assertTrue(in_array($kboivin->getId(), $agents_list), 'kboivin');

    //     $agent_select = $this->getSelect('perso_ids');
    //     $agent_select->selectByValue($abreton->getId());

    //     $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());
    //     $agents_selected = $this->getElementsText('ul#perso_ul1 li');
    //     $this->assertCount(2, $agents_selected, 'KBoivin and ABreton are selected');
    // }
}
