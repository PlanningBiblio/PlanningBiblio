<?php

use App\Entity\Agent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AgentControllerListTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testListAgent(): void
    {
        $this->setParam('Multisites-nombre', 1);
        $this->builder->delete(Agent::class);
        $this->setUpPantherClient();

        $jdevoe = $this->builder->build(Agent::class, array(
            'login' => 'jdevoe', 'nom' => 'Devoe', 'prenom' => 'John',
            'droits' => array(99,100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $id_jdevoe = $jdevoe->getId();
        $abreton = $this->builder->build(Agent::class, array(
            'login' => 'abreton', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'droits' => array(99,100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $id_abreton = $abreton->getId();
        $agent_suppr = $this->builder->build(Agent::class, array(
            'login' => 'bsuppr', 'nom' => 'Suppr', 'prenom' => 'Bientôt',
            'droits' => array(99,100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $id_agent_suppr = $agent_suppr->getId();
        $kboivin = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'droits' => array(21,4,99,100), 'supprime' => 0, 'actif' => 'Actif',
        ));
        $id_kboivin = $kboivin->getId();

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
        $this->assertStringContainsString('Nom', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Prénom', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Heures', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Statut', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Service', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Arrivée', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Départ', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Accès', $result->text('Node does not exist', true));

        $result = $crawler->filterXPath('//table[@id="tableAgents"]');
        $this->assertStringContainsString('Boivin', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Karel', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Breton', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Aubert', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Devoe', $result->text('Node does not exist', true));
        $this->assertStringContainsString('John', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Suppr', $result->text('Node does not exist', true));
        $this->assertStringContainsString('Bientôt', $result->text('Node does not exist', true));

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
