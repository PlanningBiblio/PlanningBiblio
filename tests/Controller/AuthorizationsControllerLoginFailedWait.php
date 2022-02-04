<?php

use App\Model\Agent;

use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AuthorizationsControllerLoginFailedWait extends PLBWebTestCase
{
    public function testLoginFailedWait()
    {
        $GLOBALS['config']['IPBlocker-Attempts'] = 3;
        $_SESSION['oups']['CSRFToken'] = 'FOO';
        $CSRFToken = 'FOO';
        $_SERVER['REMOTE_ADDR'] = '11.11.11.11';

        loginFailed('ben', $CSRFToken);

        $client = static::createClient([], ['REMOTE_ADDR' => '11.11.11.11']);
        $client->request('GET', '/login');
        //$response = $client->getResponse()->getContent();

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "One failed attempt don't block the IP"
        );

        loginFailed('ben', $CSRFToken);
        $client->request('GET', '/login');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "Second failed attempt don't block the IP"
        );

        loginFailed('ben', $CSRFToken);
        $client->request('GET', '/login');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "Third failed attempt will block the IP"
        );

        loginFailed('ben', $CSRFToken);
        $client->request('GET', '/login');

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            "Fourth attempt is blocked for the IP"
        );
    }
}