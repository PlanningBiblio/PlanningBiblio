<?php

use App\Entity\Agent;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;


class UnsubscribeControllerTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testUnsubscribe(): void
    {
        global $entityManager;
        $this->client = static::createClient();
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $email = 'johndoe@example.org';
        $token = encrypt($email);

        $crawler = $this->client->request('GET', "/unsubscribe/$token");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(null,false),'Désinscription','h3 is Désinscription unlogged');
        $result = $crawler->filterXPath('//p[@id="unsubscribe-text"]');
        $this->assertEquals($result->text(null,false),'Désinscrire johndoe@example.org?','mail is retrieved unlogged');

        $crawler = $this->client->request('OPTIONS', "/unsubscribe/$token");
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200, 'OPTIONS returns 200 unlogged');

        $crawler = $this->client->request('POST', "/unsubscribe/$token");
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200, 'POST returns 200 unlogged');

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $this->logInAgent($agent, array(99,100));

        $crawler = $this->client->request('GET', "/unsubscribe/$token");

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(null,false),'Désinscription','h3 is Désinscription logged-in');
        $result = $crawler->filterXPath('//p[@id="unsubscribe-text"]');
        $this->assertEquals($result->text(null,false),'Désinscrire johndoe@example.org?','mail is retrieved logged-in');

        $crawler = $this->client->request('OPTIONS', "/unsubscribe/$token");
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200, 'OPTIONS returns 200 logged-in');

        $crawler = $this->client->request('POST', "/unsubscribe/$token");
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200, 'POST returns 200 logged-in');

    }
}
