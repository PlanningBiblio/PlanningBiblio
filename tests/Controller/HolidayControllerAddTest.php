<?php

use App\Entity\Agent;
use App\Entity\Holiday;
use App\Entity\Manager;
use App\Entity\OverTime;
use App\Entity\WorkingHour;
use Doctrine\ORM\EntityManagerInterface;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

/*

5 Configuration modes

   +---+---------------+-----------------------+----------------------+------------------------+
   |   |  Conges-Mode  |  Conges-Recuperation  |     Conges-Heures    |  Conges-demi_journees  |
   +---+---------------+-----------------------+----------------------+------------------------+
   | 1 |     Jours     |      Dissocier (1)    |            x         |            1           |
   | 2 |     Jours     |      Dissocier (1)    |            x         |            0           |
   | 3 |    Heures     |      Dissocier (1)    |      qq Heures (1)   |            x           |
   | 4 |    Heures     |      Dissocier (1)    |  journée entière (0) |            x           |
   | 5 |    Heures     |      Assembler (0)    |            x         |            x           |
   +---+---------------+-----------------------+----------------------+------------------------+

*/

class HolidayControllerAddTest extends PLBWebTestCase
{

    public static function setUpBeforeClass(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Holiday::class);
        $builder->delete(WorkingHour::class);
        $builder->delete(Overtime::class);

        $jdevoe = new Agent();
        $jdevoe->setLogin('jdevoe');
        $jdevoe->setLastName('Devoe');
        $jdevoe->setFirstName('John');
        $jdevoe->setSites(['1']);
        $jdevoe->setACL([99,100]);

        $entityManager->persist($jdevoe);

        $abreton = new Agent();
        $abreton->setLogin('abreton');
        $abreton->setLastName('Breton');
        $abreton->setFirstName('Aubert');
        $abreton->setSites(['1']);
        $abreton->setACL([99,100]);

        $entityManager->persist($abreton);

        $kboivin = new Agent();
        $kboivin->setLogin('kboivin');
        $kboivin->setLastName('Boivin');
        $kboivin->setFirstName('Karel');
        $kboivin->setSites(['2']);
        $kboivin->setACL([99,100]);

        $entityManager->persist($kboivin);

        $jdupont = new Agent();
        $jdupont->setLogin('jdupont');
        $jdupont->setLastName('Dupont');
        $jdupont->setFirstName('Jean');
        $jdupont->setSites(['1', '2']);
        $jdupont->setACL([3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,701,801,802,901,1001,1002,1101,1201,1301]);
        $jdupont->setHolidayAnnualCredit(150);
        $jdupont->setHolidayCredit(40);
        $jdupont->setHolidayRemainder(9);
        $jdupont->setHolidayCompTime(3);

        $entityManager->persist($jdupont);

        $entityManager->flush();

        $holiday = new Holiday();
        $holiday->setUser($jdupont->getId());
        $holiday->setInfo($jdupont->getId());
        $holiday->setActualCredit(150);
        $holiday->setActualRemainder(8);

        $entityManager->persist($holiday);

        $hours = [
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
            ['09:00:00', '', '', '17:00:00'],
        ];

        $workingHours = new WorkingHour;
        $workingHours->setUser($jdupont->getId());
        $workingHours->setStart(new DateTime('2026-01-01'));
        $workingHours->setEnd(new DateTime('2026-12-31'));
        $workingHours->setWorkingHours($hours);
        $workingHours->setBreaktime([1,1,1,1,1,1]);
        $workingHours->setValidLevel2(1);

        $entityManager->persist($workingHours);

        $overTime = new OverTime();
        $overTime->setDate(new DateTime());
        $overTime->setUser($jdupont->getId());
        $overTime->setHours('3');
        $overTime->setEntry($jdupont->getId());

        $entityManager->persist($overTime);

