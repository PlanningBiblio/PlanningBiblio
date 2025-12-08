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
            'login' => 'alice', 'conges_credit' => 11, 'comp_time' => 22, 'conges_anticipation' => 33 
        ]);
        $alex = $this->builder->build(Agent::class, [
            'login' => 'alex', 'conges_credit' => 44, 'comp_time' => 55, 'conges_anticipation' => 66 
        ]);
        $amy = $this->builder->build(Agent::class, [
            'login' => 'amy', 'conges_credit' => 77, 'comp_time' => 88, 'conges_anticipation' => 99 
        ]);

        $repo = $this->entityManager->getRepository(Agent::class);

        $this->assertEquals(
            11,
            $alice->getHolidayCredit(),
            'alice HolidayCredit should be 11.'
        );
        $this->assertEquals(
            22,
            $alice->getHolidayCompTime(),
            'alice HolidayCompTime should be 22.'
        );
        $this->assertEquals(
            33,
            $alice->getHolidayAnticipation(),
            'alice HolidayAnticipation should be 33.'
        );

        $this->assertEquals(
            44,
            $alex->getHolidayCredit(),
            'alex HolidayCredit should be 44.'
        );
        $this->assertEquals(
            55,
            $alex->getHolidayCompTime(),
            'alex HolidayCompTime should be 55.'
        );
        $this->assertEquals(
            66,
            $alex->getHolidayAnticipation(),
            'alex HolidayAnticipation should be 66.'
        );

        $this->assertEquals(
            77,
            $amy->getHolidayCredit(),
            'amy HolidayCredit should be 77.'
        );
        $this->assertEquals(
            88,
            $amy->getHolidayCompTime(),
            'amy HolidayCompTime should be 88.'
        );
        $this->assertEquals(
            99,
            $amy->getHolidayAnticipation(),
            'amy HolidayAnticipation should be 99.'
        );
        
        $this->execute();
        $this->entityManager->clear();

        $repo = $this->entityManager->getRepository(Holiday::class);

        $this->assertEquals(
            11,
            $repo->findOneBy(['perso_id'=>$alice->getId()])->getActualCredit(),
            'Holiday actualCredit should corresponde to Agent.'
        );
        $this->assertEquals(
            22,
            $repo->findOneBy(['perso_id'=>$alice->getId()])->getActualCompTime(),
            'Holiday actualCompTime should corresponde to Agent.'
        );
        $this->assertEquals(
            33,
            $repo->findOneBy(['perso_id'=>$alice->getId()])->getActualAnticipation(),
            'Holiday actualAnticipation should corresponde to Agent.'
        );
        $this->assertEquals(
            0,
            $repo->findOneBy(['perso_id'=>$alice->getId()])->getActualRemainder(),
            'Holiday actualRemainder should be reset to 0.'
        );

        $this->assertEquals(
            44,
            $repo->findOneBy(['perso_id'=>$alex->getId()])->getActualCredit(),
            'Holiday actualCredit should corresponde to Agent.'
        );
        $this->assertEquals(
            55,
            $repo->findOneBy(['perso_id'=>$alex->getId()])->getActualCompTime(),
            'Holiday actualCompTime should corresponde to Agent.'
        );
        $this->assertEquals(
            66,
            $repo->findOneBy(['perso_id'=>$alex->getId()])->getActualAnticipation(),
            'Holiday actualAnticipation should corresponde to Agent.'
        );
        $this->assertEquals(
            0,
            $repo->findOneBy(['perso_id'=>$alex->getId()])->getActualRemainder(),
            'Holiday actualRemainder should be reset to 0.'
        );

        $this->assertEquals(
            77,
            $repo->findOneBy(['perso_id'=>$amy->getId()])->getActualCredit(),
            'Holiday actualCredit should corresponde to Agent.'
        );
        $this->assertEquals(
            88,
            $repo->findOneBy(['perso_id'=>$amy->getId()])->getActualCompTime(),
            'Holiday actualCompTime should corresponde to Agent.'
        );
        $this->assertEquals(
            99,
            $repo->findOneBy(['perso_id'=>$amy->getId()])->getActualAnticipation(),
            'Holiday actualAnticipation should corresponde to Agent.'
        );
        $this->assertEquals(
            0,
            $repo->findOneBy(['perso_id'=>$amy->getId()])->getActualRemainder(),
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

