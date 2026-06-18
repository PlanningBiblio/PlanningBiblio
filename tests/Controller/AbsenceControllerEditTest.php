<?php

use App\Entity\Agent;
use App\Entity\Manager;
use App\Entity\Absence;
use App\Entity\AbsenceDocument;
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

        $this->setUpPantherClient();
        
        // Add right 9 : Registering absences for multiple employees
        $jdoe->setACL([9, 99, 100]);

        // Add absence-validation param
        $this->config->setParam('Absences-validation', 1);
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
        $form->setValues(['debut' => '26/06/2026', 'motif' => 'Formation' ]);

        // Validate form
        $this->client->submit($form);

    }

    public function testNoModificationRights():void 
    {

        global $entityManager;
        $bmarley = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'bmarley']);
        $bmarleyId = $bmarley->getId();

        $this->setUpPantherClient();
        $this->login($bmarley);

        $abs1 = $entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $bmarleyId]);
        $crawler = $this->client->request('GET', '/absence/'. $abs1->getId());

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

        // Add right 6 :  Editing their own absences
        $bmarley->setACL([6, 99, 100]);

        $this->login($bmarley);

        $abs1 = $entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $bmarleyId]);
        $crawler = $this->client->request('GET', '/absence/'. $abs1->getId());

        $this->assertSelectorTextContains('h3', 'Modification de l\'absence');

        $agentLabel = $crawler->filter('label[for=perso_ul1]');
        $this->assertEquals('Agents :', $agentLabel->text(), 'Form agent label incorrect');

        $agentValue = $crawler->filter('ul#perso_ul1 li');
        $this->assertCount(2, $agentValue, 'There should be 2 selected agents');

        // Recurrence disabled
        $recurrenceCheckbox = $crawler->filter('#recurrence-checkbox');
        $this->assertNotNull($recurrenceCheckbox->attr('disabled'), 'The recurrence checkbox should be disabled');
        
        $recurrenceLink = $crawler->filter('#recurrence-link');
        $this->assertStringContainsString('display:none',$recurrenceLink->attr('style'),'The edition link should not be visible.');

        // Buttons
        $link = $crawler->filter('a.btn-primary');
        $this->assertEquals('Retour', $link->text(), 'The agent should not be autorized to edit the absence');
        $this->assertSelectorNotExists('input#absence-bouton-suppression');
        $this->assertSelectorNotExists('input.btn-primary[type=submit]');

    }

     public function testMultipleAgentSelectionWithPreselection():void 
     {

        global $entityManager;
        $admin = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'admin']);

        $this->setUpPantherClient();
        $this->config->setParam('Absences-notifications-agent-par-agent', 0);
        $this->login($admin);

        $crawler = $this->client->request('GET', '/absence/add');

        $agentLabel = $crawler->filter('legend[for=perso_ul1]');
        $this->assertEquals('Agent(s) :', $agentLabel->text(), 'Form agent label incorrect');

        $agentValue = $crawler->filter('ul li.perso_ids_li');
        $this->assertCount(1, $agentValue, 'There should be 1 pre selected agent');
        $this->assertEquals('Administrateur', $agentValue->text(), 'Form agent value incorrect');
        $this->assertSelectorExists('li.perso_ids_li#li1 button.perso-drop', 'There should be a close icon next to the admin name');

        $this->assertSelectorExists('span.pl-icon-add', 'There should be an add icon next to the reason selector');

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

    public function testValidationState():void 
    {

        global $entityManager;
        $admin = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'admin']);
        $bmarley = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'bmarley']);
        $bmarleyId = $bmarley->getId();
        $jdoe = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdoe']);
        $jdoeId = $jdoe->getId();

        $this->setUpPantherClient();
        $this->config->setParam('Absences-notifications-agent-par-agent', 1);

        // Make admin manager of bmarley
        $manager = new Manager();
        $manager->setUser($bmarley);
        $manager->setLevel1(1);
        $admin->addManaged($manager);

        $entityManager->persist($manager);
        $entityManager->flush();

        $this->login($admin);

        $crawler = $this->client->request('GET', '/absence/add');

        $stateValidation = $crawler->filter('#validation-state');
        $this->assertEquals('input', $stateValidation->nodeName(), 'The validation state objetc is not an input');
        $this->assertNotNull($stateValidation->attr('readonly'), 'The validation state object should be readonly');

        // Select managed agent
        $agentOption = $crawler->filterXPath(".//select[@id='perso_ids']//option[@value='" . $bmarleyId  . "']");
        $agentOption->click();

        $stateValidation = $crawler->filter('#validation-state');
        $this->assertEquals('select', $stateValidation->nodeName(), 'The validation state objetc is not a select');

        // Select non managed agent
        $agentOption = $crawler->filterXPath(".//select[@id='perso_ids']//option[@value='" . $jdoeId  . "']");
        $agentOption->click();

        $stateValidation = $crawler->filter('#validation-state');
        $this->assertEquals('input', $stateValidation->nodeName(), 'The validation state object is not an input');
        $this->assertNotNull($stateValidation->attr('readonly'), 'The validation state object should be readonly');

    }

    public function testRecurringAbsence():void 
    {

        global $entityManager;
        $bmarley = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'bmarley']);
        $bmarleyId = $bmarley->getId();

        $this->setUpPantherClient();
        $this->login($bmarley);

        // Create recurring absence
        $crawler = $this->client->request('GET', '/absence/add');

        $form = $crawler->filter('#absence-form')->form();
        $form->setValues(['debut' => '30/06/2026', 'motif' => 'Réunion' ]);
        $form['recurrence-checkbox']->tick();

        $recurrenceForm = $crawler->filter('#recurrence-form')->form();
        $recurrenceForm['recurrence-end']->select('count');
        $recurrenceForm->setValues(['recurrence-interval' => 3, 'recurrence-count' => 4]);

        // Validate recurrence form 
        $this->client->submit($recurrenceForm);
        
        // Validate form 
        $this->client->submit($form);

        $abs2 = $entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $bmarleyId , 'debut' => new DateTime('2026-06-30')]);
        $crawler = $this->client->request('GET', '/absence/'. $abs2->getId());

        // Recurrence disabled but visible
        $recurrenceCheckbox = $crawler->filter('#recurrence-checkbox');
        $this->assertNotNull($recurrenceCheckbox->attr('checked'), 'The recurrence checkbox should be checked');
        $this->assertNotNull($recurrenceCheckbox->attr('disabled'), 'The recurrence checkbox should be disabled');
        
        $recurrenceLink = $crawler->filter('#recurrence-link');
        $this->assertStringContainsString('display:none', $recurrenceLink->attr('style'),'The edition link should not be visible.');

        $recurrenceSummary = $crawler->filter('#recurrence-summary');
        $this->assertNull($recurrenceSummary->attr('style'), 'The recurrence summary should be visible');
        $this->assertEquals('Toutes les 3 semaines, les mardis, 4 fois', $recurrenceSummary->text(), 'Recurrence summary incorrect');

        // Buttons
        $this->assertSelectorExists('input#absence-bouton-suppression');
        $this->assertSelectorExists('input.btn-primary[type=submit]');
        $this->assertSelectorExists('input.btn-secondary[type=button]');

    }

    public function testOtherReasonDisplay():void 
    {

        global $entityManager;
        $bmarley = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'bmarley']);
        $bmarleyId = $bmarley->getId();

        $this->setUpPantherClient();
        $this->login($bmarley);

        $crawler = $this->client->request('GET', "/absence/add");

        $this->assertSelectorNotExists('span.pl-icon-add', 'There should not be an add icon next to the reason selector');

        $form = $crawler->filter('#absence-form')->form();
        $form->setValues(['debut' => '27/10/2026']);

        $otherReasonDiv = $crawler->filter('div.row#motif_autre');
        $this->assertStringContainsString('display:none', $otherReasonDiv->attr('style'),'The row should not be visible.');

        // Select other reason
        $reasonOption = $crawler->filterXPath(".//select[@id='motif']//option[@value='Autre']");
        $reasonOption->click();

        $otherReasonDiv = $crawler->filter('div.row#motif_autre');
        $this->assertStringContainsString('', $otherReasonDiv->attr('style'),'The row should be visible.');

        $otherReasonValue = $crawler->filter('input#motif2');
        $this->assertNotNull($otherReasonValue->attr('required'), 'The other reason input should be required');

        $form->setValues(['motif_autre' => 'Urgence médicale']);

        // Validate form
        $this->client->submit($form);

        $abs3 = $entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $bmarleyId , 'debut' => new DateTime('2026-10-27')]);
        $crawler = $this->client->request('GET', '/absence/'. $abs3->getId());
        
        $this->assertSelectorNotExists('span.pl-icon-add', 'There should not be an add icon next to the reason selector');

        $otherReasonDiv = $crawler->filter('div.row#motif_autre');
        $this->assertNull($otherReasonDiv->attr('style'), 'The row should be visible.');

        $otherReasonLabel = $crawler->filter('label[for=motif2]');
        $this->assertEquals('Motif (autre) :', $otherReasonLabel->text(), 'Form label incorrect');

        $otherReasonValue = $crawler->filter('input#motif2');
        $this->assertNotNull($otherReasonValue->attr('required'), 'The other reason input should be required');
        $this->assertEquals('Urgence médicale', $otherReasonValue->attr('value'), 'Form input incorrect');

    }

    public function testAttachedFiles(): void
    {

        global $entityManager;
        $bmarley = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'bmarley']);
        $bmarleyId = $bmarley->getId();

        $this->setUpPantherClient();
        $this->login($bmarley);

        $crawler = $this->client->request('GET', "/absence/add");

        $this->assertSelectorNotExists('div#attached-documents');
        $this->assertSelectorNotExists('button#add-file');

        $form = $crawler->filter('#absence-form')->form();
        $form->setValues(['debut' => '08/10/2026', 'motif' => 'Grève']);

        // Validate form
        $this->client->submit($form);

        $abs4 = $entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $bmarleyId , 'debut' => new DateTime('2026-10-08')]);

        // Add file to absence
        $doc = new AbsenceDocument();
        $doc->setAbsenceId($abs4->getId())->setFilename('test.txt');
        $entityManager->persist($doc);
        $entityManager->flush();

        $crawler = $this->client->request('GET', '/absence/'. $abs4->getId());

        $this->assertSelectorExists('div#attached-documents');
        $this->assertSelectorExists('button#add-file');

        $fileLink = $crawler->filter('#document_1 a.btn-link[type=link]');
        $this->assertEquals('/absences/document/1', $fileLink->attr('href'),'The file link is incorrect');
        $this->assertEquals('test.txt', $fileLink->text(),'The file name is incorrect');
        
        $this->assertSelectorExists('#document_1 button.btn-link[type=button]');

        $this->config->setParam('Absences-agent-preselection', 1);
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Manager::class);
        $builder->delete(Agent::class);
        $builder->delete(AbsenceDocument::class);
        $builder->delete(Absence::class);

    }
}
