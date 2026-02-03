<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use App\Entity\Config;
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

    public function testPlanningImportModel(): void
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

        $this->testPlanningControlCommandPlanningNotCreated();

        // Setup Panther
        $this->setUpPantherClient();

        // Use agent 9 and log in
        $agent = $this->entityManager->getRepository(Agent::class)->find(9);
        $this->login($agent);

        // Open the planning page
        $crawler = $this->client->request('GET', '/');

        // Delete the planning exist
        try {
            $linkUnlock = $crawler->filter('#icon-lock');
            $this->assertTrue($linkUnlock->count() == 1, 'unlock');
            $linkUnlock->click();
            $crawler = $this->client->refreshCrawler();

            $delete = $crawler->filter('#planning-drop');
            $this->assertTrue($delete->count() == 1, 'delete');
            $delete->click();
            $crawler = $this->client->refreshCrawler();
        } catch (\Exception $e) {
        }

        // Load a model
        $linkModel = $crawler->filter('#planning-import');

        $this->assertTrue($linkModel->count() == 1, 'Importer un modèle');

        $linkModel->click();
        $crawler = $this->client->refreshCrawler();

        $this->client->waitForVisibility('#model');

        $select = $this->getSelect('model');
        $options = $select->getOptions();

        $this->assertGreaterThan(
            1,
            count($options),
            'There should be options'
        );

        $select->selectByValue('1');

        $button = $crawler->filter('.ui-dialog-buttonpane button')->eq(1);
        $button->click();
        $crawler = $this->client->refreshCrawler();

        $this->testPlanningControlCommandPlanningNotValidated();

        // Lock
        $linkLock = $crawler->filter('#icon-unlock');
        $this->assertTrue($linkLock->count() == 1, 'lock');
        $linkLock->click();
        $crawler = $this->client->refreshCrawler();

        $this->testPlanningControlCommandPlanningValidated();

        $this->testPlanningControlCommandEmptyCells();

        parent::setData();
    }

    private function testPlanningControlCommandPlanningNotCreated(): void // no import??
    {
        $output = $this->execute();

        $this->assertStringContainsString("To: xxx.ss@biblibre.com", $output);
        $this->assertStringContainsString("Subject: Plannings", $output);
        $this->assertStringContainsString("Message:", $output);
        $this->assertStringContainsString("n'est pas créé", $output);
    }

    private function testPlanningControlCommandEmptyCells(): void
    {
        // FIXME I added this return because this test doesn't work on Sundays
        // if (date('w') == 0) {
        //     return;
        // }

        $output = $this->execute();
        $this->assertStringContainsString("Renseignement RDC, de 11h30 à 16h00", $output);
    }

    private function testPlanningControlCommandPlanningNotValidated(): void // no lock
    {
        // FIXME I added this return because this test doesn't work on Sundays
        // if (date('w') == 0) {
        //     return;
        // }

        $output = $this->execute();
        $this->assertStringContainsString("n'est pas validé;", $output);
    }

    private function testPlanningControlCommandPlanningValidated(): void // no import??
    {
        $output = $this->execute();
        $this->assertStringContainsString("n'est pas créé", $output);
    }

    private function execute(): string
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

        return $output;
    }
}