        $entityManager->flush();
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Holiday::class);
        $builder->delete(WorkingHour::class);
        $builder->delete(Overtime::class);
   }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testHolidayDisabled(): void
    {
        $this->config->setParam('Conges-Enable', 0);
        $this->config->setParam('Absences-notifications-agent-par-agent', 0);
        $this->config->setParam('Conges-Recuperations', 1);
        $this->setUpPantherClient();

        $jdupont = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $this->login($jdupont);

        $crawler = $this->client->request('GET', '/holiday/new');
        $this->assertSelectorExists('#acces_refuse');
    }

    public function testHolidayEnabled(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Enable', 1);
        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorNotExists('#acces_refuse');
        $this->assertSelectorTextContains('h3', 'Poser des congés');
        $this->assertSelectorExists('#holiday-form');
    }

    public function testHolidayMultiSite(): void
    {
        $this->setUpPantherClient();

        // Without Multisite
        $this->config->setParam('Multisites-nombre', 1);

        $crawler = $this->client->request('GET', '/holiday/new');
        $this->assertSelectorExists('#holiday-form');
        $this->assertSelectorNotExists('#sites-selection', 'There site selection div should not be present');

        // With Multisite
        $this->config->setParam('Multisites-nombre', 2);
        $this->config->setParam('Multisites-site1', 'Site N°1');
        $this->config->setParam('Multisites-site2', 'Site N°2');

        $jdupont = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $jdevoe = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdevoe']);
        $abreton = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'abreton']);
        $kboivin = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'kboivin']);

        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorExists('#sites-selection', 'There site selection div should be present');
        $sites = $crawler->filter('[id^="site_"]');
        $this->assertCount(2, $sites);

        $result = $crawler->filterXPath('//body');
        $this->assertStringContainsString('Sites :',$result->text('Node does not exist', true),'test sites');
        $this->assertStringContainsString('Site N°1',$result->text('Node does not exist', true),'test sites');
        $this->assertStringContainsString('Site N°2',$result->text('Node does not exist', true),'test sites');

        // Deselect Jean Dupont
        $closeIcon = $crawler->filter("#li" . $jdupont->getId()  . " button.perso-drop");
        $closeIcon->click();

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(5, $agents_list);
        $this->assertTrue(in_array(0, $agents_list), 'Admin');
        $this->assertTrue(in_array($jdupont->getId(), $agents_list), 'jdupont');
        $this->assertTrue(in_array($jdevoe->getId(), $agents_list), 'jdevoe');
        $this->assertTrue(in_array($abreton->getId(), $agents_list), 'abreton');
        $this->assertTrue(in_array($kboivin->getId(), $agents_list), 'kboivin');

        // Untick Site n°1
        $button = $crawler->filterXPath('//input[@name="selected_sites"]')->eq(0);
        $button->click();

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(5, $agents_list);
        $hiddenAgents = $crawler->filter('#perso_ids option[style="display: none;"]');
        $this->assertCount(2, $hiddenAgents);
        $this->assertEquals($abreton->getId(), $hiddenAgents->attr('value'), 'Breton Aubert should not be selectable');
        $this->assertEquals($jdevoe->getId(), $hiddenAgents->eq(1)->attr('value'), 'Devoe John should not be selectable');
    }

    public function testHolidayMultipleAgent(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Mode', 'jours');

        $jdevoe = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdevoe']);
        $abreton = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'abreton']);
        $kboivin = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'kboivin']);

        $crawler = $this->client->request('GET', '/holiday/new');

        $agentLabel = $crawler->filter('legend[for=perso_ul1]');
        $this->assertEquals('Agent(s) :', $agentLabel->text(), 'Form agent label incorrect');

        $selectedAgents = $crawler->filter('ul#perso_ul1 li');
        $this->assertCount(1, $selectedAgents, 'There should be only one selected agent');
        $this->assertEquals('Dupont Jean', $selectedAgents->text(), 'Dupont Jean should be selected');
        $this->assertSelectorExists('li.perso_ids_li button.perso-drop', 'There should be a close icon next to the agent name');

        $agentsOptions = $this->getSelectValues('perso_ids');
        $this->assertCount(5, $agentsOptions);
        $this->assertTrue(in_array(0, $agentsOptions));
        $this->assertTrue(in_array($jdevoe->getId(), $agentsOptions));
        $this->assertTrue(in_array($abreton->getId(), $agentsOptions));
        $this->assertTrue(in_array($kboivin->getId(), $agentsOptions));
        $this->assertFalse(in_array('tous', $agentsOptions));

        // Select second agent
        $agent_select = $this->getSelect('perso_ids');
        $agent_select->selectByValue($kboivin->getId());

        $selectedAgents = $crawler->filter('ul#perso_ul1 li');
        $this->assertCount(2, $selectedAgents, 'There should be two selected agents');
        $this->assertEquals('Boivin Karel', $selectedAgents->text(), 'Boivin Karel should be selected');

        // Elements that should not be visible when multiple agents are selected
        $this->assertSelectorIsNotVisible('#nbJours');
        $this->assertSelectorIsNotVisible('#terms');
        $this->assertSelectorIsNotVisible('#holiday_balance');
        $this->assertSelectorIsNotVisible('#holiday_credit');
        $this->assertSelectorIsNotVisible('#holiday_debit');

        $this->config->setParam('Conges-tous', 1);
        $crawler = $this->client->request('GET', '/holiday/new');

        $agentsOptions = $this->getSelectValues('perso_ids');
        $this->assertCount(6, $agentsOptions);
        $this->assertTrue(in_array('tous', $agentsOptions));

        // Select all agents
        $agent_select = $this->getSelect('perso_ids');
        $agent_select->selectByValue('tous');

        $selectedAgents = $crawler->filter('ul#perso_ul1 li');
        $this->assertCount(4, $selectedAgents, 'All agents should be selected');
    }

    public function testHolidayUniqueAgent(): void
    {
        $this->setUpPantherClient();
        $jdevoe = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdevoe']);

        $this->login($jdevoe);
        $crawler = $this->client->request('GET', '/holiday/new');

        $agentLabel = $crawler->filter('label[for=perso_id]');
        $this->assertEquals('Agent :', $agentLabel->text(), 'Form agent label incorrect');

        $this->assertSelectorNotExists('#sites-selection');
        $this->assertSelectorNotExists('ul#perso_ul1 li');
        $this->assertSelectorExists('input#agent');

        $agent = $crawler->filter('input#agent');
        $this->assertEquals('Devoe John', $agent->attr('value'), 'Form agent value incorrect');
    }

    public function testHolidayConfig1(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Mode', 'jours');
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-demi-journees', 1);
        $this->config->setParam('Conges-fullday-switching-time', 4);
        $this->config->setParam('Conges-fullday-reference-time', '');

        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorExists('input[name=halfday]');
        $halfdayLabel = $crawler->filter('label[for=halfday]');
        $this->assertEquals('Demi-journée(s) :', $halfdayLabel->text(),'The label for the halfay checkbox is incorect');
        $this->assertSelectorNotExists('input[name=allday]');

        $this->assertSelectorIsNotVisible('select[name=start_halfday]');
        $this->assertSelectorIsNotVisible('select[name=end_halfday]');

        $form = $crawler->filter('#holiday-form')->form();
        $form['halfday']->tick();
        $form->setValues(['debut' => '30/06/2026']);

        $this->assertSelectorIsVisible('select[name=start_halfday]');
        $this->assertSelectorIsVisible('select[name=end_halfday]');

        $halfdayOptions = $this->getSelectValues('start_halfday');
        $this->assertCount(3, $halfdayOptions);
        $this->assertTrue(in_array('fullday', $halfdayOptions));
        $this->assertTrue(in_array('morning', $halfdayOptions));
        $this->assertTrue(in_array('afternoon', $halfdayOptions));

        $crawler->filter('input[type=submit]')->click();

        $endDate = $form->getValues()['fin'];
        $this->assertEquals('30/06/2026', $endDate,'The end-date value should be the same as the start-date');

        $this->getSelect('start_halfday')->selectByValue('morning');
        $endHalfdayOption = $crawler->filter('#end_halfday')->attr('value');
        $this->assertEquals('morning', $endHalfdayOption,'The end halfday selected value should be the same as the start halfday');

        $joursLabel = $crawler->filter('label[for=nbJours]');
        $this->assertStringContainsString('Nombre de jours',$joursLabel->text(),'The label is incorrect');
        $this->assertSelectorNotExists('label[for=nbHeures]');
    }

    public function testHolidayConfig2(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Mode', 'jours');
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-demi-journees', 0);

        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorNotExists('input[name=allday]');
        $this->assertSelectorNotExists('input[name=halfday]');
    }

    public function testHolidayConfig3(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Mode', 'heures');
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-Heures', 1);

        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorExists('input[name=allday]');
        $alldayLabel = $crawler->filter('label[for=allday]');
        $this->assertStringContainsString('Journée(s) entière(s)', $alldayLabel->text(),'The label for the allday checkbox is incorect');
        $this->assertSelectorNotExists('input[name=halfday]');

        $this->assertSelectorIsNotVisible('#hre_debut');
        $this->assertSelectorIsNotVisible('#hre_fin');

        $form = $crawler->filter('#holiday-form')->form();
        $form['allday']->untick();

        $this->assertSelectorIsVisible('#hre_debut');
        $this->assertSelectorIsVisible('#hre_fin');

        $debutLabel = $crawler->filter('label[for=hre_debut_select]');
        $this->assertStringContainsString('Heure de début',$debutLabel->text(),'The label is incorrect');
        $finLabel = $crawler->filter('label[for=hre_fin_select]');
        $this->assertStringContainsString('Heure de fin',$finLabel->text(),'The label is incorrect');

        $heuresLabel = $crawler->filter('label[for=nbHeures]');
        $this->assertStringContainsString('Nombre d\'heures', $heuresLabel->text(),'The label is incorrect');
        $this->assertSelectorNotExists('label[for=nbJours]');
    }

    public function testHolidayConfig4(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Mode', 'heures');
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-Heures', 0);
        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorNotExists('input[name=allday]');
        $this->assertSelectorNotExists('input[name=halfday]');

        $this->assertSelectorNotExists('input#balance_before');
        $this->assertSelectorNotExists('input#balance2_before');
    }

    public function testHolidayConfig5(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Mode', 'heures');
        $this->config->setParam('Conges-Recuperations', 0);

        $jdupont = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $this->login($jdupont);
        $crawler = $this->client->request('GET', '/comptime/add');

        $this->assertSelectorExists('#acces_refuse');

        $crawler = $this->client->request('GET', '/holiday/new');

        $agentLabel = $crawler->filter('label[for=perso_ids]');
        $this->assertEquals('Agent :', $agentLabel->text(), 'Form agent label incorrect');

        $this->assertSelectorNotExists('ul#perso_ul1 li');
        $this->assertSelectorExists('select#perso_ids');

        $selectedAgent = $crawler->filter('select#perso_ids option[selected]');
        $this->assertEquals('Dupont Jean', $selectedAgent->text(), 'Selected agent value incorrect');

        $this->assertSelectorExists('input[name=allday]');
        $this->assertSelectorNotExists('input[name=halfday]');

        $this->assertSelectorExists('#terms-select');
        $termsOptions = $this->getSelectValues('terms-select');
        $this->assertCount(2, $termsOptions);
        $this->assertTrue(in_array('recuperation', $termsOptions));
        $this->assertTrue(in_array('credit', $termsOptions));

        $this->assertSelectorExists('input#balance_before');
        $this->assertSelectorExists('input#balance2_before');
    }

    public function testHolidayValidation(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-validation', 1);
        $this->config->setParam('Conges-Validation-N2', 0);
        $crawler = $this->client->request('GET', '/comptime/add');

        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());
        $validationState = $crawler->filter('select#validation-state option[selected]');
        $this->assertEquals('Demandée',$validationState->text(),'The selected validation state is incorrect');

        // Only level 1 validation rights on Holiday
        $validationOptions = $crawler->filter('select#validation-state option');
        $this->assertCount(3, $validationOptions);
        $this->assertStringContainsString('Demandée', $validationOptions->text(), 'test statut');
        $this->assertStringContainsString('Acceptée (En attente de validation hiérarchique)', $validationOptions->eq(1)->text(), 'test statut');
        $this->assertStringContainsString('Refusée (En attente de validation hiérarchique)', $validationOptions->eq(2)->text(), 'test statut');

        $jdupont = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $abreton = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'abreton']);
        $droits = array(3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,701,801,802,901,1001,1002,1101,1201,1301);

        // Add level 2 validation rights on Holiday
        array_push($droits, 601, 602);
        $jdupont->setACL($droits);

        $this->login($jdupont);
        $crawler = $this->client->request('GET', '/holiday/new');

        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());
        $validationState = $crawler->filter('select#validation-state option[selected]');
        $this->assertEquals('Demandée',$validationState->text(),'The selected validation state is incorrect');
        $validationOptions = $crawler->filter('select#validation-state option');
        $this->assertCount(5, $validationOptions);
        $this->assertStringContainsString('Demandée', $validationOptions->text(), 'test statut');
        $this->assertStringContainsString('Acceptée (En attente de validation hiérarchique)', $validationOptions->eq(1)->text(), 'test statut');
        $this->assertStringContainsString('Refusée (En attente de validation hiérarchique)', $validationOptions->eq(2)->text(), 'test statut');
        $this->assertStringContainsString('Acceptée', $validationOptions->eq(3)->text(), 'test statut');
        $this->assertStringContainsString('Refusée', $validationOptions->eq(4)->text(), 'test statut');

        // With Absences-notifications-agent-par-agent
        $this->config->setParam('Absences-notifications-agent-par-agent', 1);

        // Make jdupont manager of abreton
        $manager = new Manager();
        $manager->setUser($abreton);
        $manager->setLevel2(1);
        $jdupont->addManaged($manager);

        $this->login($jdupont);
        $crawler = $this->client->request('GET', '/holiday/new');

        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());
        $validationState = $crawler->filter('#validation-state');
        $this->assertEquals('input', $validationState->nodeName(), 'The validation state objetc is not an input');
        $this->assertNotNull($validationState->attr('readonly'), 'The validation state object should be readonly');
        $this->assertEquals('Demandé',$validationState->attr('value'),'The validation state value is incorrect');

        $agentOption = $crawler->filterXPath(".//select[@id='perso_ids']//option[@value='" . $abreton->getId()  . "']");
        $agentOption->click();

        $crawler = $this->client->refreshCrawler();
        sleep(1);

        $validationState = $crawler->filter('#validation-state');
        $this->assertEquals('input', $validationState->nodeName(), 'The validation state objetc is not an input');

        $closeIcon = $crawler->filter("#li" . $jdupont->getId()  . " button.perso-drop");
        $closeIcon->click();

        $crawler = $this->client->refreshCrawler();
        sleep(1);

        $validationState = $crawler->filter('#validation-state');
        $this->assertEquals('select', $validationState->nodeName(), 'The validation state objetc is not a select');
        $validationOptions = $crawler->filter('select#validation-state option');
        $this->assertCount(5, $validationOptions);
    }

     public function testHolidayNoValidation(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-validation', 0);
        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorExists('#holiday-form');
        $this->assertSelectorNotExists('#validation-state');
    }

    public function testHolidayValues() : void
    {
        $this->setUpPantherClient();
        $crawler = $this->client->request('GET', '/holiday/new');

        $jdupont = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);

        $terms = $crawler->filter('#terms');

        $this->assertStringContainsString('Ces heures seront débitées sur le réliquat de l\'année précédente puis sur les crédits de congés de l\'année en cours.',$terms->text(),'The term value is incorrect');

        // Hours
        $balance = $crawler->filter('input#holiday_balance');
        $this->assertStringContainsString('9h00',$balance->attr('value'),'The balance value is incorrect');
        $credit = $crawler->filter('input#holiday_credit');
        $this->assertStringContainsString('40h00',$credit->attr('value'),'The credit value is incorrect');
        $debit = $crawler->filter('input#holiday_debit');
        $this->assertStringContainsString('0h00',$debit->attr('value'),'The debit value is incorrect');

        $this->config->setParam('Conges-Mode', 'jours');
        $crawler = $this->client->request('GET', '/holiday/new');

        // Days
        $balance = $crawler->filter('input#holiday_balance');
        $this->assertStringContainsString('1.5 jours',$balance->attr('value'),'The balance value is incorrect');
        $credit = $crawler->filter('input#holiday_credit');
        $this->assertStringContainsString('5.5 jours',$credit->attr('value'),'The credit value is incorrect');
        $debit = $crawler->filter('input#holiday_debit');
        $this->assertStringContainsString('0 jour',$debit->attr('value'),'The debit value is incorrect');

        $form = $crawler->filter('#holiday-form')->form();
        $form->setValues(['debut' => '30/06/2026', 'fin' => '06/07/2026']);
        $crawler->filter('#nbJours')->click();

        $heures = $crawler->filter('input[name=heures]');
        $this->assertEquals(42, $heures->attr('value'),'The number of hours is incorrect');

        // Days after deduction
        $balance = $crawler->filter('#reliquat4');
        $this->assertStringContainsString('0 jour',$balance->text(),'The balance value is incorrect');
        $credit = $crawler->filter('#credit4');
        $this->assertStringContainsString('1 jour',$credit->text(),'The credit value is incorrect');
        $debit = $crawler->filter('#anticipation4');
        $this->assertStringContainsString('0 jour',$debit->text(),'The debit value is incorrect');

        // Validate form
        $this->client->submit($form);

        $crawler = $this->client->request('GET', '/holiday/new');

        $terms = $crawler->filter('#terms');
        $this->assertStringContainsString('Ces heures seront débitées sur les crédits de congés de l\'année en cours.',$terms->text(),'The term value is incorrect');

        // Days
        $balance = $crawler->filter('input#holiday_balance');
        $this->assertStringContainsString('0 jour',$balance->attr('value'),'The balance value is incorrect');
        $credit = $crawler->filter('input#holiday_credit');
        $this->assertStringContainsString('1 jour',$credit->attr('value'),'The credit value is incorrect');
        $debit = $crawler->filter('input#holiday_debit');
        $this->assertStringContainsString('0 jour',$debit->attr('value'),'The debit value is incorrect');
    }

    public function testComptime(): void
    {
        $this->setUpPantherClient();
        $this->config->setParam('Conges-Recuperations', 1);

        $crawler = $this->client->request('GET', '/comptime/add');

        $agentLabel = $crawler->filter('label[for=perso_id]');
        $this->assertEquals('Agent :', $agentLabel->text(), 'Form agent label incorrect');

        $this->assertSelectorNotExists('ul#perso_ul1 li');
        $this->assertSelectorExists('select#perso_id');
        $this->assertSelectorExists('input[name=allday]');
        $this->assertSelectorExists('input#nbHeures');

        $terms = $crawler->filter('#terms');
        $this->assertStringContainsString('Ces heures seront débitées sur les crédits de récupérations.',$terms->text(),'The term value is incorrect');

        $balance = $crawler->filter('input#balance_before');
        $this->assertStringContainsString('3h00',$balance->attr('value'),'The balance value is incorrect');
        $balance2 = $crawler->filter('input#balance2_before');
        $this->assertStringContainsString('6h00',$balance2->attr('value'),'The balance2 value is incorrect');

        $this->assertSelectorNotExists('#holiday_balance');
        $this->assertSelectorNotExists('#holiday_credit');
        $this->assertSelectorNotExists('#holiday_debit');
    }
}
