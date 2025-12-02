<?php

namespace App\Tests\Command;

use App\Entity\Agent;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
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
        $params = [
            'hamac_status_extra' => [0,1],
            'hamac_status_waiting' => [3],
            'hamac_status_validated' => [2,5],
            'hamac_days_before' => "2020-11-14 00:00:00",
            'Hamac-debug' => true,
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

        $this->builder->build(Agent::class, [
            'login' => 'alice', 'mail' => 'alice@example.com', 'nom' => 'Doe', 'prenom' => 'Alice',
            'supprime' => 0, 'check_hamac' => 1, 'matricule' => '0000000ff040'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'jdevoe', 'mail' => 'jdevoe@example.com', 'nom' => 'Devoe', 'prenom' => 'John',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000ee490'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'abreton', 'mail' => 'abreton@example.com', 'nom' => 'Breton', 'prenom' => 'Aubert',
            'supprime' => 1,'check_hamac' => 1, 'matricule' => '0000000ee493'
        ]);
        $this->builder->build(Agent::class, [
            'login' => 'kboivin', 'mail' => 'kboivin@example.com', 'nom' => 'Boivin', 'prenom' => 'Karel',
            'supprime' => 0,'check_hamac' => 1, 'matricule' => '0000000ee856'
        ]);

        $countBefore = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences");
        $this->assertSame(0,$countBefore, '0 absence should be founded');

        $this->execute();

        $countAfter = $this->entityManager->getConnection()->fetchOne("SELECT COUNT(*) FROM absences");
        $this->assertSame(96, $countAfter, '96 absence should be imported');
        
        $this->restore();
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:absence:import-csv');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName()
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        ]);
        $commandTester->assertCommandIsSuccessful();

    }
    
}
