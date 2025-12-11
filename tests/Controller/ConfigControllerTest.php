<?php

use App\Entity\Agent;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class ConfigControllerTest extends PLBWebTestCase
{
    public function testAccessWithNonLoggedIn() {
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

    public function testAccessWithAuthorizedUser() {

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
            '/<span> Divers<\/span>/',
            $response
        );
    }
}
