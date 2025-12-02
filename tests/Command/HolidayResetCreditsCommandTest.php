<?php

namespace App\Tests\Command;
use Tests\PLBWebTestCase;
use App\Entity\Agent;
use App\Entity\Holiday;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

class HolidayResetCreditsCommandTest extends PLBWebTestCase
{
    public function testConfigOn(): void
    {
        $this->setParam('Conges-transfer-comp-time', 1);
        
        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jduponttt',
            'conges_credit' => "11",
            'conges_reliquat' => "22",
            'conges_anticipation' => "33",
            'comp_time' => "44",
            'conges_annuel' => "55",
        ));

        $agentBefore = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jduponttt']);
        $this->assertEquals(11, $agentBefore->getHolidayCredit(), 'Before Agent conges_credit');
        $this->assertEquals(22, $agentBefore->getRemainder(), 'Before Agent conges_reliquat');
        $this->assertEquals(33, $agentBefore->getAnticipation(), 'Before Agent conges_anticipation');
        $this->assertEquals(44, $agentBefore->getCompTime(), 'Before Agent comp_time');
        $this->assertEquals(55, $agentBefore->getAnnualCredit(), 'Before Agent conges_annuel');

        $this->execute();
        $this->entityManager->clear();

        $agentAfter = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jduponttt']);
        $this->assertEquals(22, $agentAfter->getHolidayCredit(), 'After Agent conges_credit');
        $this->assertEquals(55, $agentAfter->getRemainder(), 'After Agent conges_reliquat');
        $this->assertEquals(0, $agentAfter->getAnticipation(), 'After Agent conges_anticipation');
        $this->assertEquals(0, $agentAfter->getCompTime(), 'After Agent comp_time');
        $this->assertEquals(55, $agentAfter->getAnnualCredit(), 'After Agent conges_annuel');

        $congeAfter = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $jdupont->getId()]);
        $this->assertEquals(11, $congeAfter->getPreviousCredit(), 'After Holiday solde_prec');
        $this->assertEquals(44, $congeAfter->getPreviousCompTime(), 'After Holiday recup_prec');
        $this->assertEquals(22, $congeAfter->getPreviousRemainder(), 'After Holiday reliquat_prec');
        $this->assertEquals(33, $congeAfter->getPreviousAnticipation(), 'After Holiday anticipation_prec');
        $this->assertEquals(22, $congeAfter->getActualCredit(), 'After Holiday solde_actuel');
        $this->assertEquals(0, $congeAfter->getActualCompTime(), 'After Holiday recup_actuel');
        $this->assertEquals(55, $congeAfter->getActualRemainder(), 'After Holiday reliquat_actuel');
        $this->assertEquals(0, $congeAfter->getActualAnticipation(), 'After Holiday anticipation_actuel');
        
        $this->restore();
    }

    public function testConfigOff(): void
    {
        $this->setParam('Conges-transfer-comp-time', 0);

        $jdupont = $this->builder->build(Agent::class, array(
            'login' => 'jdupont',
            'conges_credit' => "11",
            'conges_reliquat' => "22",
            'conges_anticipation' => "33",
            'comp_time' => "44",
            'conges_annuel' => "55",
        ));

        $agentBefore = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $this->assertEquals(11, $agentBefore->getHolidayCredit(), 'Before Agent conges_credit');
        $this->assertEquals(22, $agentBefore->getRemainder(), 'Before Agent conges_reliquat');
        $this->assertEquals(33, $agentBefore->getAnticipation(), 'Before Agent conges_anticipation');
        $this->assertEquals(44, $agentBefore->getCompTime(), 'Before Agent comp_time');
        $this->assertEquals(55, $agentBefore->getAnnualCredit(), 'Before Agent conges_annuel');

        $this->execute();
        $this->entityManager->clear();

        $agentAfter = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $this->assertEquals(22, $agentAfter->getHolidayCredit(), 'After Agent conges_credit');
        $this->assertEquals(11, $agentAfter->getRemainder(), 'After Agent conges_reliquat');
        $this->assertEquals(0, $agentAfter->getAnticipation(), 'After Agent conges_anticipation');
        $this->assertEquals(44, $agentAfter->getCompTime(), 'After Agent comp_time');
        $this->assertEquals(55, $agentAfter->getAnnualCredit(), 'After Agent conges_annuel');

        $congeAfter = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' =>$jdupont->getId()]);
        $this->assertEquals(11, $congeAfter->getPreviousCredit(), 'After Holiday solde_prec');
        $this->assertEquals(44, $congeAfter->getPreviousCompTime(), 'After Holiday recup_prec');
        $this->assertEquals(22, $congeAfter->getPreviousRemainder(), 'After Holiday reliquat_prec');
        $this->assertEquals(33, $congeAfter->getPreviousAnticipation(), 'After Holiday anticipation_prec');
        $this->assertEquals(22, $congeAfter->getActualCredit(), 'After Holiday solde_actuel');
        $this->assertEquals(44, $congeAfter->getActualCompTime(), 'After Holiday recup_actuel');
        $this->assertEquals(11, $congeAfter->getActualRemainder(), 'After Holiday reliquat_actuel');
        $this->assertEquals(0, $congeAfter->getActualAnticipation(), 'After Holiday anticipation_actuel');
        
        $this->restore();
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:holiday:reset:credits');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--force' => true
        ]);
        $commandTester->assertCommandIsSuccessful();
    }
}
