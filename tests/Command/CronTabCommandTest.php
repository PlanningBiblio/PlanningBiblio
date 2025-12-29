<?php

namespace App\Tests\Command;

use App\Entity\Absence;
use App\Entity\Agent;
use Tests\PLBWebTestCase;

class CronTabCommandTest extends PLBWebTestCase
{
    public function testSomething(): void
    {
        $this->setUpPantherClient();

        $agent = $this->entityManager->getRepository(Agent::class)->find(1);
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
        $dialog = $crawler->filter('.ui-dialog')
            ->reduce(fn ($node) => str_contains($node->attr('style') ?? '', 'display: block'))
            ->first();
        $button = $dialog->filter('.ui-dialog-buttonpane button')->eq(1);
        $button->click();
        $crawler = $this->client->refreshCrawler();

        // select motif
        $this->client->executeScript('document.body.click();');
        $crawler = $this->client->refreshCrawler();

        $select = $this->getSelect('motif');
        $options = $select->getOptions();

        $this->assertGreaterThan(
            1,
            count($options),
            'There should be options'
        );

        $this->client->executeScript('document.body.click();');
        $crawler = $this->client->refreshCrawler();
        dump($crawler->html());
        $select->selectByValue('Congés payés');
        $crawler = $this->client->refreshCrawler();

        // submit form
        $button = $dialog->filter('.ui-dialog-buttonpane button')->eq(1);
        $button->click();
        $crawler = $this->client->refreshCrawler();

        $countAbsences = $this->entityManager->getRepository(Absence::class)->count(['agent' => $agent]);
        $this->assertEquals(140, $countAbsences, 'There should be 140 absences created');
    }
}
