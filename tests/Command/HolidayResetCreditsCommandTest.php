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
    protected function setUp(): void
    {
        parent::setUp();

        $this->restore();

        $this->builder->build(Agent::class, array(
            'login' => 'jdupont',
            'conges_credit' => "11",
            'conges_reliquat' => "22",
            'conges_anticipation' => "33",
            'comp_time' => "44",
            'conges_annuel' => "55",
        ));
        $this->builder->build(Agent::class, array(
            'login' => 'alice',
            'conges_credit' => "1.1",
            'conges_reliquat' => "2.2",
            'conges_anticipation' => "3.3",
            'comp_time' => "4.4",
            'conges_annuel' => "5.5",
        ));
        $this->builder->build(Agent::class, array(
            'login' => 'alex',
            'conges_credit' => "1.11",
            'conges_reliquat' => "2.22",
            'conges_anticipation' => "3.33",
            'comp_time' => "4.44",
            'conges_annuel' => "5.55",
        ));

    }
    
    public function testConfigOn(): void
    {
        $this->setParam('Conges-transfer-comp-time', 1);
        
        $jdupontBefore = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $this->assertEquals(11, $jdupontBefore->getHolidayCredit(), 'Before jdupont conges_credit should be 11');
        $this->assertEquals(22, $jdupontBefore->getRemainder(), 'Before jdupont conges_reliquat should be 22');
        $this->assertEquals(33, $jdupontBefore->getAnticipation(), 'Before jdupont conges_anticipation should be 33');
        $this->assertEquals(44, $jdupontBefore->getCompTime(), 'Before jdupont comp_time should be 44');
        $this->assertEquals(55, $jdupontBefore->getAnnualCredit(), 'Before jdupont conges_annuel should be 55');

        $aliceBefore = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alice']);
        $this->assertEquals(1.1, $aliceBefore->getHolidayCredit(), 'Before alice conges_credit should be 1.1');
        $this->assertEquals(2.2, $aliceBefore->getRemainder(), 'Before alice conges_reliquat should be 2.2');
        $this->assertEquals(3.3, $aliceBefore->getAnticipation(), 'Before alice conges_anticipation should be 3.3');
        $this->assertEquals(4.4, $aliceBefore->getCompTime(), 'Before alice comp_time should be 4.4');
        $this->assertEquals(5.5, $aliceBefore->getAnnualCredit(), 'Before alice conges_annuel should be 5.5');

        $alexBefore = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $this->assertEquals(1.11, $alexBefore->getHolidayCredit(), 'Before alex conges_credit should be 1.11');
        $this->assertEquals(2.22, $alexBefore->getRemainder(), 'Before alex conges_reliquat should be 2.22');
        $this->assertEquals(3.33, $alexBefore->getAnticipation(), 'Before alex conges_anticipation should be 3.33');
        $this->assertEquals(4.44, $alexBefore->getCompTime(), 'Before alex comp_time should be 4.44');
        $this->assertEquals(5.55, $alexBefore->getAnnualCredit(), 'Before alex conges_annuel should be 5.55');

        $this->execute();
        $this->entityManager->clear();

        $jdupontAfter = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $this->assertEquals(22, $jdupontAfter->getHolidayCredit(), 'After jdupont conges_credit should be 22');
        $this->assertEquals(55, $jdupontAfter->getRemainder(), 'After jdupont conges_reliquat should be 55');
        $this->assertEquals(0, $jdupontAfter->getAnticipation(), 'After jdupont conges_anticipation should be 0');
        $this->assertEquals(0, $jdupontAfter->getCompTime(), 'After jdupont comp_time should be 0');
        $this->assertEquals(55, $jdupontAfter->getAnnualCredit(), 'After jdupont conges_annuel should be 55');

        $aliceAfter = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alice']);
        $this->assertEquals(2.2, $aliceAfter->getHolidayCredit(), 'After alice conges_credit should be 2.2');
        $this->assertEquals(5.5, $aliceAfter->getRemainder(), 'After alice conges_reliquat should be 5.5');
        $this->assertEquals(0, $aliceAfter->getAnticipation(), 'After alice conges_anticipation should be 0');
        $this->assertEquals(0, $aliceAfter->getCompTime(), 'After alice comp_time should be 0');
        $this->assertEquals(5.5, $aliceAfter->getAnnualCredit(), 'After alice conges_annuel should be 5.5');

        $alexAfter = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $this->assertEquals(2.22, $alexAfter->getHolidayCredit(), 'After alex conges_credit should be 2.22');
        $this->assertEquals(5.55, $alexAfter->getRemainder(), 'After alex conges_reliquat should be 5.55');
        $this->assertEquals(0, $alexAfter->getAnticipation(), 'After alex conges_anticipation should be 0');
        $this->assertEquals(0, $alexAfter->getCompTime(), 'After alex comp_time should be 0');
        $this->assertEquals(5.55, $alexAfter->getAnnualCredit(), 'After alex conges_annuel should be 5.55');

        $jdupontCongeAfter = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $jdupontAfter->getId()]);
        $this->assertEquals(11, $jdupontCongeAfter->getPreviousCredit(), 'After Holiday solde_prec should be 11');
        $this->assertEquals(44, $jdupontCongeAfter->getPreviousCompTime(), 'After Holiday recup_prec should be 44');
        $this->assertEquals(22, $jdupontCongeAfter->getPreviousRemainder(), 'After Holiday reliquat_prec should be 22');
        $this->assertEquals(33, $jdupontCongeAfter->getPreviousAnticipation(), 'After Holiday anticipation_prec should be 33');
        $this->assertEquals(22, $jdupontCongeAfter->getActualCredit(), 'After Holiday solde_actuel should be 22');
        $this->assertEquals(0, $jdupontCongeAfter->getActualCompTime(), 'After Holiday recup_actuel should be 0');
        $this->assertEquals(55, $jdupontCongeAfter->getActualRemainder(), 'After Holiday reliquat_actuel should be 55');
        $this->assertEquals(0, $jdupontCongeAfter->getActualAnticipation(), 'After Holiday anticipation_actuel should be 0');
        
        $aliceCongeAfter = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $aliceAfter->getId()]);
        $this->assertEquals(1.1, $aliceCongeAfter->getPreviousCredit(), 'After Holiday solde_prec should be 11');
        $this->assertEquals(4.4, $aliceCongeAfter->getPreviousCompTime(), 'After Holiday recup_prec should be 44');
        $this->assertEquals(2.2, $aliceCongeAfter->getPreviousRemainder(), 'After Holiday reliquat_prec should be 22');
        $this->assertEquals(3.3, $aliceCongeAfter->getPreviousAnticipation(), 'After Holiday anticipation_prec should be 33');
        $this->assertEquals(2.2, $aliceCongeAfter->getActualCredit(), 'After Holiday solde_actuel should be 22');
        $this->assertEquals(0, $aliceCongeAfter->getActualCompTime(), 'After Holiday recup_actuel should be 0');
        $this->assertEquals(5.5, $aliceCongeAfter->getActualRemainder(), 'After Holiday reliquat_actuel should be 55');
        $this->assertEquals(0, $aliceCongeAfter->getActualAnticipation(), 'After Holiday anticipation_actuel should be 0');

        $alexCongeAfter = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $alexAfter->getId()]);
        $this->assertEquals(1.11, $alexCongeAfter->getPreviousCredit(), 'After Holiday solde_prec should be 11');
        $this->assertEquals(4.44, $alexCongeAfter->getPreviousCompTime(), 'After Holiday recup_prec should be 44');
        $this->assertEquals(2.22, $alexCongeAfter->getPreviousRemainder(), 'After Holiday reliquat_prec should be 22');
        $this->assertEquals(3.33, $alexCongeAfter->getPreviousAnticipation(), 'After Holiday anticipation_prec should be 33');
        $this->assertEquals(2.22, $alexCongeAfter->getActualCredit(), 'After Holiday solde_actuel should be 22');
        $this->assertEquals(0, $alexCongeAfter->getActualCompTime(), 'After Holiday recup_actuel should be 0');
        $this->assertEquals(5.55, $alexCongeAfter->getActualRemainder(), 'After Holiday reliquat_actuel should be 55');
        $this->assertEquals(0, $alexCongeAfter->getActualAnticipation(), 'After Holiday anticipation_actuel should be 0');
    }

    public function testConfigOff(): void
    {
        $this->setParam('Conges-transfer-comp-time', 0);

        $jdupontBefore = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $this->assertEquals(11, $jdupontBefore->getHolidayCredit(), 'Before jdupont conges_credit should be 11');
        $this->assertEquals(22, $jdupontBefore->getRemainder(), 'Before jdupont conges_reliquat should be 22');
        $this->assertEquals(33, $jdupontBefore->getAnticipation(), 'Before jdupont conges_anticipation should be 33');
        $this->assertEquals(44, $jdupontBefore->getCompTime(), 'Before jdupont comp_time should be 44');
        $this->assertEquals(55, $jdupontBefore->getAnnualCredit(), 'Before jdupont conges_annuel should be 55');

        $aliceBefore = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alice']);
        $this->assertEquals(1.1, $aliceBefore->getHolidayCredit(), 'Before alice conges_credit should be 1.1');
        $this->assertEquals(2.2, $aliceBefore->getRemainder(), 'Before alice conges_reliquat should be 2.2');
        $this->assertEquals(3.3, $aliceBefore->getAnticipation(), 'Before alice conges_anticipation should be 3.3');
        $this->assertEquals(4.4, $aliceBefore->getCompTime(), 'Before alice comp_time should be 4.4');
        $this->assertEquals(5.5, $aliceBefore->getAnnualCredit(), 'Before alice conges_annuel should be 5.5');

        $alexBefore = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $this->assertEquals(1.11, $alexBefore->getHolidayCredit(), 'Before alex conges_credit should be 1.11');
        $this->assertEquals(2.22, $alexBefore->getRemainder(), 'Before alex conges_reliquat should be 2.22');
        $this->assertEquals(3.33, $alexBefore->getAnticipation(), 'Before alex conges_anticipation should be 3.33');
        $this->assertEquals(4.44, $alexBefore->getCompTime(), 'Before alex comp_time should be 4.44');
        $this->assertEquals(5.55, $alexBefore->getAnnualCredit(), 'Before alex conges_annuel should be 5.55');

        $this->execute();
        $this->entityManager->clear();

        $jdupontAfter = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont']);
        $this->assertEquals(22, $jdupontAfter->getHolidayCredit(), 'After jdupont conges_credit should be 22');
        $this->assertEquals(11, $jdupontAfter->getRemainder(), 'After jdupont conges_reliquat should be 11');
        $this->assertEquals(0, $jdupontAfter->getAnticipation(), 'After jdupont conges_anticipation should be 0');
        $this->assertEquals(44, $jdupontAfter->getCompTime(), 'After jdupont comp_time should be 44');
        $this->assertEquals(55, $jdupontAfter->getAnnualCredit(), 'After jdupont conges_annuel should be 55');

        $aliceAfter = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alice']);
        $this->assertEquals(2.2, $aliceAfter->getHolidayCredit(), 'After alice conges_credit should be 2.2');
        $this->assertEquals(1.1, $aliceAfter->getRemainder(), 'After alice conges_reliquat should be 1.1');
        $this->assertEquals(0, $aliceAfter->getAnticipation(), 'After alice conges_anticipation should be 0');
        $this->assertEquals(4.4, $aliceAfter->getCompTime(), 'After alice comp_time should be 4.4');
        $this->assertEquals(5.5, $aliceAfter->getAnnualCredit(), 'After alice conges_annuel should be 5.5');

        $alexAfter = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $this->assertEquals(2.22, $alexAfter->getHolidayCredit(), 'After alex conges_credit should be 2.22');
        $this->assertEquals(1.11, $alexAfter->getRemainder(), 'After alex conges_reliquat should be 1.11');
        $this->assertEquals(0, $alexAfter->getAnticipation(), 'After alex conges_anticipation should be 0');
        $this->assertEquals(4.44, $alexAfter->getCompTime(), 'After alex comp_time should be 4.44');
        $this->assertEquals(5.55, $alexAfter->getAnnualCredit(), 'After alex conges_annuel should be 5.55');

        $jdupontCongeAfter = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $jdupontAfter->getId()]);
        $this->assertEquals(11, $jdupontCongeAfter->getPreviousCredit(), 'After Holiday solde_prec should be 11');
        $this->assertEquals(44, $jdupontCongeAfter->getPreviousCompTime(), 'After Holiday recup_prec should be 44');
        $this->assertEquals(22, $jdupontCongeAfter->getPreviousRemainder(), 'After Holiday reliquat_prec should be 22');
        $this->assertEquals(33, $jdupontCongeAfter->getPreviousAnticipation(), 'After Holiday anticipation_prec should be 33');
        $this->assertEquals(22, $jdupontCongeAfter->getActualCredit(), 'After Holiday solde_actuel should be 22');
        $this->assertEquals(44, $jdupontCongeAfter->getActualCompTime(), 'After Holiday recup_actuel should be 44');
        $this->assertEquals(11, $jdupontCongeAfter->getActualRemainder(), 'After Holiday reliquat_actuel should be 11');
        $this->assertEquals(0, $jdupontCongeAfter->getActualAnticipation(), 'After Holiday anticipation_actuel should be 0');
        
        $aliceCongeAfter = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $aliceAfter->getId()]);
        $this->assertEquals(1.1, $aliceCongeAfter->getPreviousCredit(), 'After Holiday solde_prec should be 1.1');
        $this->assertEquals(4.4, $aliceCongeAfter->getPreviousCompTime(), 'After Holiday recup_prec should be 4.4');
        $this->assertEquals(2.2, $aliceCongeAfter->getPreviousRemainder(), 'After Holiday reliquat_prec should be 2.2');
        $this->assertEquals(3.3, $aliceCongeAfter->getPreviousAnticipation(), 'After Holiday anticipation_prec should be 3.3');
        $this->assertEquals(2.2, $aliceCongeAfter->getActualCredit(), 'After Holiday solde_actuel should be 2.2');
        $this->assertEquals(4.4, $aliceCongeAfter->getActualCompTime(), 'After Holiday recup_actuel should be 4.4');
        $this->assertEquals(1.1, $aliceCongeAfter->getActualRemainder(), 'After Holiday reliquat_actuel should be 1.1');
        $this->assertEquals(0, $aliceCongeAfter->getActualAnticipation(), 'After Holiday anticipation_actuel should be 0');

        $alexCongeAfter = $this->entityManager->getRepository(Holiday::class)->findOneBy(['perso_id' => $alexAfter->getId()]);
        $this->assertEquals(1.11, $alexCongeAfter->getPreviousCredit(), 'After Holiday solde_prec should be 1.11');
        $this->assertEquals(4.44, $alexCongeAfter->getPreviousCompTime(), 'After Holiday recup_prec should be 4.44');
        $this->assertEquals(2.22, $alexCongeAfter->getPreviousRemainder(), 'After Holiday reliquat_prec should be 2.22');
        $this->assertEquals(3.33, $alexCongeAfter->getPreviousAnticipation(), 'After Holiday anticipation_prec should be 3.33');
        $this->assertEquals(2.22, $alexCongeAfter->getActualCredit(), 'After Holiday solde_actuel should be 2.22');
        $this->assertEquals(4.44, $alexCongeAfter->getActualCompTime(), 'After Holiday recup_actuel should be 4.44');
        $this->assertEquals(1.11, $alexCongeAfter->getActualRemainder(), 'After Holiday reliquat_actuel should be 1.11');
        $this->assertEquals(0, $alexCongeAfter->getActualAnticipation(), 'After Holiday anticipation_actuel should be 0');
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
