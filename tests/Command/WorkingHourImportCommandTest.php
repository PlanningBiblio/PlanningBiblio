<?php

namespace App\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\WorkingHour;
use App\Entity\Agent;
use DateTime;
use App\Entity\Config;
use Tests\PLBWebTestCase;

class WorkingHourImportCommandTest extends PLBWebTestCase
{
    private string $lockFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restore();

        $this->lockFile = sys_get_temp_dir() . '/plannoCSV.lock';
        if (file_exists($this->lockFile)) {
            @unlink($this->lockFile);
        }

        $this->builder->delete(Agent::class);

        $alex = $this->builder->build(Agent::class, [
            'login' => 'alex', 'mail' => 'alex@example.com', 'nom' => 'alex', 'prenom' => 'Alice',
            'supprime' => 0,'matricule' => '0000000ff040'
        ]);

        $aurelie = $this->builder->build(Agent::class, [
            'login' => 'aurelie', 'mail' => 'aurelie@example.com', 'nom' => 'aurelie', 'prenom' => 'Alice',
            'supprime' => 0,'matricule' => '0000000ee490'
        ]);        
    }

    public function testLogin(): void
    {
        $this->setParam('PlanningHebdo-ImportAgentId', 'login');
        $this->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_login.csv');
        $this->setParam('Multisites-nombre', 1);

        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $aurelie = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'aurelie']);

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $aurelie->getId()]);

        $this->assertNull($whAlex, '');
        $this->assertNull($whAurelie, '');

        $this->execute();

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $aurelie->getId()]);

        $this->assertNotNull($whAlex, '');
        $this->assertNotNull($whAurelie, '');
    }

    public function testMail(): void
    {
        $this->setParam('PlanningHebdo-ImportAgentId', 'mail');
        $this->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_mail.csv');
        $this->setParam('Multisites-nombre', 1);
        
        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $aurelie = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'aurelie']);

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $aurelie->getId()]);

        $this->assertNull($whAlex, '');
        $this->assertNull($whAurelie, '');

        $this->execute();

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $aurelie->getId()]);

        $this->assertNotNull($whAlex, '');
        $this->assertNotNull($whAurelie, '');
    }

    public function testMatricule(): void
    {
        $this->setParam('PlanningHebdo-ImportAgentId', 'matricule');
        $this->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_matricule.csv');
        $this->setParam('Multisites-nombre', 1);
        
        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $aurelie = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'aurelie']);

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $aurelie->getId()]);

        $this->assertNull($whAlex, '');
        $this->assertNull($whAurelie, '');

        $this->execute();

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $aurelie->getId()]);

        $this->assertNotNull($whAlex, '');
        $this->assertNotNull($whAurelie, '');
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:workinghour:import');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName()
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        ]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('CSV weekly planning import completed: new/updated schedules inserted and obsolete ones purged.', $output);
    }
}
