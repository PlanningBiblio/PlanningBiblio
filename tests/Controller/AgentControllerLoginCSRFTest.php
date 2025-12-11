<?php
use App\Entity\Agent;

use Symfony\Component\DomCrawler\Crawler;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;


class AgentControllerLoginCSRFTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testLoginChangeWithFakeCSRF()
    {
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);

        $agent = $this->builder->build(Agent::class, array(
            'login' => 'login_1', 'supprime' => 0,
        ));

        $this->logInAgent($agent, array(100,99,21,401,601));

        $crawler = $this->client->request('POST', '/ajax/update_agent_login', array(
            'id' => $agent->getId(),
            'login' => 'login_2',
        ));

        $agent = $this->entityManager->find(Agent::class, $agent->getId());
        $this->assertNotEquals('login_2', $agent->getLogin());
        $this->assertEquals('login_1', $agent->getLogin());
    }

    public function testLoginChangeWithOkCSRF()
    {
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);

        $agent = $this->builder->build(Agent::class, array(
            'login' => 'login_1', 'supprime' => 0,
        ));

        $this->logInAgent($agent, array(100,99,21,401,601));

        $crawler = $this->client->request('GET', '/agent/' . $agent->getId());
        $extract_result = $crawler->filter('input#_token')->extract(array('value'));
        $token = $extract_result[0];

        $crawler = $this->client->request('POST', '/ajax/update_agent_login', array(
            'id' => $agent->getId(),
            'login' => 'login_2',
            '_token' => $token,
        ));

        $agent = $this->entityManager->find(Agent::class, $agent->getId());
        $this->assertEquals('login_2', $agent->getLogin());
    }
}
