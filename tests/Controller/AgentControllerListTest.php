<?php

use App\Model\Agent;
use App\Model\Manager;
use App\Model\Site;
use App\Model\ConfigParam;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AgentControllerListTest extends PLBWebTestCase
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

    public function testListAgent()
    {
        $this->builder->delete(Site::class);
        $this->builder->delete(Agent::class);
        $this->setUpPantherClient();

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $id_jdevoe = $jdevoe->id();
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'droits' => array(99,100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $id_abreton = $abreton->id();
        $agent_suppr = $this->builder->build(Agent::class, array(
            'login' => 'bsuppr', 'nom' => 'Suppr', 'prenom' => 'Bientôt',
            'droits' => array(99,100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $id_agent_suppr = $agent_suppr->id();
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'droits' => array(21,4,99,100), 'supprime' => 0, 'actif' => 'Actif','heures_travail'=>1.4,
            'check_hamac' => 0, 'conges_credit' => -1.2, 'conges_reliquat' => 1.2, 'comp_time' => 0, 'conges_anticipation' => 0,
            'conges_annuel' => 3.4,
        ));

        $id_kboivin = $kboivin->id();

        // Login with agent having rights for absences
        $this->login($kboivin);

        $crawler = $this->client->request('GET', '/agent');

        $this->assertSelectorTextContains('h3', 'Liste des agents');

        $onclick = "location.href='/agent/add';";
        $result = $crawler->filterXPath("//input[@value='Ajouter']");
        $this->assertEquals($onclick, $result->attr('onclick'));

        $list = $this->getSelectValues('showAgentSelect');
        $this->assertCount(3, $list);

        $this->assertTrue(in_array('Actif', $list));
        $this->assertTrue(in_array('Inactif', $list));
        $this->assertTrue(in_array('Supprimé', $list));

        $result = $crawler->filterXPath('//table[@id="tableAgents"]/thead');
        $this->assertStringContainsString('Nom', $result->text());
        $this->assertStringContainsString('Prénom', $result->text());
        $this->assertStringContainsString('Heures', $result->text());
        $this->assertStringContainsString('Statut', $result->text());
        $this->assertStringContainsString('Service', $result->text());
        $this->assertStringContainsString('Arrivée', $result->text());
        $this->assertStringContainsString('Départ', $result->text());
        $this->assertStringContainsString('Accès', $result->text());

        $result = $crawler->filterXPath('//table[@id="tableAgents"]');
        $this->assertStringContainsString('Boivin', $result->text());
        $this->assertStringContainsString('Karel', $result->text());
        $this->assertStringContainsString('Breton', $result->text());
        $this->assertStringContainsString('Aubert', $result->text());
        $this->assertStringContainsString('Devoe', $result->text());
        $this->assertStringContainsString('John', $result->text());
        $this->assertStringContainsString('Suppr', $result->text());
        $this->assertStringContainsString('Bientôt', $result->text());

        //TEST SELECTION

        $checkbox = $crawler->filterXPath("//input[@value='$id_abreton']");
        $checkbox->click();

        $select_action_values = $this->getSelectValues('action');
        $this->assertCount(3, $select_action_values);

        $this->assertTrue(in_array('edit', $select_action_values));
        $this->assertTrue(in_array('delete', $select_action_values), 'Admin');

        $select = $this->getSelect('action');
        $select->selectByValue('edit');

        $button = $crawler->filterXPath('//input[@value="Valider"]');
        $button->click();

        $result = $crawler->filterXPath('//div[@aria-labelledby="ui-id-1"]');
        $this->assertStringContainsString('display: block', $result->attr('style'));
    }
}
