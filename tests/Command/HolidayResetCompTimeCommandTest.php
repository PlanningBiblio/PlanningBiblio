<?php
//fini
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
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jduponttt', 'nom' => 'Duponttt', 'prenom' => 'Jean', 'temps'=>'',
            'droits' => array(3,4,5,6,9,17,20,21,22,23,25,99,100,201,202,301,302,401,402,501,502,601,602,701,801,802,901,1001,1002,1101,1201,1301),
            'sites' => '["1"]',
            'conges_credit' => "3.8",
            'conges_reliquat' => "3.8",
            'conges_anticipation' => "0",
            'comp_time' => "1.9",
            'conges_annuel' => "3.8",
        ));

        $repo = $this->entityManager->getRepository(Agent::class);
        $agent = $repo->findOneBy(['login' => 'jduponttt']);
        $this->assertNotNull($agent, 'Agent should exist in database');
        $this->assertEquals(1.9, $agent->getCompTime(), 'comp_time should be 1.9');

        $this->execute();
        $this->entityManager->clear();
        
        $repo = $this->entityManager->getRepository(Agent::class);
        $agentAfter = $repo->findOneBy(['login' => 'jduponttt']);
        $this->assertNotNull($agentAfter, 'Agent should still exist after cron');
        $this->assertEquals(0.0, $agentAfter->getCompTime(), 'After the command comp_time should be 0');
        
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