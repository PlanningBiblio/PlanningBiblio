<?php

namespace App\Tests\Controller;

use App\Kernel;
use App\Tests\Controller\BaseControllerTest;

class AdminInfoControllerTest extends BaseControllerTest
{
    public function testEmpty()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/info');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString("Messages d'informations", $client->getResponse()->getContent());
        //dump($client->getResponse()->getContent());
    }
}
