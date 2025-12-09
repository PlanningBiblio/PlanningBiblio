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
        $agent = $this->builder->build(Agent::class, array(
            'login' => 'kboivin', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'droits' => array(4, 21, 99, 100, 301), 'supprime' => 0, 'actif' => 'Actif',
        ));

        $this->login($agent);

        // Open the planning page
        $crawler = $this->client->request('GET', '/');

        $link = $crawler->filter('#planning-import');

        $this->assertTrue($link->count() == 1, 'Importer un modèle');

        $link->click();
        $crawler = $this->client->refreshCrawler();

        $this->client->wait(15)->until($this->jqueryAjaxFinished());

        $this->client->wait(10)->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::id('model')
            )
        );

        $this->client->wait(5)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::id('model')
            )
        );


        $select = $this->getSelect('model');
        $options = $select->getOptions();

        $this->assertGreaterThan(
            1,
            count($options),
            'There should be options'
        );

        $select->selectByValue('5');
        $buttonValide = $crawler->selectButton('Valider');
        $buttonValide->click();


        //TODO use Panther instead
        $start = new \DateTime('today 09:00:00');
        $end = new \DateTime('today 10:00:00');

        $today = new \DateTime('');
        $this->builder->build(PlanningPositionTabAffectation::class, [
            'date' => $today, 'tableau' => 1, 'site' => 1
        ]);
        $this->builder->build(PlanningPositionLock::class, [
            'date' => $today, 'verrou' => '1', 'perso' => 0,
            'verrou2' => 1, 'validation2' => new \DateTime('2025-11-17 10:19:36'), 'perso2' => 1, 'site' => 1
        ]);
        $this->builder->build(PlanningPositionTab::class, [
            'id' => 1, 'tableau' => 1,
            'nom' => 'Scolaire : Mercredi - Samedi',
            'site' => 1
        ]);
        $this->builder->build(PlanningPosition::class, [
            'id' => 1,
            'perso_id' => 19, 'date' => $today,
            'site' => 1, 'debut' => $start, 'fin' => $end
        ]);
        $this->builder->build(PlanningPositionHours::class, [
            'numero'  => 1,
            'tableau' => 1,
            'debut' => $start, 'fin' => $end
        ]);

        $pos = new Position();
        $pos->setName('toto');
        $pos->setGroup('');
        $pos->setGroupId(0);
        $pos->setMandatory('Obligatoire');
        $pos->setFloor(2);
        $pos->setActivities([5, 9]);
        $pos->setStatistics(1);
        $pos->setTeleworking(0);
        $pos->setBlocking(1);
        $pos->setLunch(0);
        $pos->setDelete(null);
        $this->entityManager->persist($pos);

        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->builder->build(PlanningPositionLines::class, [
            'numero'  => 1,
            'tableau' => 1,
            'ligne'   => 1,
            'type'    => 'poste',
            'poste'   => $pos->getId()
        ]);

        $repo = $this->entityManager->getRepository(PlanningPositionTabAffectation::class);
        $this->assertNotNull(
            $repo->findOneBy(['date' => $today, 'tableau' => 1, 'site' => 1]),
            'PlanningPositionTabAffectation should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPositionLock::class);
        $this->assertNotNull(
            $repo->findOneBy(['date' => $today, 'site' => 1]),
            'PlanningPositionLock should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPositionTab::class);
        $this->assertNotNull(
            $repo->find(1),
            'PlanningPositionTab should be saved'
        );

        $repo = $this->entityManager->getRepository(Position::class);
        $this->assertNotNull(
            $repo->find(id: $pos->getId()),
            'Position should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPosition::class);
        $this->assertNotNull(
            $repo->find(1),
            'PlanningPosition should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPositionHours::class);
        $this->assertNotNull(
            $repo->findOneBy(['numero' => 1, 'tableau' => 1]),
            'PlanningPositionHours should be saved'
        );

        $repo = $this->entityManager->getRepository(PlanningPositionLines::class);
        $this->assertNotNull(
            $repo->findOneBy(['numero' => 1, 'tableau' => 1, 'ligne' => 1]),
            'PlanningPositionLines should be saved'
        );

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

    }
    
}
