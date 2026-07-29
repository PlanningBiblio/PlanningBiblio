<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use App\Entity\Config;
use App\Entity\Holiday;
use App\Entity\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\FixtureBuilder;

class HolidayReminderCommandTest extends KernelTestCase
{
    private $config;
    private $entityManager;
    private $holidayId = 0;

    public static function setUpBeforeClass(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $config = $entityManager->getRepository(Config::class);

        $config->setParam('Absences-notifications-agent-par-agent', 0);
        $config->setParam('Conges-Enable', 1);
        $config->setParam('Conges-Rappels', 1);
        $config->setParam('Conges-Rappels-Jours', 14);
        $config->setParam('Conges-Rappels-N1', '["Mail-Planning"]');
        $config->setParam('Conges-Rappels-N2', '["mails_responsables"]');
        $config->setParam('Mail-Planning', 'mail1@example.com; mail2@example.com');

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Holiday::class);

        $agent = new Agent();
        $agent->setLogin('jdupont');
        $agent->setLastname('Dupont');
        $agent->setFirstname('Jean');
        $agent->setMail('jdupont@mail.fr');
        $agent->setACL([99,100]);
        $agent->setManagersMails('mail3@example.com; mail4@example.com');
        $entityManager->persist($agent);
        
        $agentLevel1 = new Agent();
        $agentLevel1->setLogin('feefeqt')
            ->setMail('mail5@example.com');
        $entityManager->persist($agentLevel1);

        $agentLevel1Notif = new Agent();
        $agentLevel1Notif->setLogin('zeceaxa')
            ->setMail('mail6@example.com');
        $entityManager->persist($agentLevel1Notif);

        $agentLevel2 = new Agent();
        $agentLevel2->setLogin('eczevaq')
            ->setMail('mail7@example.com');
        $entityManager->persist($agentLevel2);

        $agentLevel2Notif = new Agent();
        $agentLevel2Notif->setLogin('evzaaca')
            ->setMail('mail8@example.com');
        $entityManager->persist($agentLevel2Notif);

        $manager = new Manager();
        $manager->setUser($agent);
        $manager->setManager($agentLevel1);
        $manager->setLevel1(1);
        $manager->setLevel2(0);
        $manager->setLevel1Notification(0);
        $manager->setLevel2Notification(0);
        $entityManager->persist($manager);
        $entityManager->flush();

        $manager = new Manager();
        $manager->setUser($agent);
        $manager->setManager($agentLevel1Notif);
        $manager->setLevel1(1);
        $manager->setLevel2(0);
        $manager->setLevel1Notification(1);
        $manager->setLevel2Notification(0);
        $entityManager->persist($manager);
        $entityManager->flush();

        $manager = new Manager();
        $manager->setUser($agent);
        $manager->setManager($agentLevel2);
        $manager->setLevel1(1);
        $manager->setLevel2(1);
        $manager->setLevel1Notification(0);
        $manager->setLevel2Notification(0);
        $entityManager->persist($manager);
        $entityManager->flush();

        $manager = new Manager();
        $manager->setUser($agent);
        $manager->setManager($agentLevel2Notif);
        $manager->setLevel1(1);
        $manager->setLevel2(1);
        $manager->setLevel1Notification(0);
        $manager->setLevel2Notification(1);
        $entityManager->persist($manager);
        $entityManager->flush();

        $now = new \DateTime();
        $start = new \DateTime('+1 day');
        $end = new \DateTime('+5 day');

        $holiday = new Holiday();
        $holiday->setUser($agent->getId());
        $holiday->setStart($start);
        $holiday->setEnd($end);
        $holiday->setEntryDate($now);
        $holiday->setChange($agent->getId());
        $holiday->setDelete(0);
        $holiday->setInfo(0);
        $holiday->setEntry($agent->getId());
        $holiday->setValidLevel1Date($now);
        $holiday->setValidLevel1(0);
        $holiday->setValidLevel2(0);

