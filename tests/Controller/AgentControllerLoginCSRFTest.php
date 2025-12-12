<?php
use App\Model\Agent;

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
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);


        $agent = $this->builder->build(Agent::class, array(
            'login' => 'login_1', 'supprime' => 0,
        ));

        $this->logInAgent($agent, array(100,99,21,401,601));

        $crawler = $this->client->request('POST', '/ajax/update_agent_login', array(
            'id' => $agent->id(),
            'login' => 'login_2',
        ));

        $agent = $entityManager->find(Agent::class, $agent->id());
        $this->assertNotEquals('login_2', $agent->login());
        $this->assertEquals('login_1', $agent->login());

    }

    public function testLoginChangeWithOkCSRF()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);


        $agent = $this->builder->build(Agent::class, array(
            'login' => 'login_1', 'supprime' => 0,
        ));

        $this->logInAgent($agent, array(100,99,21,401,601));

        $crawler = $this->client->request('GET', '/agent/' . $agent->id());
        $extract_result = $crawler->filter('input#_token')->extract(array('value'));
        $token = $extract_result[0];

        $crawler = $this->client->request('POST', '/ajax/update_agent_login', array(
            'id' => $agent->id(),
            'login' => 'login_2',
            '_token' => $token,
        ));

        $agent = $entityManager->find(Agent::class, $agent->id());
        $this->assertEquals('login_2', $agent->login());

    }
}
