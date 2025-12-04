<?php

namespace App\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\WorkingHour;
use App\Entity\Agent;
use App\Entity\Config;
use Tests\PLBWebTestCase;

class WorkingHourExportCommandTest extends PLBWebTestCase
{
    public function testWorkingHourExportCommand(): void
    {
        $this->setParam('PlanningHebdo-ExportFile', '/tmp/test-export.csv');
        $this->setParam('PlanningHebdo-ExportDaysBefore', '1');
        $this->setParam('PlanningHebdo-ExportDaysAfter', '1');
        $this->setParam('PlanningHebdo-ExportAgentId', 'matricule');
        $this->setParam('EDTSamedi',1);
        $this->setParam('PlanningHebdo',1);

        $this->entityManager->clear();

        $alice = $this->builder->build(Agent::class, array(
            'login' => 'alice', 'mail' => 'alice@example.com', 'nom' => 'Doe', 'prenom' => 'Alice',
            'supprime' => 0, 'matricule' => '0000000ff040'
        ));

        $this->builder->build(WorkingHour::class, array(
            'perso_id' => $alice->getId(), 'temps' => '[["09:00:00","","","19:00:00","1"],["09:00:00","","","19:00:00","1"],["09:00:00","","","19:00:00","1"],["09:00:00","","","19:00:00","1"],["09:00:00","","","19:00:00","1"],["09:00:00","","","19:00:00","1"],["","","","",""]]', 
            'actuel' => '1'
        ));
        // $this->entityManager->persist($workinghour);
        // $this->entityManager->flush();
        $this->entityManager->clear();

        $this->execute();

        $this->assertFileExists('/tmp/test-export.csv');
        $contents = file_get_contents('/tmp/test-export.csv');
        $this->assertStringContainsString('0000000ff040', $contents);
        
        $this->restore();
    }

    protected function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:workinghour:export');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--not-really' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
    }
}