        $entityManager->persist($holiday);
        $entityManager->flush();        
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Holiday::class);
        $builder->delete(Manager::class);
    }

    protected function setUp(): void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->config = $this->entityManager->getRepository(Config::class);
    }

    public function testReminderDisabled(): void
    {
        $this->config->setParam('Conges-Rappels', 0);

        $result = ['[WARNING] Holiday reminder is disabled.'];

        $this->execute($result);

        $this->config->setParam('Conges-Rappels', 1);
    }

    public function testReminderLevel1MailPlanning(): void
    {
        $result = [
            '[OK] Reminders sent for leave pending validation.',
            'Recipient: mail1@example.com',
            'Recipient: mail2@example.com',
            '!Recipient: mail3@example.com',
            '!Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result);
    }

    public function testReminderLevel2MailResponsible(): void
    {
        $result = [
            '[OK] Reminders sent for leave pending validation.',
            '!Recipient: mail1@example.com',
            '!Recipient: mail2@example.com',
            'Recipient: mail3@example.com',
            'Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result, 1);
    }

    public function testReminderValid(): void
    {
        $result = [
            '[OK] Reminders sent for leave pending validation.',
            '!Recipient: mail1@example.com',
            '!Recipient: mail2@example.com',
            '!Recipient: mail3@example.com',
            '!Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result, 1, 1);
    }

    public function testReminderLevel1MailResponsible(): void
    {
        $this->config->setParam('Conges-Rappels-N1', '["mails_responsables"]');
        $this->config->setParam('Conges-Rappels-N2', '["Mail-Planning"]');

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            '!Recipient: mail1@example.com',
            '!Recipient: mail2@example.com',
            'Recipient: mail3@example.com',
            'Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result);
    }

    public function testReminderLevel2MailPlanning(): void
    {
        $this->config->setParam('Conges-Rappels-N1', '["mails_responsables"]');
        $this->config->setParam('Conges-Rappels-N2', '["Mail-Planning"]');

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            'Recipient: mail1@example.com',
            'Recipient: mail2@example.com',
            '!Recipient: mail3@example.com',
            '!Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result, 1);
    }

    public function testReminderValid2(): void
    {
        $this->config->setParam('Conges-Rappels-N1', '["mails_responsables"]');
        $this->config->setParam('Conges-Rappels-N2', '["Mail-Planning"]');

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            '!Recipient: mail1@example.com',
            '!Recipient: mail2@example.com',
            '!Recipient: mail3@example.com',
            '!Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result, 1, 1);
    }

    public function testReminderLevel1Both(): void
    {
        $this->config->setParam('Conges-Rappels-N1', '["Mail-Planning","mails_responsables"]');
        $this->config->setParam('Conges-Rappels-N2', '["Mail-Planning","mails_responsables"]');

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            'Recipient: mail1@example.com',
            'Recipient: mail2@example.com',
            'Recipient: mail3@example.com',
            'Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result);
    }

    public function testReminderLevel2Both(): void
    {
        $this->config->setParam('Conges-Rappels-N1', '["Mail-Planning","mails_responsables"]');
        $this->config->setParam('Conges-Rappels-N2', '["Mail-Planning","mails_responsables"]');

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            'Recipient: mail1@example.com',
            'Recipient: mail2@example.com',
            'Recipient: mail3@example.com',
            'Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result, 1);
    }

    public function testReminderValidBoth(): void
    {
        $this->config->setParam('Conges-Rappels-N1', '["Mail-Planning","mails_responsables"]');
        $this->config->setParam('Conges-Rappels-N2', '["Mail-Planning","mails_responsables"]');

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            '!Recipient: mail1@example.com',
            '!Recipient: mail2@example.com',
            '!Recipient: mail3@example.com',
            '!Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result, 1, 1);
    }

    public function testReminderLevel1ValidationScheme(): void
    {
        $this->config->setParam('Absences-notifications-agent-par-agent', 1);

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            '!Recipient: mail1@example.com',
            '!Recipient: mail2@example.com',
            '!Recipient: mail3@example.com',
            '!Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            'Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result);
    }

    public function testReminderLevel2ValidationScheme(): void
    {
        $this->config->setParam('Absences-notifications-agent-par-agent', 1);

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            '!Recipient: mail1@example.com',
            '!Recipient: mail2@example.com',
            '!Recipient: mail3@example.com',
            '!Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            'Recipient: mail8@example.com',
        ];

        $this->execute($result, 1);
    }

    public function testReminderValidValidationScheme(): void
    {
        $this->config->setParam('Absences-notifications-agent-par-agent', 1);

        $result = [
            '[OK] Reminders sent for leave pending validation.',
            '!Recipient: mail1@example.com',
            '!Recipient: mail2@example.com',
            '!Recipient: mail3@example.com',
            '!Recipient: mail4@example.com',
            '!Recipient: mail5@example.com',
            '!Recipient: mail6@example.com',
            '!Recipient: mail7@example.com',
            '!Recipient: mail8@example.com',
        ];

        $this->execute($result, 1, 1);
    }

    private function execute($result, $level1 = 0, $level2 = 0): void
    {
        $kernel = self::bootKernel();
        $application = new Application(self::$kernel);

        $entityManager = $this->entityManager;

        foreach ($this->config as $k => $v) {
            $param = $entityManager->getRepository(Config::class)->findOneBy(['nom' => $k]);
            $param->setValue($v);
            $entityManager->persist($param);
            $entityManager->flush();
            $GLOBALS['config'][$k] = $v;
        }

        $holiday = $entityManager->getRepository(Holiday::class)->find(1);
        $holiday->setValidLevel1($level1);
        $holiday->setValidLevel2($level2);
        $entityManager->persist($holiday);
        $entityManager->flush();

        $command = $application->find('app:holiday:reminder');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName()
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        ]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();

        foreach ($result as $r) {
            if (substr($r, 0, 1) == '!') {
                $r = substr($r, 1);
                $this->assertStringNotContainsString($r, $output);
            } else {
                $this->assertStringContainsString($r, $output);
            }
        }
    }
}
