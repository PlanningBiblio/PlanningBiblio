<?php
//fini
namespace App\Tests\Command;

use App\Entity\Agent;
use App\Entity\Absence;
use App\Entity\ConfigParam;
use App\Entity\AbsenceDocument;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\PLBWebTestCase;

class AbsenceDeleteDocumentsCommandTest extends PLBWebTestCase
{
    public function testConfigOff_NoDeletion(): void
    {

        $this->setParam('Absences-DelaiSuppressionDocuments', 0);

        $old = (new AbsenceDocument())
            ->setFilename('old')
            ->setDate(new \DateTime('2022-10-09'))
            ->setAbsenceId(100);

        $this->entityManager->persist($old);
        $this->entityManager->flush();

        $this->execute();

        $this->entityManager->clear();

        $info = $this->entityManager->getRepository(AbsenceDocument::class)->findOneBy(array('filename' => 'old'));

        $this->assertEquals('old',  $info->getFilename(), 'filename is fichier');
        $this->assertEquals(100, $info->getAbsenceId(), 'absence_id is 100');
        $this->assertStringContainsString('/src/Entity/../../var/upload/test/absences/', $info->upload_dir(), 'upload dir ok');
    }

    public function testHaveOneToDeleteAndOneNo(): void
    {
        $this->setParam('Absences-DelaiSuppressionDocuments', 1);

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AbsenceDocument::class);

        $now = new \DateTime();
        $past = \DateTime::createFromFormat("d/m/Y", '09/10/2022');

        $abs_doc_now = new AbsenceDocument();
        $abs_doc_now->setFilename('fichier_now');
        $abs_doc_now->setDate($now);
        $abs_doc_now->setAbsenceId(100);

        $abs_doc_past = new AbsenceDocument();
        $abs_doc_past->setFilename('fichier_past');
        $abs_doc_past->setDate($past);
        $abs_doc_past->setAbsenceId(100);

        $this->entityManager->persist($abs_doc_past);
        $this->entityManager->persist($abs_doc_now);
        $this->entityManager->flush();

        foreach ([$abs_doc_now, $abs_doc_past] as $doc) {
            $projectDir = self::getContainer()->getParameter('kernel.project_dir');
            $alt = $projectDir . '/var/upload/test/absences/' . $doc->getAbsenceId() . '/' . $doc->getId() . '/' . $doc->getFilename();
            if (!is_dir(dirname($alt))) {
                mkdir(dirname($alt), 0777, true);
            }
            
            file_put_contents($alt, 'dummy');
        }

        $this->execute();

        $this->entityManager->clear();

        $deleted = $this->entityManager->getRepository(AbsenceDocument::class)
            ->findOneBy(['filename' => 'fichier_past']);
        $this->assertNull($deleted, 'Old doc should be deleted by cron');

        $kept = $this->entityManager->getRepository(AbsenceDocument::class)
            ->findOneBy(['filename' => 'fichier_now']); 
        $this->assertNotNull($kept, 'Recent doc should be kept by cron');
    }

    public function testHaveNothingToDelete_OneNew(): void
    {
        $this->setParam('Absences-DelaiSuppressionDocuments', 2);

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AbsenceDocument::class);

        $now = new \DateTime();

        $abs_doc_now = new AbsenceDocument();
        $abs_doc_now->setFilename('fichier_now');
        $abs_doc_now->setDate($now);
        $abs_doc_now->setAbsenceId(100);

        $this->entityManager->persist($abs_doc_now);
        $this->entityManager->flush();
        $this->execute();

        $this->entityManager->clear();

        $kept = $this->entityManager->getRepository(AbsenceDocument::class)
            ->findOneBy(['filename' => 'fichier_now']); 
        $this->assertNotNull($kept, 'Recent doc should be kept by cron');
    }

    public function testHaveNothingToDelete_Nothing(): void
    {
        $this->setParam('Absences-DelaiSuppressionDocuments', 1);

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AbsenceDocument::class);

        $this->execute();

        $this->entityManager->clear();

        $deleted = $this->entityManager->getRepository(AbsenceDocument::class)
            ->findAll();
        $this->assertEmpty($deleted, 'Old doc should be deleted by cron');
        
        $this->restore();
    }

    private function execute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
 
        $command = $application->find('app:absence:delete-documents');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName()
        ], [
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE
        ]);
        $commandTester->assertCommandIsSuccessful();

    }
}
