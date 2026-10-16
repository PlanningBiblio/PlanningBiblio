<?php

use App\Entity\Agent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AgentControllerBulkDeleteTest extends PLBWebTestCase
{
    public function testBulkDelete(): void
    {
        $entityManager = $this->entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $admin = $builder->build(Agent::class, array('login' => 'kboivin', 'droits' => array(21, 99, 100)));
        $jdevoe = $builder->build(Agent::class, array('login' => 'jdevoe', 'supprime' => 0, 'actif' => 'Actif'));
        $abreton = $builder->build(Agent::class, array('login' => 'abreton', 'supprime' => 0, 'actif' => 'Actif'));

        $this->logInAgent($admin, array(21, 99, 100));

        $list = json_encode(array($jdevoe->getId(), $abreton->getId(), 1));

        // Assert deletion fails without CSRF token
        $this->client->request('POST', '/agent/bulk/delete', array('list' => $list));
        $this->assertResponseRedirects('/access-denied');

        $entityManager->clear();
        $this->assertEquals(0, $entityManager->find(Agent::class, $jdevoe->getId())->getDeletion(), 'jdevoe is not deleted');
        $this->assertEquals(0, $entityManager->find(Agent::class, $abreton->getId())->getDeletion(), 'abreton is not deleted');

        $crawler = $this->client->request('GET', '/agent');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion succeeds with CSRF token
        $this->client->request('POST', '/agent/bulk/delete', array('_token' => $token, 'list' => $list));
        $this->assertResponseIsSuccessful();

        $entityManager->clear();
        $this->assertEquals(1, $entityManager->find(Agent::class, $jdevoe->getId())->getDeletion(), 'jdevoe is marked as deleted');
        $this->assertEquals(1, $entityManager->find(Agent::class, $abreton->getId())->getDeletion(), 'abreton is marked as deleted');
        $this->assertEquals(0, $entityManager->find(Agent::class, 1)->getDeletion(), 'admin (id 1) is never deleted');
    }
}
