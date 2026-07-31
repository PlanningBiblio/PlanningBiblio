<?php

use App\Entity\Agent;
use App\Service\IPBlockerService;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class IPBlockerServiceTest extends PLBWebTestCase
{
    public function testLogFailure(): void
    {
        $this->config->setParam('IPBlocker-Attempts', '3');
        $_SERVER['REMOTE_ADDR'] = '11.11.11.11';

        $client = static::createClient([]);
        $iPBlocker = $client->getContainer()->get(IPBlockerService::class);

        $iPBlocker->logFailure('ben');

        $client->request('GET', '/login');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "One failed attempt don't block the IP"
        );

        $iPBlocker->logFailure('ben');
        $client->request('GET', '/login');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "Second failed attempt don't block the IP"
        );

        $iPBlocker->logFailure('ben');
        $client->request('GET', '/login');

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
            "Third failed attempt will block the IP"
        );

        $iPBlocker->logFailure('ben');
        $client->request('GET', '/login');

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode(),
            "Fourth attempt is blocked for the IP"
        );
    }
}
