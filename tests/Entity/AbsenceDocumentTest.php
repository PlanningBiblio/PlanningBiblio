<?php

namespace App\Tests\Entity;

use App\Entity\AbsenceDocument;
use App\Entity\Agent;
use Tests\PLBWebTestCase;

class AbsenceDocumentTest extends PLBWebTestCase
{
    private AbsenceDocument $document;

    protected function setUp(): void
    {
        $this->document = new AbsenceDocument();
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->document->getId());
        $this->assertSame(0, $this->document->getAbsenceId());
        $this->assertSame('', $this->document->getFilename());
        $this->assertNull($this->document->getDate());
    }

    public function testSettersAndGetters(): void
    {
        $date = new \DateTime('2025-01-01');

        $this->document
            ->setAbsenceId(10)
            ->setFilename('file.pdf')
            ->setDate($date);

        $this->assertSame(10, $this->document->getAbsenceId());
        $this->assertSame('file.pdf', $this->document->getFilename());
        $this->assertSame($date, $this->document->getDate());
    }

    public function testFluentInterface(): void
    {
        $result = $this->document->setFilename('test.pdf');

        $this->assertInstanceOf(AbsenceDocument::class, $result);
    }

    public function testDeleteFileDoesNothingIfDataMissing(): void
    {
        // Aucun id, aucun filename -> ne doit rien faire
        $this->document->deleteFile();

        $this->assertTrue(true); // Si aucune exception, test OK
    }

    public function testUploadDirIsGenerated(): void
    {
        $_ENV['APP_ENV'] = 'test';

        $dir = $this->document->upload_dir();

        $this->assertStringContainsString('/var/upload/test/absences/', $dir);
    }

    public function testAdd(): void
    {
        parent::setUp();

        $entityManager = $this->entityManager;

        $builder = $this->builder;
        $builder->delete(Agent::class);
        $builder->delete(AbsenceDocument::class);

        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $this->logInAgent($agent, [100]);

        $date = new \DateTime('2022-10-09');

        $absenceDoc = new AbsenceDocument();
        $absenceDoc->setFilename('fichier');
        $absenceDoc->setDate($date);
        $absenceDoc->setAbsenceId(100);

        $entityManager->persist($absenceDoc);
        $entityManager->flush();

        $info = $entityManager->getRepository(AbsenceDocument::class)->findOneBy(array('filename' => 'fichier'));

        $this->assertEquals('fichier', $info->getFilename(), "filename is fichier");
        $this->assertEquals($date, $info->getDate(), "date is 09/10/2022");
        $this->assertEquals(100, $info->getAbsenceId(), 'absence_id is 100');
        $this->assertStringContainsString('/src/Entity/../../var/upload/test/absences/', $info->upload_dir(), 'upload dir ok');
    }
}
