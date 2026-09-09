<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use App\Entity\Config;
use App\Entity\WorkingHour;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\FixtureBuilder;

class WorkingHourImportCommandTest extends KernelTestCase
{
    private $entityManager;

    public static function setUpBeforeClass(): void
    {
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);
        $builder->delete(WorkingHours::class);
        
        $lockFile = sys_get_temp_dir() . '/plannoCSV.lock';
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }

        $alex = $this->builder->build(Agent::class, [
            'login' => 'alex', 'mail' => 'alex@example.com', 'nom' => 'alex', 'prenom' => 'Alice',
            'supprime' => 0,'matricule' => '0000000ff040'
            ]);

        $aurelie = $this->builder->build(Agent::class, [
            'login' => 'aurelie', 'mail' => 'aurelie@example.com', 'nom' => 'aurelie', 'prenom' => 'Alice',
            'supprime' => 0,'matricule' => '0000000ee490'
            ]);        
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testLogin(): void
    {
        $this->config->setParam('PlanningHebdo-ImportAgentId', 'login');
        $this->config->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_login.csv');
        $this->config->setParam('Multisites-nombre', 1);

        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $aurelie = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'aurelie']);

        $time = [
            0 => ['', '', '', '', 0],
            1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
            2 => ['09:00:00', '13:00:00', '', '', 1],
            3 => ['10:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
            4 => ['10:35', '12:35', '13:00:00', '17:00:00', 1],
            5 => ['09:00:00', '13:00:00', '', '', 1],
        ];

        $whAurelie = new WorkingHour();
        $whAurelie->setUser($aurelie->getId())
            ->setStart(new DateTime('2025-04-01'))
            ->setEnd(new DateTime('2025-04-30'))
            ->setWorkingHours($time);
        $this->entityManager->persist($whAurelie);

        $whAurelie = new WorkingHour();
        $whAurelie->setUser($aurelie->getId())
            ->setStart(new DateTime('2025-05-26'))
            ->setEnd(new DateTime('2025-06-01'))
            ->setWorkingHours($time);
        $this->entityManager->persist($whAurelie);
        $this->entityManager->flush();

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findBy(['perso_id' => $aurelie->getId()]);

        $this->assertNull($whAlex, '');
        $this->assertCount(2, $whAurelie, 'Aurelie should have 2 workingHours');

        $this->execute();

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findBy(['perso_id' => $aurelie->getId()]);

        $this->assertNotNull($whAlex, '');
        $this->assertCount(6, $whAurelie, 'Aurelie should have 6 workingHours');
    }

    public function testMail(): void
    {
        $this->addConfig('PlanningHebdo-ImportAgentId', 'mail');
        $this->config->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_mail.csv');
        $this->config->setParam('Multisites-nombre', 1);
        
        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $aurelie = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'aurelie']);

        $time = [
            0 => ['', '', '', '', 0],
            1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
            2 => ['09:00:00', '13:00:00', '', '', 1],
            3 => ['10:00:00', '12:00:00', '13:00:00', '17:00:00', 1],
            4 => ['10:35', '12:35', '13:00:00', '17:00:00', 1],
            5 => ['09:00:00', '13:00:00', '', '', 1],
        ];

        $whAurelie = new WorkingHour();
        $whAurelie->setUser($aurelie->getId())
            ->setStart(new DateTime('2025-04-01'))
            ->setEnd(new DateTime('2025-04-30'))
            ->setWorkingHours($time);
        $this->entityManager->persist($whAurelie);

        $whAurelie = new WorkingHour();
        $whAurelie->setUser($aurelie->getId())
            ->setStart(new DateTime('2025-05-26'))
            ->setEnd(new DateTime('2025-06-01'))
            ->setWorkingHours($time);
        $this->entityManager->persist($whAurelie);
        $this->entityManager->flush();

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findBy(['perso_id' => $aurelie->getId()]);

        $this->assertNull($whAlex, '');
        $this->assertCount(2, $whAurelie, 'Aurelie should have 2 workingHours');

        $this->execute();

        $whAlex = $this->entityManager->getRepository(WorkingHour::class)->findOneBy(['perso_id' => $alex->getId()]);
        $whAurelie = $this->entityManager->getRepository(WorkingHour::class)->findBy(['perso_id' => $aurelie->getId()]);

        $this->assertNotNull($whAlex, '');
        $this->assertCount(6, $whAurelie, 'Aurelie should have 6 workingHours');
    }

    public function testMatricule(): void
    {
        $this->addConfig('PlanningHebdo-ImportAgentId', 'matricule');
        $this->config->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_matricule.csv');
        $this->config->setParam('Multisites-nombre', 1);
        
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
    }
}
