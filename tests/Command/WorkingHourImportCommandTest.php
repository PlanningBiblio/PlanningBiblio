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
    private $config;
    private $entityManager;

    public static function setUpBeforeClass(): void
    {
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);
        $builder->delete(WorkingHour::class);

        $lockFile = sys_get_temp_dir() . '/plannoCSV.lock';
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $alex = new Agent();
        $alex->setLogin('alex')
            ->setMail('alex@example.com')
            ->setEmployeeNumber('0000000ff040');
        $entityManager->persist($alex);

        $aurelie = new Agent();
        $aurelie->setLogin('aurelie')
            ->setMail('aurelie@example.com')
            ->setEmployeeNumber('0000000ee490');
        $entityManager->persist($aurelie);

        $entityManager->flush();

        $config = new Config();
        $config->setName('PlanningHebdo-ImportAgentId')->setValue('');
        $entityManager->persist($config);

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
        $entityManager->persist($whAurelie);

        $whAurelie = new WorkingHour();
        $whAurelie->setUser($aurelie->getId())
            ->setStart(new DateTime('2025-05-26'))
            ->setEnd(new DateTime('2025-06-01'))
            ->setWorkingHours($time);
        $entityManager->persist($whAurelie);

        $entityManager->flush();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->config = $this->entityManager->getRepository(Config::class);

        $importedHours = $this->entityManager->getRepository(WorkingHour::class)->findImported();
        foreach ($importedHours as $hours) {
            $this->entityManager->remove($hours);
        }

        $this->entityManager->flush();
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();

        $builder->delete(Agent::class);
        $builder->delete(WorkingHour::class);
    }

    public function testLogin(): void
    {
        $this->config->setParam('PlanningHebdo-ImportAgentId', 'login');
        $this->config->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_login.csv');
        $this->config->setParam('Multisites-nombre', 1);

        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $aurelie = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'aurelie']);

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
        $this->config->setParam('PlanningHebdo-ImportAgentId', 'mail');
        $this->config->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_mail.csv');
        $this->config->setParam('Multisites-nombre', 1);

        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $aurelie = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'aurelie']);

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
        $this->config->setParam('PlanningHebdo-ImportAgentId', 'matricule');
        $this->config->setParam('PlanningHebdo-CSV', __DIR__ . '/../data/workingHourImport_matricule.csv');
        $this->config->setParam('Multisites-nombre', 1);

        $alex = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'alex']);
        $aurelie = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'aurelie']);

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
