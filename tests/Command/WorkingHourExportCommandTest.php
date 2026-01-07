<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use App\Entity\WorkingHour;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\PLBWebTestCase;

class WorkingHourExportCommandTest extends PLBWebTestCase
{
    private string $lockFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);

        $this->builder->build(Agent::class, array(
            'login' => 'alice', 'mail' => 'alice@example.com', 'nom' => 'Doe', 'prenom' => 'Alice',
            'supprime' => 0, 'matricule' => '0000000ff040'
        ));

        $this->builder->build(Agent::class, array(
            'login' => 'alex', 'mail' => 'alex@example.com', 'nom' => 'Doe', 'prenom' => 'Alex',
            'supprime' => 0, 'matricule' => '0000000ee490'
        ));

        $this->lockFile = sys_get_temp_dir() . '/plannoWorkingHourExport.lock';
        if (file_exists($this->lockFile)) {
            @unlink($this->lockFile);
        }
    }

    public function testWorkingHourExportCommand(): void
    {
        $file = sys_get_temp_dir() . '/PlannoTestWorkingHourExport.csv';

        $this->setParam('PlanningHebdo-ExportFile', $file);
        $this->setParam('PlanningHebdo-ExportDaysBefore', 5);
        $this->setParam('PlanningHebdo-ExportDaysAfter', 10);
        $this->setParam('PlanningHebdo-ExportAgentId', 'matricule');
        $this->setParam('EDTSamedi',1);
        $this->setParam('PlanningHebdo',1);

        $this->entityManager->clear();

        $alice = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alice']);
        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);

        $this->builder->build(WorkingHour::class, [
            'perso_id' => $alice->getId(),
            'actuel' => 1,
            'valide' => true,
            'debut' => new \DateTime('1 month ago'),
            'fin' => new \DateTime('+ 1 year'),
            'temps' => [
                0 => ['', '', '', '', 0],
                1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
                2 => ['09:00:00', '13:00:00', '', '', 1],
                3 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
                4 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
                5 => ['09:00:00', '13:00:00', '', '', 1],
            ]
        ]);

        $this->builder->build(WorkingHour::class, [
            'perso_id' => $alex->getId(),
            'actuel' => 1,
            'valide' => true,
            'debut' => new \DateTime('1 month ago'),
            'fin' => new \DateTime('+ 1 year'),
            'temps' => [
                0 => ['', '', '', '', 0],
                1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
                2 => ['09:00:00', '13:00:00', '', '', 1],
                3 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
                4 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
                5 => ['09:00:00', '13:00:00', '', '', 1],
            ]
        ]);

        $this->entityManager->clear();

        $this->execute();

        $this->assertFileExists($file);
        // $contents = file_get_contents($file);
        // $this->assertStringContainsString('0000000ff040', $contents);

        // Tests the content
        $date = new \DateTime('next monday');
        $nextMonday = $date->format('Y-m-d');
        $date->modify('+1 day');
        $nextTuesday = $date->format('Y-m-d');

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $cells = explode(',', $line);

            if ($cells[0] == $nextMonday and $cells[1] == '0000000ff040') {
                // faire les vérification ici
                echo "\nNext Monday\n";
                var_dump($cells);
                // Add Assert $cells[2] is empty
                // Add asser $cells 3,4,5,6 does not exist
                $this->assertStringContainsString('', $cells[2]);
                for ($i = 3; $i <= 6; $i++) {
                    $this->assertArrayNotHasKey($i, $cells);
                }
                $this->assertNull('', $cells[2]);
            }

            if ($cells[0] == $nextTuesday and $cells[1] == '0000000ff040') {
                // faire les vérification ici
                echo "\nNext Tuesday\n";
                var_dump($cells);
                // Add Assert $cells[2] is empty
                // Add assert $cells 3,4,5,6 exists
                // Add assert $cells[3] = '09:00'
                // Add assert $cells[4] = '12:00'
                // Add assert $cells[5] = '13:00'
                // Add assert $cells[6] = '17:00'

            }
        }

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
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
    }
}
