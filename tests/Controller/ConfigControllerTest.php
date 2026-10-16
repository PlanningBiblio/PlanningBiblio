<?php

use App\Entity\Agent;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class ConfigControllerTest extends PLBWebTestCase
{
    public function testAccessWithNonLoggedIn(): void {
        $this->client->request('GET', '/config');

        $response = $this->client->getResponse()->getContent();
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode(), 'Anonymous users are redirected to the login page');
        $this->assertMatchesRegularExpression('/refresh/', $response);
        $this->assertMatchesRegularExpression('/content/', $response);
        $this->assertMatchesRegularExpression('/url/', $response);
        $this->assertMatchesRegularExpression('/login/', $response);
        $this->assertMatchesRegularExpression('/redirURL/', $response);
        $this->assertMatchesRegularExpression('/config/', $response);

    }

    public function testAccessWithAuthorizedUser(): void {

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(20));

        $this->client->request('GET', '/config');

        $response = $this->client->getResponse()->getContent();
        $this->assertMatchesRegularExpression(
            '/<h3>Configuration fonctionnelle<\/h3>/',
            $response
        );

        $this->assertMatchesRegularExpression(
            '/<button class="accordion-button collapsed".*>\n *Divers\n *<\/button>/',
            $response
        );
    }

    public function testUpdateConfigWrongCSRF(): void
    {
        $this->builder->delete(Agent::class);

        $agent = $this->builder->build(Agent::class, ['login' => 'agent_test', 'droits' => [20]]);
        $this->logInAgent($agent, $agent->getACL());

        $crawler = $this->client->request('POST', '/config', ['_token' => 'fake token']);
        $this->assertEquals($this->client->getResponse()->getStatusCode(), 302, 'Wrong CSRF Token returns 302');
    }    
}
