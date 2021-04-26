<?php

use App\Model\Agent;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class ConfigControllerTest extends PLBWebTestCase
{
    public function testAccessWithNonLoggedIn() {
        $client = static::createClient();

        $client->request('GET', '/config');

        $response = $client->getResponse()->getContent();
        $this->assertContains(
            'Accès refusé',
            $response
        );
    }

    public function testAccessWithAuthorizedUser() {

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(20));

        $client = static::createClient();

        $client->request('GET', '/config');

        $response = $client->getResponse()->getContent();
        $this->assertContains(
            '<h3>Configuration</h3>',
            $response
        );

        $this->assertContains(
            '<span> Divers</span>',
            $response
        );
    }
}
