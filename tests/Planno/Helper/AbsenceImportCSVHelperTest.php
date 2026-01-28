<?php

use App\Planno\Helper\AbsenceImportCSVHelper;
use App\Entity\Agent;
use App\Entity\Absence;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

class AbsenceImportCSVHelperTest extends PLBWebTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder->delete(Agent::class);
        $this->builder->delete(Absence::class);
    }

    public function testImport(): void {
        $entityManager = $this->entityManager;
        $this->setParam('AbsImport-Reason',       'Test reason');
        $this->setParam('AbsImport-Agent',        'matricule');
        $this->setParam('AbsImport-ConvertBegin', "/^(\d{2}\/\d{2}\/\d{4})$/\n/^(\d{2}\/\d{2}\/\d{4}) (matin)$/\n/^(\d{2}\/\d{2}\/\d{4}) (après-midi)$/");
        $this->setParam('AbsImport-ConvertEnd',   "/^(\d{2}\/\d{2}\/\d{4})$/\n/^(\d{2}\/\d{2}\/\d{4}) (matin)$/\n/^(\d{2}\/\d{2}\/\d{4}) (après-midi)$/");

        $loggedin = $this->builder->build(Agent::class, array(
             'login' => 'loggedin', 'nom' => 'In', 'prenom' => 'Logged',
             'droits' => array(99,100)
        ));
        $loggedin_id = $loggedin->getId();

        $example_agent = $this->builder->build(Agent::class, array(
             'login' => 'alogin', 'prenom' => 'Ex', 'nom' => 'Ample', 'matricule' => 24, 'mail' => 'john.doe@example.com',
             'droits' => array(99,100)
        ));

        $uploadedFile = $this->setUploadedFile('AbsenceImportCSVHelperTest-matricule.csv');
        $helper   = new AbsenceImportCSVHelper();
        $results  = $helper->import($uploadedFile, $loggedin_id);

        # New absence, agent found by matricule
        $absences = $entityManager->getRepository(Absence::class)->findBy(array('perso_id' => $example_agent->getId()));
        $this->assertEquals(count($absences), 1, "One absence has been added");
        $absence = $absences[0];

        $this->assertEquals($absence->getReason(), 'Test reason', "Absence reason is set according to AbsImport-Reason");
        $this->assertEquals($absence->getStart(),  \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-11 13:00:00'), "start date was successfully transformed");
        $this->assertEquals($absence->getEnd(),    \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-14 13:00:00'), "end date was sucessfully transformed");

        # New import, absence already exists
        $results  = $helper->import($uploadedFile, $loggedin_id);
        $this->assertEquals(count($absences), 1, "Absence not added when it already exists");
        $this->assertStringStartsWith("L'absence pour Ex Ample existe déjà", $results[5]['message'], "Log message: absence not added when it already exist");

        # agent not found by matricule
        $this->assertEquals(count($absences), 1, "Absence not added when agent it not found");
        $this->assertEquals($results[8]['message'], 'Impossible de trouver un agent qui a 25 pour matricule', "Agent not found by matricule");

        # Empty start regex
        $this->builder->delete(Absence::class);
        $this->setParam('AbsImport-ConvertBegin', '');
        $helper = new AbsenceImportCSVHelper();
        $results  = $helper->import($uploadedFile, $loggedin_id);
        $absences = $entityManager->getRepository(Absence::class)->findBy(array('perso_id' => $example_agent->getId()));
        $this->assertEquals(count($absences), 0, "absence has not been added when AbsImport-ConvertBegin is empty");
        $this->assertEquals($results[2]['message'], 'AbsImport-ConvertBegin est vide', "Log message: AbsImport-ConvertBegin is empty");

        # Empty end regex
        $this->setParam('AbsImport-ConvertBegin',  "/^(\d{2}\/\d{2}\/\d{4})$/\n/^(\d{2}\/\d{2}\/\d{4}) (matin)$/\n/^(\d{2}\/\d{2}\/\d{4}) (après-midi)$/");
        $this->setParam('AbsImport-ConvertEnd',    '');
        $helper = new AbsenceImportCSVHelper();
        $results  = $helper->import($uploadedFile, $loggedin_id);
        $absences = $entityManager->getRepository(Absence::class)->findBy(array('perso_id' => $example_agent->getId()));
        $this->assertEquals(count($absences), 0, "absence has not been added when AbsImport-ConvertEnd is empty");
        $this->assertEquals($results[3]['message'], 'AbsImport-ConvertEnd est vide', "Log message: AbsImport-ConvertEnd is empty");

        # Invalid start regex
        $this->setParam('AbsImport-ConvertBegin',  "I'm an invalid start regex");
        $this->setParam('AbsImport-ConvertEnd',    '');
        $helper = new AbsenceImportCSVHelper();
        $results  = $helper->import($uploadedFile, $loggedin_id);
        $absences = $entityManager->getRepository(Absence::class)->findBy(array('perso_id' => $example_agent->getId()));
        $this->assertEquals(count($absences), 0, "absence has not been added when AbsImport-ConvertBegin is invalid ");
        $this->assertEquals($results[2]['message'], "I'm an invalid start regex n'est pas une expression régulière valide dans l'option de configuration AbsImport-ConvertBegin", "Log message: AbsImport-ConvertBegin is invalid");

        # Invalid end regex
        $this->setParam('AbsImport-ConvertBegin', "/^(\d{2}\/\d{2}\/\d{4})$/\n/^(\d{2}\/\d{2}\/\d{4}) (matin)$/\n/^(\d{2}\/\d{2}\/\d{4}) (après-midi)$/");
        $this->setParam('AbsImport-ConvertEnd',   "I'm an invalid end regex");
        $helper = new AbsenceImportCSVHelper();
        $results  = $helper->import($uploadedFile, $loggedin_id);
        $absences = $entityManager->getRepository(Absence::class)->findBy(array('perso_id' => $example_agent->getId()));
        $this->assertEquals(count($absences), 0, "absence has not been added when AbsImport-ConvertEnd is invalid ");
        $this->assertEquals($results[3]['message'], "I'm an invalid end regex n'est pas une expression régulière valide dans l'option de configuration AbsImport-ConvertEnd", "Log message: AbsImport-ConvertEnd is invalid");

        $helper   = new AbsenceImportCSVHelper();
        $uploadedFile = $this->setUploadedFile('AbsenceImportCSVHelperTest-login.csv');

        # New absence, agent found by login
        $this->builder->delete(Absence::class);
        $this->setParam('AbsImport-ConvertBegin', "/^(\d{2}\/\d{2}\/\d{4})$/\n/^(\d{2}\/\d{2}\/\d{4}) (matin)$/\n/^(\d{2}\/\d{2}\/\d{4}) (après-midi)$/");
        $this->setParam('AbsImport-ConvertEnd',   "/^(\d{2}\/\d{2}\/\d{4})$/\n/^(\d{2}\/\d{2}\/\d{4}) (matin)$/\n/^(\d{2}\/\d{2}\/\d{4}) (après-midi)$/");
        $this->setParam('AbsImport-Agent',        'login');
        $helper = new AbsenceImportCSVHelper();
        $results  = $helper->import($uploadedFile, $loggedin_id);
        $absences = $entityManager->getRepository(Absence::class)->findBy(array('perso_id' => $example_agent->getId()));
        $this->assertEquals(count($absences), 1, "One absence has been added");
        $absence = $absences[0];

        # agent not found by login
        $this->assertEquals(count($absences), 1, "Absence not added when agent it not found");
        $this->assertEquals($results[8]['message'], 'Impossible de trouver un agent qui a notalogin pour login', "Agent not found by login");

        $uploadedFile = $this->setUploadedFile('AbsenceImportCSVHelperTest-mail.csv');

        # New absence, agent found by email
        $this->builder->delete(Absence::class);
        $this->setParam('AbsImport-Agent', 'mail');
        $helper = new AbsenceImportCSVHelper();
        $results  = $helper->import($uploadedFile, $loggedin_id);
        $absences = $entityManager->getRepository(Absence::class)->findBy(array('perso_id' => $example_agent->getId()));
        $this->assertEquals(count($absences), 1, "One absence has been added");

        # agent not found by login
        $this->assertEquals(count($absences), 1, "Absence not added when agent is not found");
        $this->assertEquals($results[8]['message'], 'Impossible de trouver un agent qui a does.not.exist@example.com pour mail', "Agent not found by email");

        $this->builder->delete(Absence::class);
        $uploadedFile = $this->setUploadedFile('AbsenceImportCSVHelperTest-regexes.csv');

        # Check regexes
        $helper = new AbsenceImportCSVHelper();
        $results  = $helper->import($uploadedFile, $loggedin_id);
        $absences = $entityManager->getRepository(Absence::class)->findBy(array('perso_id' => $example_agent->getId()));
        $this->assertEquals(count($absences), 3, "Three absences have been added");
        $absence = $absences[0];

        # Start morning
        $this->assertEquals($absence->getStart(),  \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-01 09:00:00'), "start date was successfully transformed");

        # End afternoon
        $this->assertEquals($absence->getEnd(),    \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-01 20:00:00'), "end date was successfully transformed");

        $absence = $absences[1];

        # Start afternoon
        $this->assertEquals($absence->getStart(),  \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-02 13:00:00'), "start date was successfully transformed");

        # End morning
        $this->assertEquals($absence->getEnd(),  \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-03 13:00:00'), "start date was successfully transformed");

        $absence = $absences[2];

        # Start nothing
        $this->assertEquals($absence->getStart(),  \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-04 09:00:00'), "start date was successfully transformed");

        # End nothing
        $this->assertEquals($absence->getEnd(),  \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-05 20:00:00'), "start date was successfully transformed");
    }

    private function setUploadedFile($filename): \Symfony\Component\HttpFoundation\File\UploadedFile {
        $local_file = __DIR__ . '/' . $filename;
        return new Symfony\Component\HttpFoundation\File\UploadedFile(
            $local_file,
            $filename,
            'text/csv',
            null,
            true
        );
    }
}
