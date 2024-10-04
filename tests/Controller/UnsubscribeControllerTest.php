<?php

use App\Model\Agent;
use App\Model\Skill;
use App\Model\ConfigParam;
use App\Model\Manager;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;


class UnsubscribeControllerTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testUnsubscribe()
    {
        global $entityManager;
        $client = static::createClient();
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $crawler = $client->request('GET', '/unsubscribe');

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(null,false),'Désinscription','h3 is Désinscription unlogged');

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $this->logInAgent($agent, array(99,100));

        $crawler = $client->request('GET', '/unsubscribe');

        $result = $crawler->filterXPath('//h3');
        $this->assertEquals($result->text(null,false),'Désinscription','h3 is Désinscription logged-in');

    }
}
