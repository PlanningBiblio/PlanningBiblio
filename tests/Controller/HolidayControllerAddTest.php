<?php

use App\Entity\Agent;
use App\Entity\Manager;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class HolidayControllerAddTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testAddWithoutMultiSite(): void
    {
        $this->config->setParam('Multisites-nombre', 1);
        $this->config->setParam('Absences-notifications-agent-par-agent', 0);
        $this->config->setParam('PlanningHebdo', 0);
        $this->config->setParam('Conges-Enable', 1);
        $this->config->setParam('Conges-Mode', 'heures');
        $this->config->setParam('Conges-Heures', 1);
        $this->config->setParam('Conges-validation', 1);
        $this->config->setParam('Conges-Validation-N2', 0);
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-tous', 0);
        $this->config->setParam('Conges-Rappels', 0);
        $this->config->setParam('Conges-Rappels-Jours', 14);
        $this->config->setParam('Conges-fullday-switching-time', 4);
        $this->config->setParam('Conges-fullday-reference-time', '');
        $this->config->setParam('Conges-planningVide', 1);
        $this->config->setParam('Conges-apresValidation', 1);
        $this->config->setParam('Recup-Uneparjour', 1);
        $this->setUpPantherClient();

        $droits = array(3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,701,801,802,901,1001,1002,1101,1201,1301);

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'sites' => ["1"],
            'droits' => array(100)
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'sites' => ["1"],
            'droits' => array(99,100)
        ));
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'sites' => ["1"],
            'droits' => array(99,100)
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'temps'=> [],
            'droits' => $droits,
            'sites' => ["1"],
            'conges_credit' => "3.8",
            'conges_reliquat' => "3.8",
            'conges_anticipation' => "0",
            'comp_time' => "1.9",
            'conges_annuel' => "3.8",
        ));

        // Login with agent having rights for conges
        $this->login($jdupont);

        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorTextContains('h3', 'Poser des congés');

        $this->assertSelectorNotExists('#sites-selection', 'There site selection div should not be present');

        // Multiple agents test
        $selectedAgents = $crawler->filter('ul#perso_ul1 li');
        $this->assertCount(1, $selectedAgents, 'There should be only one selected agent');
        $this->assertEquals('Dupont Jean', $selectedAgents->text(), 'Dupont Jean should be selected');
        $this->assertSelectorExists('li.perso_ids_li button.perso-drop', 'There should be a close icon next to the agent name');

        $agentsOptions = $this->getSelectValues('perso_ids');
        $this->assertCount(6, $agentsOptions);
        $this->assertTrue(in_array(0, $agentsOptions));
        $this->assertTrue(in_array(1, $agentsOptions));
        $this->assertTrue(in_array($jdevoe->getId(), $agentsOptions));
        $this->assertTrue(in_array($abreton->getId(), $agentsOptions));
        $this->assertTrue(in_array($kboivin->getId(), $agentsOptions));

        // Select second agent
        $agent_select = $this->getSelect('perso_ids');
        $agent_select->selectByValue($kboivin->getId());

        $selectedAgents = $crawler->filter('ul#perso_ul1 li');
        $this->assertCount(2, $selectedAgents, 'There should be two selected agents');
        $this->assertEquals('Boivin Karel', $selectedAgents->text(), 'Boivin Karel should be selected');

        // Elements that should not be visible when multiple agents are selected
        $this->assertSelectorIsNotVisible('#nbHeures');
        $this->assertSelectorIsNotVisible('#terms');
        $this->assertSelectorIsNotVisible('#holiday_balance');
        $this->assertSelectorIsNotVisible('#holiday_credit');
        $this->assertSelectorIsNotVisible('#holiday_debit');

        // Test without Conges-demi-journees
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

        // Test with Conges-demi-journees
        $this->config->setParam('Conges-Mode', 'jours');
        $this->config->setParam('Conges-demi-journees', 1);

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

        // Test with Conges-validation
        $validationState = $crawler->filter('select#validation-state option[selected]');
        $this->assertEquals('Demandée',$validationState->text(),'The selected validation state is incorrect');
        $validationOptions = $crawler->filter('select#validation-state option');
        $this->assertCount(3, $validationOptions);
        $this->assertStringContainsString('Demandée', $validationOptions->text(), 'test statut');
        $this->assertStringContainsString('Acceptée (En attente de validation hiérarchique)', $validationOptions->eq(1)->text(), 'test statut');
        $this->assertStringContainsString('Refusée (En attente de validation hiérarchique)', $validationOptions->eq(2)->text(), 'test statut');

        // Add validation level 2 rights on Holiday
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

        // Test with Conge-validation and Absences-notifications-agent-par-agent
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

        $validationState = $crawler->filter('#validation-state');
        $this->assertEquals('input', $validationState->nodeName(), 'The validation state objetc is not an input');

        $closeIcon = $crawler->filter("#li" . $jdupont->getId()  . " button.perso-drop");
        $closeIcon->click();

        $validationState = $crawler->filter('#validation-state');
        $this->assertEquals('select', $validationState->nodeName(), 'The validation state objetc is not a select');
        $validationOptions = $crawler->filter('select#validation-state option');
        $this->assertCount(5, $validationOptions);

        // Test without Conges-validation
        $this->config->setParam('Conges-validation', 0);
        $crawler = $this->client->request('GET', '/holiday/new');
        $this->assertSelectorNotExists('#validation-state');

        //test Conges-Mode => jours
        $crawler = $this->client->request('GET', '/holiday/new');

        $JoursLabel = $crawler->filter('label[for=nbJours]');
        $this->assertStringContainsString('Nombre de jours',$JoursLabel->text(),'The label is incorrect');
        $this->assertSelectorNotExists('label[for=nbHeures]');

        // Test Conges-Mode => heures
        $this->config->setParam('Conges-Mode', 'heures');

        $crawler = $this->client->request('GET', '/holiday/new');

        $HeuresLabel = $crawler->filter('label[for=nbHeures]');
        $this->assertStringContainsString('Nombre d\'heures', $HeuresLabel->text(),'The label is incorrect');
        $this->assertSelectorNotExists('label[for=nbJours]');

        // Test conges anticipation, reliquat and crédit 

        $terms = $crawler->filter('#terms');
        $this->assertStringContainsString('Ces heures seront débitées sur le réliquat de l\'année précédente puis sur les crédits de congés de l\'année en cours.',$terms->text(),'The term value is incorrect');
        $balance = $crawler->filter('input#holiday_balance');
        $this->assertStringContainsString('3h48',$balance->attr('value'),'The balance value is incorrect');
        $credit = $crawler->filter('input#holiday_credit');
        $this->assertStringContainsString('3h48',$credit->attr('value'),'The credit value is incorrect');
        $debit = $crawler->filter('input#holiday_debit');
        $this->assertStringContainsString('0h00',$debit->attr('value'),'The debit value is incorrect');
    }

    public function testAddMultisite(): void
    {
        $this->config->setParam('Absences-notifications-agent-par-agent', 0);
        $this->config->setParam('PlanningHebdo', 0);
        $this->config->setParam('Conges-Enable', 1);
        $this->config->setParam('Conges-Mode', 'heures');
        $this->config->setParam('Conges-Heures', 0);
        $this->config->setParam('Conges-validation', 1);
        $this->config->setParam('Conges-Validation-N2', 0);
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-tous', 0);
        $this->config->setParam('Conges-tous', 0);
        $this->config->setParam('Conges-Rappels-Jours', 14);
        $this->config->setParam('Conges-demi-journees', 1);
        $this->config->setParam('Conges-fullday-switching-time', 4);
        $this->config->setParam('Conges-fullday-reference-time', '');
        $this->config->setParam('Conges-planningVide', 1);
        $this->config->setParam('Conges-apresValidation', 1);
        $this->config->setParam('Recup-Uneparjour', 1);

        $this->config->setParam('Multisites-nombre', 2);
        $this->config->setParam('Multisites-site1', 'Site N°1');
        $this->config->setParam('Multisites-site2', 'Site N°2');

        $this->setUpPantherClient();

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'sites' => ["1"],
            'droits' => array(100)
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'sites' => ["1"],
            'droits' => array(99,100)
        ));
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'sites' => ["2"],
            'droits' => array(99,100)
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'temps'=> [], 'sites' => ["1", "2"],
            'droits' => array(3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301),
            'conges_credit' => 30,
            'conges_reliquat' => 10,
            'conges_anticipation' => 5
        ));

        // Login with agent having rights for conges
        $this->login($jdupont);

        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorTextContains('h3', 'Poser des congés');

        $result = $crawler->filterXPath('//body');

        $this->assertSelectorExists('#sites-selection', 'There site selection div should be present');
        $sites = $crawler->filter('[id^="site_"]');
        $this->assertCount(2, $sites);
        
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
}
