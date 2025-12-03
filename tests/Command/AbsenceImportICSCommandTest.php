<?php

namespace App\Tests\Command;

use App\Entity\Absence;
use App\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\PLBWebTestCase;

class AbsenceImportICSCommandTest extends PLBWebTestCase
{
    private string $lockFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder->delete(Agent::class);

        $this->builder->build(Agent::class, [
            'login' => 'alice',
            'mail' => 'alice',
            'matricule' => 'alice',
        ]);

        $this->lockFile = sys_get_temp_dir() . '/plannoAbsenceImportICS.lock';
        if (file_exists($this->lockFile)) {
            @unlink($this->lockFile);
        }

    }

    public function testExitsWhenLockFileIsRecent(): void
    {
        file_put_contents($this->lockFile, '');
        touch($this->lockFile, time());

        $exited = false;
        try {
            $this->execute();
        } catch (\Exception $e) {
            $exited = true;
        }

        $this->assertTrue($exited, 'it should exit if find lock file recent');
    }

    // Test ICS Server 1 with "login" as variable
    public function testAbsenceImportICSServer1WithLogin(): void
    {
        $this->setParam('ICS-Server1', __DIR__ . '/../data/absenceImport[login].ics');
        $this->check(1, 'ICS Server 1 with login');
    }

    // Test ICS Server 1 with "email" as variable
    public function testAbsenceImportICSServer1WithEmail(): void
    {
        $this->setParam('ICS-Server1', __DIR__ . '/../data/absenceImport[email].ics');
        $this->check(1, 'ICS Server 1 with e-mail');
    }

    // Test ICS Server 1 with "mail" as variable
    public function testAbsenceImportICSServer1WithMail(): void
    {
        $this->setParam('ICS-Server1', __DIR__ . '/../data/absenceImport[mail].ics');
        $this->check(1, 'ICS Server 1 with e-mail');
    }

    // Test ICS Server 1 with "matricule" as variable
    public function testAbsenceImportICSServer1WithEmployeeNumber(): void
    {
        $this->setParam('ICS-Server1', __DIR__ . '/../data/absenceImport[matricule].ics');
        $this->check(1, 'ICS Server 1 with employee number');
    }

    // Test ICS Server 3
    public function testAbsenceImportICSServer3(): void
    {
        $this->setParam('ICS-Server1', '');
        $this->setParam('ICS-Server3', 1);
        $this->check(3, 'ICS Server 3');
    }

    private function check($serverNumber, $test)
    {
        $ICSCheck = [0,0,0];
        $ICSCheck[$serverNumber -1] = 1;

        $user = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alice']);
        $user->setIcsCheck($ICSCheck);
        $user->setIcsUrl( $serverNumber == 3 ? __DIR__ . '/../data/absenceImport.ics' : '');

        $this->entityManager->flush();

        $abs = $this->entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $user->getId()]);
        $this->assertNull( $abs, $test . ': Before the command, absence should not exist');

        $this->execute();

        $abs = $this->entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $user->getId()]);
        $this->assertNotNull($abs, $test . ': After the command absences should exist');

        $user->setIcsCheck([0,0,0]);
        $this->entityManager->flush();

        $this->execute();
        
        $this->entityManager->clear();

        $abs = $this->entityManager->getRepository(Absence::class)->findOneBy(['perso_id' => $user->getId()]);
        $this->assertNull($abs, $test . ': After changing ics check, absence should not exist');
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:absence:import-ics');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName()
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('ICS import completed: absences updated', $output);
    }
}
