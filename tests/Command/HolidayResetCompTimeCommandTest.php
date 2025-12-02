<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use App\Entity\Holiday;
use Tests\PLBWebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
class HolidayResetCompTimeCommandTest extends PLBWebTestCase
{

    public function testHolidayResetCompTimeCommand(): void
    {
        $alice = $this->builder->build(Agent::class, array(
            'login' => 'alice',
            'conges_credit' => 11,
            'conges_reliquat' => 12,
            'conges_anticipation' => 13,
            'comp_time' => 14,
        ));

        $alex = $this->builder->build(Agent::class, array(
            'login' => 'alex',
            'conges_credit' => 16.6,
            'conges_reliquat' => 17.7,
            'conges_anticipation' => 18.8,
            'comp_time' => 19.9,
        ));

        $amy = $this->builder->build(Agent::class, array(
            'login' => 'amy',
            'conges_credit' => 21.1,
            'conges_reliquat' => 22.2,
            'conges_anticipation' => 23,
            'comp_time' => 24,
        ));

        $repo = $this->entityManager->getRepository(Agent::class);
        $aliceBefore = $repo->findOneBy(['login' => 'alice']);
        $this->assertEquals(14, $aliceBefore->getCompTime(), 'comp_time should be 14');
        $alexBefore = $repo->findOneBy(['login' => 'alex']);
        $this->assertEquals(19.9, $alexBefore->getCompTime(), 'comp_time should be 19');
        $amyBefore = $repo->findOneBy(['login' => 'amy']);
        $this->assertEquals(24, $amyBefore->getCompTime(), 'comp_time should be 24');

        $this->execute();
        $this->entityManager->clear();
        
        $repo = $this->entityManager->getRepository(Agent::class);
        $aliceAfter = $repo->findOneBy(['login' => 'alice']);
        $this->assertEquals(0.0, $aliceAfter->getCompTime(), 'After the command comp_time should be 0');
        $alexAfter = $repo->findOneBy(['login' => 'alex']);
        $this->assertEquals(0.0, $alexAfter->getCompTime(), 'After the command comp_time should be 0');
        $amyAfter = $repo->findOneBy(['login' => 'amy']);
        $this->assertEquals(0.0, $amyAfter->getCompTime(), 'After the command comp_time should be 0');

        $repo = $this->entityManager->getRepository(Holiday::class);
        $aliceHoliday = $repo->findOneBy(['perso_id' => $alice->getId()]);
        $this->assertEquals(11, $aliceHoliday->getActualCredit(), '');
        $this->assertEquals(0, $aliceHoliday->getActualCompTime(), '');
        $this->assertEquals(12, $aliceHoliday->getActualRemainder(), '');
        $this->assertEquals(13, $aliceHoliday->getActualAnticipation(), '');

        $repo = $this->entityManager->getRepository(Holiday::class);
        $alexHoliday = $repo->findOneBy(['perso_id' => $alex->getId()]);
        $this->assertEquals(16.6, $alexHoliday->getActualCredit(), '');
        $this->assertEquals(0, $alexHoliday->getActualCompTime(), '');
        $this->assertEquals(17.7, $alexHoliday->getActualRemainder(), '');
        $this->assertEquals(18.8, $alexHoliday->getActualAnticipation(), '');

        $repo = $this->entityManager->getRepository(Holiday::class);
        $amyHoliday = $repo->findOneBy(['perso_id' => $amy->getId()]);
        $this->assertEquals(21.1, $amyHoliday->getActualCredit(), '');
        $this->assertEquals(0, $amyHoliday->getActualCompTime(), '');
        $this->assertEquals(22.2, $amyHoliday->getActualRemainder(), '');
        $this->assertEquals(23, $amyHoliday->getActualAnticipation(), '');

        $this->restore();
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:holiday:reset:comp-time');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--force' => true
        ]);
        $commandTester->assertCommandIsSuccessful();
    }
    
}