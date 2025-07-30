<?php

use App\Model\Absence;
use App\Model\Agent;
use App\PlanningBiblio\WorkingHours;
use Symfony\Component\DomCrawler\Crawler;
use Tests\FixtureBuilder;
use Tests\PLBWebTestCase;

class AgentControllerPasswordTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);
    }

    public function testPasswordChangeWithCSRFOk()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);


        $clear = "Password_1";
        $crypted = password_hash($clear, PASSWORD_BCRYPT);

        $crypted2 = password_hash('Password_changed2', PASSWORD_BCRYPT);

        $agent = $this->builder->build(Agent::class, array(
            'login' => 'agent_password', 'supprime' => 0,'password' => $crypted,
        ));

        $this->logInAgent($agent, array(100,99,401,601));

        $crawler = $this->client->request('GET', '/agent/' . $agent->getId());
        $extract_result = $crawler->filter('input#_token')->extract(array('value'));
        $token = $extract_result[0];

        $crawler = $this->client->request('POST', '/ajax/change-own-password', array(
            'current_password' => 'Password_1',
            'password' => 'Password_changed2',
            '_token' => $token,
        ));

        $agent = $entityManager->find(Agent::class, $agent->getId());
        $this->assertTrue(password_verify('Password_changed2', $agent->getPassword()));

        $result = $crawler->filterXPath('//p');
        $this->assertEquals($result->text('Node does not exist', false), 'Password successfully changed');

    }


    public function testPasswordChangeWithFakeCSRF()
    {
        global $entityManager;
        $_SESSION['oups']['CSRFToken'] = '00000';
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);


        $clear = "Password_1";
        $crypted = password_hash($clear, PASSWORD_BCRYPT);

        $agent = $this->builder->build(Agent::class, array(
            'login' => 'agent_password', 'supprime' => 0,'password' => $crypted,
        ));

        $this->logInAgent($agent, array(100,99,401,601));

        $crawler = $this->client->request('POST', '/ajax/change-own-password', array(
            'current_password' => 'Password_1',
            'password' => 'Password_changed2',
            '_token' => 'fake_token',
        ));

        $agent = $entityManager->find(Agent::class, $agent->getId());
        $this->assertFalse(password_verify('Password_changed2', $agent->getPassword()));
        $this->assertTrue(password_verify('Password_1', $agent->getPassword()));

    }
}
