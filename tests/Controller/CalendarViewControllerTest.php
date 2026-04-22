<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

final class CalendarViewControllerTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testIndex(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $agent = $builder->build(Agent::class, ['login' => 'jdoenv']);

        $this->logInAgent($agent, [99,100]);

        $this->client->request('GET', '/calendar/view');

        self::assertResponseIsSuccessful();
    }
}
