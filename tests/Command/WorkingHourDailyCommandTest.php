<?php

namespace App\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\WorkingHour;
use Tests\PLBWebTestCase;

class WorkingHourDailyCommandTest extends PLBWebTestCase
{

    public function testWorkingHourDailyCommand(): void
    {
        $WorkingHour1 = $this->builder->build(WorkingHour::class,array(
            'perso_id' => 1,
            'actuel' => 0,
            'valide' => 1,
            'debut' => new \DateTime("2000-01-01"),
            'fin' => new \DateTime("2022-01-01")
        )); 

        $WorkingHour2 = $this->builder->build(WorkingHour::class,array(
            'perso_id' => 1,
            'actuel' => 1,
            'valide' => 1,
            'debut' => new \DateTime("2000-01-01"),
            'fin' => new \DateTime()
        )); 

        $WorkingHour3 = $this->builder->build(WorkingHour::class,array(
            'perso_id' => 1,
            'actuel' => 0,
            'valide' => 1,
            'debut' => new \DateTime(),
        )); 

        $id1 = $WorkingHour1->getId();
        $id2 = $WorkingHour2->getId();
        $id3 = $WorkingHour3->getId();

        $this->assertEquals(0, $WorkingHour1->isCurrent(), 'Before WorkingHour1 should NOT be current');
        $this->assertEquals(1, $WorkingHour2->isCurrent(), 'Before WorkingHour2 should be current');
        $this->assertEquals(0, $WorkingHour3->isCurrent(), 'Before WorkingHour3 should NOT be current');

        $this->execute();

        $this->entityManager->clear();

        $repo = $this->entityManager->getRepository(WorkingHour::class);
        $WorkingHour1 = $repo->find($id1);
        $WorkingHour2 = $repo->find($id2);
        $WorkingHour3 = $repo->find($id3);

        $this->assertEquals(0, $WorkingHour1->isCurrent(), 'After WorkingHour1 should NOT be current');
        $this->assertEquals(1, $WorkingHour2->isCurrent(), 'After WorkingHour2 should be current');
        $this->assertEquals(1, $WorkingHour3->isCurrent(), 'After WorkingHour3 should be current');
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:workinghour:daily');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            ['command'  => $command->getName()], 
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Weekly planning records have been successfully updated for all employees.', $output);

    }
}
