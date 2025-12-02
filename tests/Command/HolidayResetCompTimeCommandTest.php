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
        $this->assertEquals(11, $aliceHoliday->getActualCredit(), 'Alice: actualCredit should be 11');
        $this->assertEquals(0, $aliceHoliday->getActualCompTime(), 'Alice: actualCompTime should be 0');
        $this->assertEquals(12, $aliceHoliday->getActualRemainder(), 'Alice: actualRemainder should be 12');
        $this->assertEquals(13, $aliceHoliday->getActualAnticipation(), 'Alice: actualAnticipation should be 13');

        $repo = $this->entityManager->getRepository(Holiday::class);
        $alexHoliday = $repo->findOneBy(['perso_id' => $alex->getId()]);
        $this->assertEquals(16.6, $alexHoliday->getActualCredit(), 'Alex: actualCredit should be 16.6');
        $this->assertEquals(0, $alexHoliday->getActualCompTime(), 'Alex: actualCompTime should be 0');
        $this->assertEquals(17.7, $alexHoliday->getActualRemainder(), 'Alex: actualRemainder should be 17.7');
        $this->assertEquals(18.8, $alexHoliday->getActualAnticipation(), 'Alex: actualAnticipation should be 18.8');

        $repo = $this->entityManager->getRepository(Holiday::class);
        $amyHoliday = $repo->findOneBy(['perso_id' => $amy->getId()]);
        $this->assertEquals(21.1, $amyHoliday->getActualCredit(), 'Amy: actualCredit should be 21.1');
        $this->assertEquals(0, $amyHoliday->getActualCompTime(), 'Amy: actualCompTime should be 0');
        $this->assertEquals(22.2, $amyHoliday->getActualRemainder(), 'Amy: actualRemainder should be 22.2');
        $this->assertEquals(23, $amyHoliday->getActualAnticipation(), 'Amy: actualAnticipation should be 23');

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