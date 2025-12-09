<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use App\Entity\PlanningPosition;
use App\Entity\PlanningPositionHours;
use App\Entity\PlanningPositionLines;
use App\Entity\PlanningPositionLock;
use App\Entity\PlanningPositionTab;
use App\Entity\PlanningPositionTabAffectation;
use App\Entity\Position;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\PLBWebTestCase;

class PlanningControlCommandTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        parent::setData('data7');

    }

    public function testPlanningControlCommand(): void
    {
        $this->setParam('Rappels-Actifs', 1);
        $this->setParam('Multisites-nombre', 1);
        $this->setParam('Multisites-site1', 1);
        $this->setParam('Multisites-site2', 0);
        $this->setParam('Multisites-site3', 0);
        $this->setParam('Multisites-site4', 0);
        $this->setParam('Rappels-Jours', 1);
        $this->setParam('Dimanche', 0);
        $this->setParam('Rappels-Renfort', 0);
        $this->setParam('Conges-Enable', 0);
        $this->setParam('Mail-Planning', 'xxx.ss@biblibre.com');


        // Setup Panther
        $this->setUpPantherClient();

        // Create an agent and log in
        $agent = $this->entityManager->getRepository(Agent::class)->find(1);
        $this->login($agent);

        // Open the planning page
        $crawler = $this->client->request('GET', '/');

        //load a model
        $linkModel = $crawler->filter('#planning-import');

        $this->assertTrue($linkModel->count() == 1, 'Importer un modèle');

        $linkModel->click();
        $crawler = $this->client->refreshCrawler();

        $select = $this->getSelect('model');
        $options = $select->getOptions();

        $this->assertGreaterThan(
            1,
            count($options),
            'There should be options'
        );

        $select->selectByValue('5');

        $this->client
            ->waitFor('#import-model-dialog'); // ensure dialog loaded

        $button = $crawler->filter('.ui-dialog-buttonpane button')->eq(1);
        $button->click();
        $crawler = $this->client->refreshCrawler();

        //lock
        $linkLock = $crawler->filter('#icon-unlock');

        $this->assertTrue($linkLock->count() == 1, 'lock');

        $linkLock->click();

        $this->execute();

        $this->restore();
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:planning:control');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--not-really' => true
        ]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString("To: xxx.ss@biblibre.com", $output);
        $this->assertStringContainsString("Subject: Plannings", $output);
        $this->assertStringContainsString("Message:", $output);
        $this->assertStringContainsString(" est validé;", $output);

    }
    
}
