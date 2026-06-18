<?php

use App\Entity\Agent;
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
        $this->config->setParam('Conges-Mode', 'Heures');
        $this->config->setParam('Conges-Heures', 0);
        $this->config->setParam('Conges-validation', 1);
        $this->config->setParam('Conges-Validation-N2', 0);
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-tous', 0);
        $this->config->setParam('Conges-Rappels', 0);
        $this->config->setParam('Conges-Rappels-Jours', 14);
        $this->config->setParam('Conges-demi-journees', 1);
        $this->config->setParam('Conges-fullday-switching-time', 4);
        $this->config->setParam('Conges-fullday-reference-time', '');
        $this->config->setParam('Conges-planningVide', 1);
        $this->config->setParam('Conges-apresValidation', 1);
        $this->config->setParam('Recup-Uneparjour', 1);
        $this->setUpPantherClient();

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
            'login' => 'jduponttt', 'nom' => 'Duponttt', 'prenom' => 'Jean', 'temps'=> [],
            'droits' => array(3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301),
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

        //test multi agents
        $agents_selected = $this->getElementsText('ul#perso_ul1 li');
        $this->assertCount(1, $agents_selected, 'jduponttt is the only default selected agent');
        $this->assertTrue(in_array('Duponttt Jean', $agents_selected), 'KBoivin is selected');

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(6, $agents_list);
        $this->assertTrue(in_array(0, $agents_list), '-- Ajoutez un agent --');
        $this->assertTrue(in_array(1, $agents_list), 'Admin');
        $this->assertTrue(in_array($jdevoe->getId(), $agents_list), 'jdevoe');
        $this->assertTrue(in_array($abreton->getId(), $agents_list), 'abreton');
        $this->assertTrue(in_array($kboivin->getId(), $agents_list), 'kboivin');

        $agent_select = $this->getSelect('perso_ids');
        $agent_select->selectByValue($abreton->getId());

        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());
        $agents_selected = $this->getElementsText('ul#perso_ul1 li');
        $this->assertCount(2, $agents_selected, 'KBoivin and ABreton are selected');

        //test without Conges-demi-journees
        $this->config->setParam('Conges-Recuperations', 0);
        $this->config->setParam('Conges-Mode', 'heures');

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filter('label[for=allday]');
        $this->assertStringContainsString('Journée(s) entière(s) :',$result->text('Node does not exist', true),'test Conges-demi-journees');
        $this->assertSelectorNotExists('label[for=halfday]');

        //test with Conges-demi-journees
        $this->config->setParam('Conges-demi-journees', 1);
        $this->config->setParam('Conges-Mode', 'jours');

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filter('label[for=halfday]');
        $this->assertStringContainsString('Demi-journée(s) :',$result->text('Node does not exist', true),'test Conges-demi-journees');
        $this->assertSelectorNotExists('label[for=allday]');

        //test with Conges-validation
        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());
        $result = $crawler->filter('select#validation-state option[selected]');
        $this->assertStringContainsString('Demandée',$result->text(),'test Conges-validation');

        //test without Conges-validation
        $this->config->setParam('Conges-validation', 0);

        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorNotExists('#validation-state');

        //test Conges-Mode => heures
        $this->config->setParam('Conges-Mode', 'heures');

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filter('label[for=nbHeures]');
        $this->assertStringContainsString('Nombre d\'heures :',$result->text('Node does not exist', true),'test Conges-Mode');
        $this->assertSelectorNotExists('label[for=nbJours]');

        //test Conges-Mode => jours
        $this->config->setParam('Conges-Mode', 'jours');

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filter('label[for=nbJours]');
        $this->assertStringContainsString('Nombre de jours',$result->text('Node does not exist', true),'test Conges-Mode');
        $this->assertSelectorNotExists('label[for=nbHeures]');

        //test with Conges-Heures
        $this->config->setParam('Conges-Mode', 'heures');
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-Heures', 1);

        $crawler = $this->client->request('GET', '/holiday/new');

        $this->assertSelectorIsNotVisible('div#hre_debut');
        $this->assertSelectorIsNotVisible('div#hre_fin');

        $form = $crawler->filter('#holiday-form')->form();
        $form['allday']->untick();

        $this->assertSelectorIsVisible('div#hre_debut');
        $this->assertSelectorIsVisible('div#hre_fin');

        $result = $crawler->filter('label[for=hre_debut_select]');
        $this->assertStringContainsString('Heure de début',$result->text('Node does not exist', true),'test Conges-Heures');
        $result = $crawler->filter('label[for=hre_fin_select]');
        $this->assertStringContainsString('Heure de fin',$result->text('Node does not exist', true),'test Conges-Heures');

        //test conges anticipation, reliquat and crédit 

        $result = $crawler->filter('input#holiday_balance');
        $this->assertStringContainsString('3h48',$result->attr('value'),'test Reliquat');
        $result = $crawler->filter('input#holiday_credit');
        $this->assertStringContainsString('3h48',$result->attr('value'),'test crédit');
        $result = $crawler->filter('input#holiday_debit');
        $this->assertStringContainsString('0h00',$result->attr('value'),'test solde débiteur');

        //test statut with all rights

        $this->config->setParam('Multisites-nombre', 1);
        $this->config->setParam('Absences-notifications-agent-par-agent', 0);
        $this->config->setParam('PlanningHebdo', 0);
        $this->config->setParam('Conges-Enable', 1);
        $this->config->setParam('Conges-Mode', 'Heures');
        $this->config->setParam('Conges-Heures', 0);
        $this->config->setParam('Conges-validation', 1);
        $this->config->setParam('Conges-Validation-N2', 0);
        $this->config->setParam('Conges-Recuperations', 1);
        $this->config->setParam('Conges-tous', 0);
        $this->config->setParam('Conges-Rappels', 0);
        $this->config->setParam('Conges-Rappels-Jours', 14);
        $this->config->setParam('Conges-demi-journees', 1);
        $this->config->setParam('Conges-fullday-switching-time', 4);
        $this->config->setParam('Conges-fullday-reference-time', '');
        $this->config->setParam('Conges-planningVide', 1);
        $this->config->setParam('Conges-apresValidation', 1);
        $this->config->setParam('Recup-Uneparjour', 1);
        $crawler = $this->client->request('GET', '/holiday/new');

        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());
        $result = $crawler->filter('select#validation-state option');
        $this->assertStringContainsString('Demandée', $result->text(), 'test statut');
        $this->assertStringContainsString('Acceptée (En attente de validation hiérarchique)', $result->eq(1)->text(), 'test statut');
        $this->assertStringContainsString('Refusée (En attente de validation hiérarchique)', $result->eq(2)->text(), 'test statut');
        $this->assertStringContainsString('Acceptée', $result->eq(3)->text(), 'test statut');
        $this->assertStringContainsString('Refusée', $result->eq(4)->text(), 'test statut');
    }

    public function testAddMultisite(): void
    {
        $this->config->setParam('Multisites-nombre', 1);
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

        $this->assertStringContainsString('Sites :',$result->text('Node does not exist', true),'test sites');
        $this->assertStringContainsString('Site N°1',$result->text('Node does not exist', true),'test sites');
        $this->assertStringContainsString('Site N°2',$result->text('Node does not exist', true),'test sites');

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(5, $agents_list);
        $this->assertTrue(in_array(0, $agents_list), 'Admin');
        $this->assertTrue(in_array($jdupont->getId(), $agents_list), 'jdupont');
        $this->assertTrue(in_array($jdevoe->getId(), $agents_list), 'jdevoe');
        $this->assertTrue(in_array($abreton->getId(), $agents_list), 'abreton');
        $this->assertTrue(in_array($kboivin->getId(), $agents_list), 'kboivin');

        $button = $crawler->filterXPath('//input[@name="selected_sites"]')->eq(0);
        $button->click();

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(5, $agents_list);
        $this->assertTrue(in_array($jdupont->getId(), $agents_list), 'jdupont');
        $this->assertTrue(in_array($kboivin->getId(), $agents_list), 'kboivin');

    }
}
