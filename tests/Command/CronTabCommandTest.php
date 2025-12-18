<?php

namespace App\Tests\Command;

use Tests\PLBWebTestCase;

class CronTabCommandTest extends PLBWebTestCase
{
    public function testSomething(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
