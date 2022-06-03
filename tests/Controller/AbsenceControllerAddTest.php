<?php

use App\Model\Agent;
use App\Model\Manager;
use App\Model\ConfigParam;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AbsenceControllerAddTest extends PLBWebTestCase
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

    public function testAdd()
    {
        $this->setParam('Absences-notifications-agent-par-agent', 0);
        $this->setParam('Multisites-nombre', 1);

        $this->setUpPantherClient();

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

        // Login with agent having rights for absences
        $this->login($kboivin);

        $this->client->request('GET', '/absence/add');

        $this->assertSelectorTextContains('h3', 'Ajouter une absence');

        $agents_selected = $this->getElementsText('ul#perso_ul1 li');
        $this->assertCount(1, $agents_selected, 'KBoivin is the only default selected agent');
        $this->assertTrue(in_array('Boivin Karel', $agents_selected), 'KBoivin is selected');

        $this->assertSelectorExists('select#perso_ids');

        $agents_list = $this->getSelectValues('perso_ids');
        $this->assertCount(5, $agents_list);

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
    }
}
