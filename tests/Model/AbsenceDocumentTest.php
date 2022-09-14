<?php


use App\Model\Agent;
use App\Model\Access;
use App\Model\AbsenceDocument;

use Tests\FixtureBuilder;

use Tests\PLBWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class AbsenceDocumentTest extends PLBWebTestCase
{
    public function testAdd() {
        global $entityManager;
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent = $builder->build(Agent::class, array('login' => 'jdevoe'));
        $builder->delete(AbsenceDocument::class);
        $this->logInAgent($agent, array(100));

        $date = \DateTime::createFromFormat("d/m/Y", '09/10/2022');

        $abs_doc = new AbsenceDocument();
        $abs_doc->filename('fichier');
        $abs_doc->date($date);
        $abs_doc->absence_id(100);

        $entityManager->persist($abs_doc);
        $entityManager->flush();

        $info = $entityManager->getRepository(AbsenceDocument::class)->findOneBy(array('filename' => 'fichier'));

        $this->assertEquals('fichier', $info->filename(), "filename is fichier");
        $this->assertEquals($date, $info->date(), "date is 09/10/2022");
        $this->assertEquals(100, $info->absence_id(), 'absence_id is 100');
        $this->assertStringContainsString('/src/Model/../../var/upload/test/absences/',$abs_doc->upload_dir(),'upload dir ok');
    }
}


