<?php

use App\Entity\Agent;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AuthorizationsControllerLoginFailedWait extends PLBWebTestCase
{
    public function testLoginFailedWait(): void
    {
        $GLOBALS['config']['IPBlocker-Attempts'] = 3;
        $_SERVER['REMOTE_ADDR'] = '11.11.11.11';

        loginFailed('ben', $this->CSRFToken);

        $client = static::createClient([], ['REMOTE_ADDR' => '11.11.11.11']);
        $client->request('GET', '/login');
        //$response = $client->getResponse()->getContent();

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "One failed attempt don't block the IP"
        );

        loginFailed('ben', $this->CSRFToken);
        $client->request('GET', '/login');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "Second failed attempt don't block the IP"
        );

        loginFailed('ben', $this->CSRFToken);
        $client->request('GET', '/login');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "Third failed attempt will block the IP"
        );

        loginFailed('ben', $this->CSRFToken);
        $client->request('GET', '/login');

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            "Fourth attempt is blocked for the IP"
        );
    }
}
