<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use Tests\PLBWebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class HolidayResetCompTimeCommandTest extends PLBWebTestCase
{

    public function testSomething(): void
    {
        $jdoe = $this->builder->build(Agent::class, [
            'login' => 'jdoe',
            'comp_time' => '120',
        ]);

        $jdupont = $this->builder->build(Agent::class, [
            'login' => 'jdupont',
            'comp_time' => '1.9',
        ]);

        $kboivin = $this->builder->build(agent::class, [
            'login' => 'kboivin',
            'comp_time' => '21.89',
        ]);

        $repo = $this->entityManager->getRepository(Agent::class);

        $agent = $repo->findOneBy(['login' => 'jdoe']);
        $this->assertEquals(120, $agent->getCompTime(), 'comp_time should be 120');

        $agent = $repo->findOneBy(['login' => 'jdupont']);
        $this->assertEquals(1.9, $agent->getCompTime(), 'comp_time should be 1.9');

        $agent = $repo->findOneBy(['login' => 'kboivin']);
        $this->assertEquals(21.89, $agent->getCompTime(), 'comp_time should be 21.89');

        $this->execute();

        $this->entityManager->clear();
        $repo = $this->entityManager->getRepository(Agent::class);

        $agent = $repo->findOneBy(['login' => 'jdoe']);
        $this->assertEquals(0, $agent->getCompTime(), 'After the command comp_time should be 0');
        
        $agent = $repo->findOneBy(['login' => 'jdupont']);
        $this->assertEquals(0, $agent->getCompTime(), 'After the command comp_time should be 0');
        
        $agent = $repo->findOneBy(['login' => 'kboivin']);
        $this->assertEquals(0, $agent->getCompTime(), 'After the command comp_time should be 0');
        
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
            '--not-really' => true
        ]);

        $commandTester->assertCommandIsSuccessful();
    }
}

