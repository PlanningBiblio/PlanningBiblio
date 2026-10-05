<?php

use App\Entity\Agent;
use App\Entity\Model;
use App\Entity\ModelAgent;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class ModelControllerTest extends PLBWebTestCase
{
    public function testDelete(): void
    {
        $entityManager = $this->entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Model::class);
        $builder->delete(ModelAgent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(301));

        $modelId = 1;
        $builder->build(Model::class, array('model_id' => $modelId, 'nom' => 'to be deleted', 'jour' => 9, 'site' => 1));
        $builder->build(ModelAgent::class, array('model_id' => $modelId, 'perso_id' => $agent->getId(), 'debut' => new DateTime('09:00:00'), 'fin' => new DateTime('12:00:00'), 'jour' => '9', 'tableau' => '1', 'commentaire' => '', 'site' => 1));

        $crawler = $this->client->request('GET', '/model');
        $this->assertSelectorCount(1, '#tableModeles tbody tr');
        $token = $crawler->filter('#_token')->attr('value');

        // Assert deletion succeeds with CSRF token
        $this->client->request('POST', "/model/$modelId/delete", array('_token' => $token));
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->request('GET', '/model');
        $this->assertSelectorCount(0, '#tableModeles tbody tr');

        $entityManager->clear();
        $this->assertCount(0, $entityManager->getRepository(Model::class)->findBy(array('model_id' => $modelId)), 'model is deleted');
        $this->assertCount(0, $entityManager->getRepository(ModelAgent::class)->findBy(array('model_id' => $modelId)), 'model agents are deleted');
    }
}
