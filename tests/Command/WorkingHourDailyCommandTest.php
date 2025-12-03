<?php

namespace App\Tests\Command;

use DateTime;
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
            'debut' => new DateTime("2000-01-01"),
        )); 

        $WorkingHour2 = $this->builder->build(WorkingHour::class,array(
            'perso_id' => 1,
            'actuel' => 1,
            'valide' => 1,
            'debut' => new DateTime("2000-01-01"),
        )); 

        $WorkingHour3 = $this->builder->build(WorkingHour::class,array(
            'perso_id' => 1,
            'actuel' => 0,
            'valide' => 1,
            'debut' => new DateTime(),
        )); 

        $this->entityManager->persist($WorkingHour1);
        $this->entityManager->persist($WorkingHour2);
        $this->entityManager->persist($WorkingHour3);
        $this->entityManager->flush();

        $id1 = $WorkingHour1->getId();
        $id2 = $WorkingHour2->getId();
        $id3 = $WorkingHour3->getId();

        $repo = $this->entityManager->getRepository(WorkingHour::class);
        $wh1 = $repo->find($id1);
        $wh2 = $repo->find($id2);
        $wh3 = $repo->find($id3);

        $this->assertEquals(0, $wh1->isCurrent(), 'Before WorkingHour1 should NOT be current');
        $this->assertEquals(1, $wh2->isCurrent(), 'Before WorkingHour2 should NOT be current');
        $this->assertEquals(0, $wh3->isCurrent(), 'Before WorkingHour3 should NOT be current');

        $this->execute();

        $this->entityManager->clear();

        $repo = $this->entityManager->getRepository(WorkingHour::class);
        $wh11 = $repo->find($id1);
        $wh22 = $repo->find($id2);
        $wh33 = $repo->find($id3);

        $this->assertEquals(0, $wh11->isCurrent(), 'After WorkingHour1 should NOT be current');
        $this->assertEquals(0, $wh22->isCurrent(), 'After WorkingHour2 should NOT be current');
        $this->assertEquals(1, $wh33->isCurrent(), 'After WorkingHour3 should NOT be current');
        
        $this->restore();
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:workinghour:daily');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName()
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Weekly planning records have been successfully updated for all employees.', $output);

    }


}
