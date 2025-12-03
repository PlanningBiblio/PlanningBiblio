<?php
//TO BE CONTINUED
namespace App\Tests\Command;

use App\Entity\Absence;
use App\Entity\Agent;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\PLBWebTestCase;

class AbsenceImportICSCommandTest extends PLBWebTestCase
{
    private string $lockFile;
    protected function setUp(): void
    {
        parent::setUp();

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

    public function testAgent(): void
    {
        $this->setParam('ICS-Server3',1);

        $alice = $this->builder->build(Agent::class, array(
            'login' => 'alice', 'mail' => 'alice@example.com', 'nom' => 'Doe', 'prenom' => 'Alice',
            'supprime' => 0, 'check_ics' => '[0,0,1]', 'url_ics' => __DIR__ . '/../data/absenceImport.ics'
        ));

        $abs = $this->entityManager->getRepository(Absence::class)->findOneBy(["perso_id"=> $alice->getId()]);
        $this->assertNull( $abs, '');

        $this->execute();

        $abs = $this->entityManager->getRepository(Absence::class)->findOneBy(["perso_id"=> $alice->getId()]);
        $this->assertNotNull( $abs, '');

        $alice->setIcsCheck('[0,0,0]');
        $this->entityManager->flush();

        $this->execute();
        
        $this->entityManager->clear();

        $abs = $this->entityManager->getRepository(Absence::class)->findOneBy(["perso_id"=> $alice->getId()]);
        $this->assertNull( $abs, '');
        
        $this->restore();
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

        $this->assertStringContainsString('ICS import completed: absences updated and entries from disabled calendars purged.', $output);

    }

}
