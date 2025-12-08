<?php

namespace App\Tests\Command;

use App\Entity\Absence;
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

        $params = [
            'hamac_status_extra' => [0,1],
            'hamac_status_waiting' => [3],
            'hamac_status_validated' => [2,5],
            'hamac_days_before' => $daysBefore,
            'Hamac-debug' => 1,
            'Hamac-motif' => 'Hamac',
            'Hamac-id' => 'matricule',
            'Hamac-status' => '2,3,5',
            'Hamac-csv' => __DIR__ . '/../data/absences.csv',
        ];

        foreach ($params as $k => $v) {
            try {
                $this->setParam($k, $v);
            } catch (\Throwable $e) {
            }
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

    public function testAgent(): void
    {

        $a=$this->builder->build(Agent::class, [
            'login' => 'alice',
            'supprime' => 0, 'check_hamac' => 1, 'matricule' => '0000000ff040'
        ]);
        $b=$this->builder->build(Agent::class, [
            'login' => 'jdevoe',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000ee490'
        ]);
        $c=$this->builder->build(Agent::class, [
            'login' => 'abreton',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000ee493'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'kboivin',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000ee856'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'aaa',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000xx421'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'bbb',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000xx941'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'ccc',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000aa928'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'ddd',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000cc203'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'eee',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000dd322'
        ]);

        $countBefore = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences");
        $this->assertSame(0,$countBefore, '0 absence should be founded');

        $this->execute();

        $countAfter = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences");
        $this->assertSame(241, $countAfter, '241 absence should be imported');//252-10-1
        
        $this->restore();
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
