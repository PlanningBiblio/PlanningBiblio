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
        $alice = new Agent();
        $alice->setLogin('alice');
        $alice->setLogin('alice');
        $alice->setMail('alice@example.com');
        $alice->setFirstname('Doe');
        $alice->setLastname('Alice');
        $alice->setStatus('');
        $alice->setCategory('Titulaire');
        $alice->setService('');
        $alice->setArrival(new \DateTime('2021-12-12 00:00:00'));
        $alice->setDeparture(new \DateTime('2028-12-12 00:00:00'));
        $alice->setSkills('');
        $alice->setActive('Actif');
        $alice->setACL([1,1,1]);
        $alice->setPassword('password');
        $alice->setComment('111');
        $alice->setLastLogin(new \DateTime(''));
        $alice->setWeeklyServiceHours(0);
        $alice->setWeeklyWorkingHours(0);
        $alice->setSites('["3"]' );
        $alice->setWorkingHours(' [["09:00:00","12:00:00","13:00:00","17:00:00"],["09:00:00","12:00:00","13:00:00","17:00:00"],["09:00:00","12:00:00","13:00:00","17:00:00"],["09:00:00","12:00:00","13:00:00","17:00:00"],["09:00:00","12:00:00","13:00:00","17:00:00"],["","","",""]] ');
        $alice->setInformations('');
        $alice->setRecovery('');
        $alice->setMailsResponsables('');
        $alice->setCheckHamac(1);
        $alice->setCheckMsGraph(0);
        $alice->setDeletion(0);
        $alice->setHolidayCredit(11);
        $alice->setCompTime(22);
        $alice->setAnticipation(33);
        $this->entityManager->persist($alice);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $repo = $this->entityManager->getRepository(Agent::class);
        $this->assertNotNull($repo->findOneBy(['id'=>$alice->getId()]), 'Alice should be persisted in the database.');

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
        foreach ($repo->findAll() as $agent)
        $this->assertEquals(
            0.00,
            $agent->getRemainder(),
            'Agent remainder should be reset to 0.'
        );
        
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
            '--not-really' => true
        ]);
        $commandTester->assertCommandIsSuccessful();
    }
}
