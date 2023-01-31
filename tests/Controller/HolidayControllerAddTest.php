<?php

use App\Model\Agent;
use App\Model\ConfigParam;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class HolidayControllerAddTest extends PLBWebTestCase
{
    protected $builder;
    protected $entityManager;
    protected $CSRFToken;

    protected function setUp(): void
    {
        parent::setUp();

        global $entityManager;

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

    public function testAddWithoutMultiSite()
    {
        $this->setParam('Multisites-nombre', 1);
        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('PlanningHebdo', 0);
        $this->setParam('Conges-Enable', 1);
        $this->setParam('Conges-Mode', 'Heures');
        $this->setParam('Conges-Heures', 0);
        $this->setParam('Conges-validation', 1);
        $this->setParam('Conges-Validation-N2', 0);
        $this->setParam('Conges-Recuperations', 1);
        $this->setParam('Conges-tous', 0);
        $this->setParam('Conges-Rappels', 0);
        $this->setParam('Conges-Rappels-Jours', 14);
        $this->setParam('Conges-demi-journees', 1);
        $this->setParam('Conges-fullday-switching-time', 4);
        $this->setParam('Conges-fullday-reference-time', '');
        $this->setParam('Conges-planningVide', 1);
        $this->setParam('Conges-apresValidation', 1);
        $this->setParam('Recup-Uneparjour', 1);
        $this->setUpPantherClient();

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'sites' => '["1"]',
            'droits' => array(100)
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'sites' => '["1"]',
            'droits' => array(99,100)
        ));
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'sites' => '["1"]',
            'droits' => array(99,100)
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jduponttt', 'nom' => 'Duponttt', 'prenom' => 'Jean', 'temps'=>'',
            'droits' => array(3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301),
            'sites' => '["1"]',
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
        $this->assertTrue(in_array($jdevoe->id(), $agents_list), 'jdevoe');
        $this->assertTrue(in_array($abreton->id(), $agents_list), 'abreton');
        $this->assertTrue(in_array($kboivin->id(), $agents_list), 'kboivin');

        $agent_select = $this->getSelect('perso_ids');
        $agent_select->selectByValue($abreton->id());

        $this->client->getWebDriver()->wait()->until($this->jqueryAjaxFinished());
        $agents_selected = $this->getElementsText('ul#perso_ul1 li');
        $this->assertCount(2, $agents_selected, 'KBoivin and ABreton are selected');

        //test without Conges-demi-journees
        $this->setParam('Conges-Recuperations', 0);
        $this->setParam('Conges-Mode', 'heures');

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filterXPath('//table[@class="tableauFiches"]');

        $this->assertStringContainsString('Journée(s) entière(s)',$result->text(),'test Conges-demi-journees');
        $this->assertStringNotContainsString('Demi-journée(s)',$result->text(),'test Conges-demi-journees');

        //test with Conges-demi-journees
        $this->setParam('Conges-demi-journees', 1);
        $this->setParam('Conges-Mode', 'jours');

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filterXPath('//body');
        $this->assertStringNotContainsString('Journée(s) entière(s)',$result->text(),'test Conges-demi-journees');
        $this->assertStringContainsString('Demi-journée(s)',$result->text(),'test Conges-demi-journees');

        //test with Conges-validation
        $result = $crawler->filterXPath('//body');
        $this->assertStringContainsString('Demandé',$result->text(),'test Conges-validation');

        //test without Conges-validation
        $this->setParam('Conges-validation', 0);

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filterXPath('//body');
        $this->assertStringNotContainsString('Demandé',$result->text(),'test Conges-validation');

        //test Conges-Mode => heures
        $this->setParam('Conges-Mode', 'heures');

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filterXPath('//body');
        $this->assertStringContainsString('Nombre d\'heures',$result->text(),'test Conges-Mode');
        $this->assertStringNotContainsString('Nombre de jours',$result->text(),'test Conges-Mode');

        //test Conges-Mode => jours
        $this->setParam('Conges-Mode', 'jours');

        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filterXPath('//body');
        $this->assertStringContainsString('Nombre de jours',$result->text(),'test Conges-Mode');
        $this->assertStringNotContainsString('Nombre d\'heures',$result->text(),'test Conges-Mode');

        //test with Conges-Heures
        $this->setParam('Conges-Mode', 'heures');
        $this->setParam('Conges-Recuperations', 1);
        $this->setParam('Conges-Heures', 1);

        $crawler = $this->client->request('GET', '/holiday/new');

        $button = $crawler->filterXPath('//input[@class="checkdate"]');
        $button->click();

        $result = $crawler->filterXPath('//body');
        $this->assertStringContainsString('Heure de début',$result->text(),'test Conges-Heures');
        $this->assertStringContainsString('Heure de fin',$result->text(),'test Conges-Heures');

        //test conges anticipation, reliquat and crédit 

        $result = $crawler->filterXPath('//body');
        $this->assertStringContainsString('Reliquat : 3h48',$result->text(),'test Reliquat');
        $this->assertStringContainsString('Crédit de congés : 3h48',$result->text(),'test crédit');
        $this->assertStringContainsString('Solde débiteur : 0h00',$result->text(),'test solde débiteur');

        //test statut with all rights

        $this->setParam('Multisites-nombre', 1);
        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('PlanningHebdo', 0);
        $this->setParam('Conges-Enable', 1);
        $this->setParam('Conges-Mode', 'Heures');
        $this->setParam('Conges-Heures', 0);
        $this->setParam('Conges-validation', 1);
        $this->setParam('Conges-Validation-N2', 0);
        $this->setParam('Conges-Recuperations', 1);
        $this->setParam('Conges-tous', 0);
        $this->setParam('Conges-Rappels', 0);
        $this->setParam('Conges-Rappels-Jours', 14);
        $this->setParam('Conges-demi-journees', 1);
        $this->setParam('Conges-fullday-switching-time', 4);
        $this->setParam('Conges-fullday-reference-time', '');
        $this->setParam('Conges-planningVide', 1);
        $this->setParam('Conges-apresValidation', 1);
        $this->setParam('Recup-Uneparjour', 1);
        $crawler = $this->client->request('GET', '/holiday/new');

        $result = $crawler->filterXPath('//td[@id="validation-statuses"]');
        $this->assertStringContainsString('Demandé',$result->text(),'test statut');
        $this->assertStringContainsString('Acceptée (En attente de validation hiérarchique)',$result->text(),'test statut');
        $this->assertStringContainsString('Refusée (En attente de validation hiérarchique)',$result->text(),'test statut');
        $this->assertStringContainsString('Acceptée',$result->text(),'test statut');
        $this->assertStringContainsString('Refusée',$result->text(),'test statut');
    }

    public function testAddMultisite()
    {
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
        $this->setParam('Conges-tous', 0);
        $this->setParam('Conges-Rappels-Jours', 14);
        $this->setParam('Conges-demi-journees', 1);
        $this->setParam('Conges-fullday-switching-time', 4);
        $this->setParam('Conges-fullday-reference-time', '');
        $this->setParam('Conges-planningVide', 1);
        $this->setParam('Conges-apresValidation', 1);
        $this->setParam('Recup-Uneparjour', 1);

        $this->setParam('Multisites-nombre', 2);
        $this->setParam('Multisites-site1', 'Site N°1');
        $this->setParam('Multisites-site2', 'Site N°2');

        $this->setUpPantherClient();

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John', 'sites' => json_encode(["1"]),
            'droits' => array(100)
        ));
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert', 'sites' => json_encode(["1"]),
            'droits' => array(99,100)
        ));
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel', 'sites' => json_encode(["2"]),
            'droits' => array(99,100)
        ));
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont', 'nom' => 'Dupont', 'prenom' => 'Jean', 'temps'=>'', 'sites' => json_encode(["1", "2"]),
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

        $this->assertStringContainsString('Sites:',$result->text(),'test sites');
        $this->assertStringContainsString('Site N°1',$result->text(),'test sites');
        $this->assertStringContainsString('Site N°2',$result->text(),'test sites');

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(5, $agents_list);
        $this->assertTrue(in_array(0, $agents_list), 'Admin');
        $this->assertTrue(in_array($jdupont->id(), $agents_list), 'jdupont');
        $this->assertTrue(in_array($jdevoe->id(), $agents_list), 'jdevoe');
        $this->assertTrue(in_array($abreton->id(), $agents_list), 'abreton');
        $this->assertTrue(in_array($kboivin->id(), $agents_list), 'kboivin');

        $button = $crawler->filterXPath('//input[@name="selected_sites"]')->eq(0);
        $button->click();

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(5, $agents_list);
        $this->assertTrue(in_array($jdupont->id(), $agents_list), 'jdupont');
        $this->assertTrue(in_array($kboivin->id(), $agents_list), 'kboivin');

    }
}