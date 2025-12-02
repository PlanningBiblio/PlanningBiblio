<?php

namespace App\Tests\Command;

use Tests\PLBWebTestCase;
use App\Entity\Agent;
use App\Entity\Holiday;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class HolidayResetRemainderCommandTest extends PLBWebTestCase
{
    public function testResetRemainderCommand(): void
    {
        $alice = $this->builder->build(Agent::class, [
            'login' => 'alice', 'prenom' => 'Alice',
            'supprime' => 0, 'conges_credit' => 11, 'comp_time' => 22, 'conges_anticipation' =>33 
        ]);
        
        $repo = $this->entityManager->getRepository(Agent::class);
        $this->assertEquals(
            11,
            $alice->getHolidayCredit(),
            'Agent HolidayCredit should be 11.'
        );
        $this->assertEquals(
            22,
            $alice->getHolidayCompTime(),
            'Agent HolidayCompTime should be 22.'
        );
        $this->assertEquals(
            33,
            $alice->getHolidayAnticipation(),
            'Agent HolidayAnticipation should be 33.'
        );
        
        $this->execute();
        $this->entityManager->clear();

        $repo = $this->entityManager->getRepository(Holiday::class);
        $this->assertEquals(
            11,
            $repo->findOneBy(['perso_id'=>$alice->getId()])->getActualCredit(),
            'Holiday actualCredit should remain unchanged.'
        );
        $this->assertEquals(
            22,
            $repo->findOneBy(['perso_id'=>$alice->getId()])->getActualCompTime(),
            'Holiday actualCompTime should remain unchanged.'
        );
        $this->assertEquals(
            33,
            $repo->findOneBy(['perso_id'=>$alice->getId()])->getActualAnticipation(),
            'Holiday actualAnticipation should remain unchanged.'
        );
        $this->assertEquals(
            0,
            $repo->findOneBy(['perso_id'=>$alice->getId()])->getActualRemainder(),
            'Holiday actualRemainder should be reset to 0.'
        );
        $repo = $this->entityManager->getRepository(Agent::class);
        foreach ($repo->findAll() as $agent){
            $this->assertEquals(
                0.00,
                $agent->getHolidayRemainder(),
                'Agent remainder should be reset to 0.'
            );
        }
        
        $this->restore();
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:holiday:reset:remainder');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--force' => true
        ]);
        $commandTester->assertCommandIsSuccessful();
    }
}
