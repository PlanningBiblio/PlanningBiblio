<?php

namespace App\Tests\Command;

use App\Entity\Absence;
use App\Entity\AbsenceReason;
use App\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\PLBWebTestCase;

class AbsenceImportCSVCommandTest extends PLBWebTestCase
{
    private string $lockFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lockFile = sys_get_temp_dir() . '/plannoAbsenceImportCSV.lock';
        if (file_exists($this->lockFile)) {
            @unlink($this->lockFile);
        }

        $origin = new \DateTimeImmutable('2025-10-14');
        $now = new \DateTimeImmutable();
        $interval = $origin->diff($now);
        $daysBefore = (int) $interval->format('%R%a');

        $this->config->setParam('Hamac-debug', '1');
        $this->config->setParam('Hamac-motif', 'Hamac');
        $this->config->setParam('Hamac-id', 'matricule');
        $this->config->setParam('Hamac-status', '2,3,5');
        $this->config->setParam('Hamac-csv', __DIR__ . '/../data/absences.csv');

        $GLOBALS['config']['hamac_status_extra'] = [0,1];
        $GLOBALS['config']['hamac_status_waiting'] = [3];
        $GLOBALS['config']['hamac_status_validated'] = [2,5];
        $GLOBALS['config']['hamac_days_before'] = $daysBefore;

        $this->builder->delete(Absence::class);
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

    public function testAgent(): void
    {

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => 'yokl8ucgenw6'
        ]);

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => 'qv0bzb2g8yj0'
        ]);

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => 'ezcjirxm6oht'
        ]);

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => 'rn9cmni34l0y'
        ]);

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => '7tzvc1ahrdrw'
        ]);

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => 'b6mizbg7at2j'
        ]);

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => 'ewcmtgoawa7j'
        ]);

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => 'o1nxxbhfbx1e'
        ]);

        $this->builder->build(Agent::class, [
            'check_hamac' => 1,
            'matricule' => '3m6mrm6sd1uc'
        ]);

        $countBefore = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences");
        $this->assertSame(0, $countBefore, '0 absence should be founded');

        $this->execute();

        $countAfter = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences");
        $this->assertSame(214, $countAfter, "214 absences from 2025-10-14 with status 2 should be imported");
    }

    // Hamac-motif = [SUMMARY]: the label (2nd column) is used as reason if it matches an existing reason
    // (case-insensitive), otherwise "Autre" with the label in motif_autre
    public function testSummaryReason(): void
    {
        $this->config->setParam('Hamac-motif', '[SUMMARY]');

        $reason = new AbsenceReason();
        $reason->setValue('Form');
        $reason->setRank(999);
        $this->entityManager->persist($reason);

        foreach (['yokl8ucgenw6', 'qv0bzb2g8yj0', 'ezcjirxm6oht', 'rn9cmni34l0y', '7tzvc1ahrdrw', 'b6mizbg7at2j', 'ewcmtgoawa7j', 'o1nxxbhfbx1e', '3m6mrm6sd1uc'] as $matricule) {
            $this->builder->build(Agent::class, ['check_hamac' => 1, 'matricule' => $matricule]);
        }
        $this->entityManager->flush();

        try {
            $this->execute();

            $connection = $this->entityManager->getConnection();

            $this->assertSame(0, (int) $connection->fetchOne("SELECT COUNT(*) FROM absences WHERE motif = '[SUMMARY]'"), 'the keyword is never saved as reason');
            $this->assertGreaterThan(0, (int) $connection->fetchOne("SELECT COUNT(*) FROM absences WHERE motif = 'Form' AND motif_autre = '' AND commentaires = 'FORM'"), 'FORM matches the reason Form');
            $this->assertGreaterThan(0, (int) $connection->fetchOne("SELECT COUNT(*) FROM absences WHERE motif = 'Autre' AND motif_autre = 'CONGh'"), 'CONGh matches no reason');
            $this->assertSame(0, (int) $connection->fetchOne("SELECT COUNT(*) FROM absences WHERE motif = 'Autre' AND motif_autre = ''"), 'motif_autre is always filled with "Autre"');
        } finally {
            $this->config->setParam('Hamac-motif', 'Hamac');
            $this->entityManager->remove($this->entityManager->getRepository(AbsenceReason::class)->find($reason->getId()));
            $this->entityManager->flush();
        }
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:absence:import-csv');

        $commandTester = new CommandTester($command);

        $commandTester->execute(
            ['command'  => $command->getName()],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        $commandTester->assertCommandIsSuccessful();
    }
}
