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
        $this->assertMatchesRegularExpression('/Accès refusé/', $response);
    }

    public function testAccessWithAuthorizedUser() {

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));

        $this->logInAgent($agent, array(20));

        $client = static::createClient();

        $client->request('GET', '/config');

        $response = $client->getResponse()->getContent();
        $this->assertMatchesRegularExpression(
            '/<h3>Configuration<\/h3>/',
            $response
        );

        $this->assertMatchesRegularExpression(
            '/<span> Divers<\/span>/',
            $response
        );
    }
}
