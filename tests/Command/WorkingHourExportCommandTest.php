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
        $file = sys_get_temp_dir() . '/plannoTestWorkingHourExport.csv';

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
                0 => ['', '', '', '', 0], // Monday
                1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1], // Tuesday
                2 => ['09:00:00', '13:00:00', '', '', 1], // Wednesday
                3 => ['10:00:00', '12:00:00', '13:00:00', '17:00:00', 1], // Thursday
                4 => ['10:35', '12:35', '13:00:00', '17:00:00', 1], // Friday
                5 => ['09:00:00', '13:00:00', '', '', 1], // Saturday
            ]
        ]);

        $this->builder->build(WorkingHour::class, [
            'perso_id' => $alex->getId(),
            'actuel' => 1,
            'valide' => true,
            'debut' => new \DateTime('1 month ago'),
            'fin' => new \DateTime('+ 1 year'),
            'temps' => [
                0 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1], // Monday
                1 => ['', '', '', '', 0], // Tuesday
                2 => ['09:00:00', '', '', '13:00:00', 1], // Wednesday, from 9 to 13 using the 1st and the 4th slots
                3 => ['10:00:00', '12:00:00', '13:00:00', '17:00:00', 1], // Thursday
                4 => ['10:00:00', '12:00:00', '13:00:00', '20:00:00', 1, '17:00:00', '18:00:00'], // Friday
                5 => ['13:35', '', '', '', 1, '17:35', ''], // Saturday, from 13:35 to 17:35 using the 1st and the 5th slots
            ]
        ]);

        $this->entityManager->clear();

        $this->execute();

        $this->assertFileExists($file);

        // Tests the content
        $date = new \DateTime('next monday');
        $nextMonday = $date->format('Y-m-d');
        $nextTuesday = $date->modify('+1 day')->format('Y-m-d');        
        $nextWednesday = $date->modify('+1 day')->format('Y-m-d');
        $nextThursday = $date->modify('+1 day')->format('Y-m-d');
        $nextFriday = $date->modify('+1 day')->format('Y-m-d');
        $nextSaturday = $date->modify('+1 day')->format('Y-m-d');

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $cells = explode(',', $line);

            if ($cells[0] == $nextMonday and $cells[1] == '0000000ff040') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                for ($i = 3; $i <= 7; $i++) {
                    $this->assertArrayNotHasKey($i, $cells, 'Alice doesn\'t work next Monday');
                }
            }

            if ($cells[0] == $nextTuesday and $cells[1] == '0000000ff040') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('09:00', $cells[3], 'Alice works from 9 to 12 and from 13 to 17 next tuesday');
                $this->assertEquals('12:00', $cells[4], 'Alice works from 9 to 12 and from 13 to 17 next tuesday');
                $this->assertEquals('13:00', $cells[5], 'Alice works from 9 to 12 and from 13 to 17 next tuesday');
                $this->assertEquals('17:00', $cells[6], 'Alice works from 9 to 12 and from 13 to 17 next tuesday');
            }

            if ($cells[0] == $nextWednesday and $cells[1] == '0000000ff040') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('09:00', $cells[3], 'Alice works from 9 to 13 next wednesday');
                $this->assertEquals('13:00', $cells[4], 'Alice works from 9 to 13 next wednesday');
                $this->assertArrayNotHasKey(5, $cells, 'Alice works from 9 to 13 next wednesday');
                $this->assertArrayNotHasKey(6, $cells, 'Alice works from 9 to 13 next wednesday');
            }

            if ($cells[0] == $nextThursday and $cells[1] == '0000000ff040') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('10:00', $cells[3], 'Alice works from 10 to 12 and from 13 to 17 next thursday');
                $this->assertEquals('12:00', $cells[4], 'Alice works from 10 to 12 and from 13 to 17 next thursday');
                $this->assertEquals('13:00', $cells[5], 'Alice works from 10 to 12 and from 13 to 17 next thursday');
                $this->assertEquals('17:00', $cells[6], 'Alice works from 10 to 12 and from 13 to 17 next thursday');
            }

            if ($cells[0] == $nextFriday and $cells[1] == '0000000ff040') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('10:35', $cells[3], 'Alice works from 10:35 to 12:35 and from 13 to 17 next friday');
                $this->assertEquals('12:35', $cells[4], 'Alice works from 10:35 to 12:35 and from 13 to 17 next friday');
                $this->assertEquals('13:00', $cells[5], 'Alice works from 10:35 to 12:35 and from 13 to 17 next friday');
                $this->assertEquals('17:00', $cells[6], 'Alice works from 10:35 to 12:35 and from 13 to 17 next friday');
            }

            if ($cells[0] == $nextSaturday and $cells[1] == '0000000ff040') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('09:00', $cells[3], 'Alice works from 9 to 13 next saturday');
                $this->assertEquals('13:00', $cells[4], 'Alice works from 9 to 13 next saturday');
                $this->assertArrayNotHasKey(5, $cells, 'Alice works from 9 to 13 next saturday');
                $this->assertArrayNotHasKey(6, $cells, 'Alice works from 9 to 13 next saturday');
            }

            // Check for the second person
            if ($cells[0] == $nextMonday and $cells[1] == '0000000ee490') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('09:00', $cells[3], 'Alex doesn\'t work next monday');
                $this->assertEquals('12:00', $cells[4], 'Alex doesn\'t work next monday');
                $this->assertEquals('13:00', $cells[5], 'Alex doesn\'t work next monday');
                $this->assertEquals('17:00', $cells[6], 'Alex doesn\'t work next monday');
            }

            if ($cells[0] == $nextTuesday and $cells[1] == '0000000ee490') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                for ($i = 3; $i <= 6; $i++) {
                    $this->assertArrayNotHasKey($i, $cells);
                }
            }

            if ($cells[0] == $nextWednesday and $cells[1] == '0000000ee490') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('09:00', $cells[3], 'Alex works from 9 to 13 next wednesday');
                $this->assertEquals('13:00', $cells[4], 'Alex works from 9 to 13 next wednesday');
                $this->assertArrayNotHasKey(5, $cells, 'Alex works from 9 to 13 next wednesday');
                $this->assertArrayNotHasKey(6, $cells, 'Alex works from 9 to 13 next wednesday');
            }

            if ($cells[0] == $nextThursday and $cells[1] == '0000000ee490') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('10:00', $cells[3], 'Alex works from 10 to 12 and from 13 to 17 next thursday');
                $this->assertEquals('12:00', $cells[4], 'Alex works from 10 to 12 and from 13 to 17 next thursday');
                $this->assertEquals('13:00', $cells[5], 'Alex works from 10 to 12 and from 13 to 17 next thursday');
                $this->assertEquals('17:00', $cells[6], 'Alex works from 10 to 12 and from 13 to 17 next thursday');
            }

            if ($cells[0] == $nextFriday and $cells[1] == '0000000ee490') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('10:00', $cells[3], 'Alex works from 10 to 12, from 13 to 17 and from 18 to 20 next friday');
                $this->assertEquals('12:00', $cells[4], 'Alex works from 10 to 12, from 13 to 17 and from 18 to 20 next friday');
                $this->assertEquals('13:00', $cells[5], 'Alex works from 10 to 12, from 13 to 17 and from 18 to 20 next friday');
                $this->assertEquals('17:00', $cells[6], 'Alex works from 10 to 12, from 13 to 17 and from 18 to 20 next friday');
                $this->assertEquals('18:00', $cells[7], 'Alex works from 10 to 12, from 13 to 17 and from 18 to 20 next friday');
                $this->assertEquals('20:00', $cells[8], 'Alex works from 10 to 12, from 13 to 17 and from 18 to 20 next friday');
            }

            if ($cells[0] == $nextSaturday and $cells[1] == '0000000ee490') {
                $this->assertEmpty($cells[2], 'The third field is always empty');
                $this->assertEquals('13:35', $cells[3], 'Alex works from 13:35 to 17:35 next saturday');
                $this->assertEquals('17:35', $cells[4], 'Alex works from 13:35 to 17:35 next saturday');
                $this->assertArrayNotHasKey(5, $cells, 'Alex works from 13:35 to 17:35 next saturday');
                $this->assertArrayNotHasKey(6, $cells, 'Alex works from 13:35 to 17:35 next saturday');
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
