<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use Tests\PLBWebTestCase;

class CronTabCommandTest extends PLBWebTestCase
{
    public function testSomething(): void
    {
        $this->setUpPantherClient();

        $agent = $this->builder->build(Agent::class);
        $this->login($agent);

        $dateStr = (new \DateTime())->format('d/m/Y');

        $crawler = $this->client->request('GET', '/absence/add');
        $crawler->filter('input[name="debut"]')->sendKeys($dateStr);
        $crawler->filter('input[name="fin"]')->sendKeys($dateStr);

        // recurrence check
        $recurrence = $crawler->filter('#recurrence-checkbox');
        $this->assertTrue($recurrence->count() == 1, 'Récurrence check');
        $recurrence->click();
        $crawler = $this->client->refreshCrawler();

        // click enregistre

        //
        

    }
}
