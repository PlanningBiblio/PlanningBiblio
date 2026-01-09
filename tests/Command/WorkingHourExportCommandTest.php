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
                0 => ['', '', '', '', 0],// Monday
                1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],// Tuesday
                2 => ['09:00:00', '13:00:00', '', '', 1],// Wednesday
                3 => ['10:00:00', '12:00:00', '13:00:00', '17:00:00', 1],// Thursday
                4 => ['10:35', '12:35', '13:00:00', '17:00:00', 1],// Friday
                5 => ['09:00:00', '13:00:00', '', '', 1],// Saturday
            ]
        ]);

        $this->builder->build(WorkingHour::class, [
            'perso_id' => $alex->getId(),
            'actuel' => 1,
            'valide' => true,
            'debut' => new \DateTime('1 month ago'),
            'fin' => new \DateTime('+ 1 year'),
            'temps' => [
                0 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],// Monday
                1 => ['', '', '', '', 0],// Tuesday
                2 => ['09:00:00', '13:00:00', '', '', 1],// Wednesday
                3 => ['10:00:00', '12:00:00', '13:00:00', '17:00:00', 1],// Thursday
                4 => ['10:00:00', '12:00:00', '13:00:00', '17:00:00', 1],// Friday
                5 => ['13:35', '17:35', '', '', 1],// Saturday
            ]
        ]);

        $this->entityManager->clear();

        $this->execute();

        $this->assertFileExists($file);

        // Tests the content
        $date = new \DateTime('next monday');
        $nextMonday = $date->format('Y-m-d');
        $date->modify('+1 day');
        $nextTuesday = $date->format('Y-m-d');        
        $date->modify('+1 day');
        $nextWednesday = $date->format('Y-m-d');
        $date->modify('+1 day');
        $nextThursday = $date->format('Y-m-d');
        $date->modify('+1 day');
        $nextFriday = $date->format('Y-m-d');
        $date->modify('+1 day');
        $nextSaturday = $date->format('Y-m-d');

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $cells = explode(',', $line);

            if ($cells[0] == $nextMonday and $cells[1] == '0000000ff040') {
                // faire les vérification ici
                echo "\nAlice Next Monday\n";
                $this->assertEmpty($cells[2]);
                for ($i = 3; $i <= 7; $i++) {
                    $this->assertArrayNotHasKey($i, $cells);
                }
            }

            if ($cells[0] == $nextTuesday and $cells[1] == '0000000ff040') {
                echo "\nAlice Next Tuesday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('09:00', $cells[3]);
                $this->assertEquals('12:00', $cells[4]);
                $this->assertEquals('13:00', $cells[5]);
                $this->assertEquals('17:00', $cells[6]);
            }

            if ($cells[0] == $nextWednesday and $cells[1] == '0000000ff040') {
                echo "\nAlice Next Wednesday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('09:00', $cells[3]);
                $this->assertEquals('13:00', $cells[4]);
                $this->assertArrayNotHasKey(5, $cells);
                $this->assertArrayNotHasKey(6, $cells);
            }

            if ($cells[0] == $nextThursday and $cells[1] == '0000000ff040') {
                echo "\nAlice Next Thursday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('10:00', $cells[3]);
                $this->assertEquals('12:00', $cells[4]);
                $this->assertEquals('13:00', $cells[5]);
                $this->assertEquals('17:00', $cells[6]);
            }

            if ($cells[0] == $nextFriday and $cells[1] == '0000000ff040') {
                echo "\nAlice Next Friday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('10:35', $cells[3]);
                $this->assertEquals('12:35', $cells[4]);
                $this->assertEquals('13:00', $cells[5]);
                $this->assertEquals('17:00', $cells[6]);
            }

            if ($cells[0] == $nextSaturday and $cells[1] == '0000000ff040') {
                echo "\nAlice Next Saturday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('09:00', $cells[3]);
                $this->assertEquals('13:00', $cells[4]);
                $this->assertArrayNotHasKey(5, $cells);
                $this->assertArrayNotHasKey(6, $cells);
            }

            // Check for the second person
            if ($cells[0] == $nextMonday and $cells[1] == '0000000ee490') {
                // faire les vérification ici
                echo "\nAlex Next Monday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('09:00', $cells[3]);
                $this->assertEquals('12:00', $cells[4]);
                $this->assertEquals('13:00', $cells[5]);
                $this->assertEquals('17:00', $cells[6]);
            }

            if ($cells[0] == $nextTuesday and $cells[1] == '0000000ee490') {
                echo "\nAlex Next Tuesday\n";
                $this->assertEmpty($cells[2]);
                for ($i = 3; $i <= 6; $i++) {
                    $this->assertArrayNotHasKey($i, $cells);
                }
            }

            if ($cells[0] == $nextWednesday and $cells[1] == '0000000ee490') {
                echo "\nAlex Next Wednesday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('09:00', $cells[3]);
                $this->assertEquals('13:00', $cells[4]);
                $this->assertArrayNotHasKey(5, $cells);
                $this->assertArrayNotHasKey(6, $cells);
            }

            if ($cells[0] == $nextThursday and $cells[1] == '0000000ee490') {
                echo "\nAlex Next Thursday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('10:00', $cells[3]);
                $this->assertEquals('12:00', $cells[4]);
                $this->assertEquals('13:00', $cells[5]);
                $this->assertEquals('17:00', $cells[6]);
            }

            if ($cells[0] == $nextFriday and $cells[1] == '0000000ee490') {
                echo "\nAlex Next Friday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('10:00', $cells[3]);
                $this->assertEquals('12:00', $cells[4]);
                $this->assertEquals('13:00', $cells[5]);
                $this->assertEquals('17:00', $cells[6]);
            }

            if ($cells[0] == $nextSaturday and $cells[1] == '0000000ee490') {
                echo "\nAlex Next Saturday\n";
                $this->assertEmpty($cells[2]);
                $this->assertEquals('13:35', $cells[3]);
                $this->assertEquals('17:35', $cells[4]);
                $this->assertArrayNotHasKey(5, $cells);
                $this->assertArrayNotHasKey(6, $cells);
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
