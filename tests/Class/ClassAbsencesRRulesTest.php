<?php

use PHPUnit\Framework\TestCase;

use Tests\FixtureBuilder;
use Symfony\Component\DomCrawler\Crawler;

use App\Model\Absence;
use App\Model\Agent;

require_once(__DIR__ . '/../../public/absences/class.absences.php');

class ClassAbsencesRRulesTest extends TestCase
{
    public function testEveryTwoWeeks(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent= $builder->build(
            Agent::class,
            array( 'login' => 'akhalan', 'actif' => 'Actif', 'sites' => json_encode(["1"]))
        );

        $_SESSION['oups']['CSRFToken'] = '00000';

        // Absence from 2022-10-10 to 2022-10-10
        // Every 2 weeks
        // Ends after 5 repeats
        $a = new \absences();
        $a->debut = '10/10/2022';
        $a->fin = '10/10/2022';
        $a->hre_debut = '00:00:00';
        $a->hre_fin = '23:59:59';
        $a->perso_ids = array($agent->id());
        $a->commentaires = 'This is an absence';
        $a->motif = 'Formation';
        $a->motif_autre = '';
        $a->CSRFToken = '00000';
        $a->rrule = 'FREQ=WEEKLY;WKST=MO;INTERVAL=2;BYDAY=MO;COUNT=5';
        $a->valide = '1';
        $a->pj1 = '';
        $a->pj2 = '';
        $a->so = '';
        $a->add();

        $absences = $entityManager->getRepository(Absence::class)
                                  ->findBy(
                                      array('perso_id' => $agent->id()),
                                      array('debut' => 'ASC')
                                  );

        $this->assertEquals(5, count($absences), '5 absences generated from 2022-10-10');

        $first_absence = $absences[0];
        $second_absence = $absences[1];
        $third_absence = $absences[2];
        $fourth_absence = $absences[3];
        $fifth_absence = $absences[4];

        // First absence
        $this->assertEquals('2022-10-10', $first_absence->debut()->format('Y-m-d'),
            'First absence starts at 2022-10-10');

        $this->assertEquals('2022-10-10', $first_absence->fin()->format('Y-m-d'),
            'First absence ends at 2022-10-10');

        // Second absence
        $this->assertEquals('2022-10-24', $second_absence->debut()->format('Y-m-d'),
            'Second absence starts at 2022-10-24');

        $this->assertEquals('2022-10-24', $second_absence->fin()->format('Y-m-d'),
            'second absence ends at 2022-10-24');

        // Third absence
        $this->assertEquals('2022-11-07', $third_absence->debut()->format('Y-m-d'),
            'Third absence starts at 2022-11-07');

        $this->assertEquals('2022-11-07', $third_absence->fin()->format('Y-m-d'),
            'Third absence ends at 2022-11-07');

        // Fourth absence
        $this->assertEquals('2022-11-21', $fourth_absence->debut()->format('Y-m-d'),
            'Fourth absence starts at 2022-11-21');

        $this->assertEquals('2022-11-21', $fourth_absence->fin()->format('Y-m-d'),
            'Fourth absence ends at 2022-11-21');

        // Fifth absence
        $this->assertEquals('2022-12-05', $fifth_absence->debut()->format('Y-m-d'),
            'Fifth absence starts at 2022-12-05');

        $this->assertEquals('2022-12-05', $fifth_absence->fin()->format('Y-m-d'),
            'Fifth absence ends at 2022-12-05');
    }

    public function testFourDaysAbsenceEveryThreeWeeks(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent= $builder->build(
            Agent::class,
            array( 'login' => 'akhalan', 'actif' => 'Actif', 'sites' => json_encode(["1"]))
        );

        $_SESSION['oups']['CSRFToken'] = '00000';

        // Absence from 2022-12-10 to 2022-12-13
        // Every 3 weeks
        // Until 20/01/2023
        $a = new \absences();
        $a->debut = '10/12/2022';
        $a->fin = '13/12/2022';
        $a->hre_debut = '00:00:00';
        $a->hre_fin = '23:59:59';
        $a->perso_ids = array($agent->id());
        $a->commentaires = 'This is an absence';
        $a->motif = 'Formation';
        $a->motif_autre = '';
        $a->CSRFToken = '00000';
        $a->rrule = 'FREQ=WEEKLY;WKST=MO;INTERVAL=3;BYDAY=MO;UNTIL=20230120T000000Z';
        $a->valide = '1';
        $a->pj1 = '';
        $a->pj2 = '';
        $a->so = '';
        $a->add();

        $absences = $entityManager->getRepository(Absence::class)
                                  ->findBy(
                                      array('perso_id' => $agent->id()),
                                      array('debut' => 'ASC')
                                  );

        $this->assertEquals(3, count($absences), '5 absences generated from 2022-10-10');

        $first_absence = $absences[0];
        $second_absence = $absences[1];
        $third_absence = $absences[2];

        // First absence
        $this->assertEquals('2022-12-10', $first_absence->debut()->format('Y-m-d'),
            'First absence starts at 10/12/2022');

        $this->assertEquals('2022-12-13', $first_absence->fin()->format('Y-m-d'),
            'First absence ends at 13/12/2022');

        // Second absence
        $this->assertEquals('2022-12-26', $second_absence->debut()->format('Y-m-d'),
            'Second absence starts at 2022-31-24');

        $this->assertEquals('2022-12-29', $second_absence->fin()->format('Y-m-d'),
            'second absence ends at 2023-01-04');

        // Third absence
        $this->assertEquals('2023-01-16', $third_absence->debut()->format('Y-m-d'),
            'Third absence 2023 at 2022-01-25');

        $this->assertEquals('2023-01-19', $third_absence->fin()->format('Y-m-d'),
            'Third absence ends at 2022-01-29');
    }

    public function testEveryFourDays(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent= $builder->build(
            Agent::class,
            array( 'login' => 'akhalan', 'actif' => 'Actif', 'sites' => json_encode(["1"]))
        );

        $_SESSION['oups']['CSRFToken'] = '00000';

        // Absence from 2022-10-10 to 2022-10-10
        // Every 4 days
        // Ends after 3 repeats
        $a = new \absences();
        $a->debut = '10/10/2022';
        $a->fin = '10/10/2022';
        $a->hre_debut = '00:00:00';
        $a->hre_fin = '23:59:59';
        $a->perso_ids = array($agent->id());
        $a->commentaires = 'This is an absence';
        $a->motif = 'Formation';
        $a->motif_autre = '';
        $a->CSRFToken = '00000';
        $a->rrule = 'FREQ=DAILY;WKST=MO;INTERVAL=4;BYDAY=MO;COUNT=3';
        $a->valide = '1';
        $a->pj1 = '';
        $a->pj2 = '';
        $a->so = '';
        $a->add();

        $absences = $entityManager->getRepository(Absence::class)
                                  ->findBy(
                                      array('perso_id' => $agent->id()),
                                      array('debut' => 'ASC')
                                  );

        $this->assertEquals(3, count($absences), '5 absences generated from 2022-10-10');

        $first_absence = $absences[0];
        $second_absence = $absences[1];
        $third_absence = $absences[2];

        // First absence
        $this->assertEquals('2022-10-10', $first_absence->debut()->format('Y-m-d'),
            'First absence starts at 2022-10-10');

        $this->assertEquals('2022-10-10', $first_absence->fin()->format('Y-m-d'),
            'First absence ends at 2022-10-10');

        // Second absence
        $this->assertEquals('2022-10-14', $second_absence->debut()->format('Y-m-d'),
            'Second absence starts at 2022-10-14');

        $this->assertEquals('2022-10-14', $second_absence->fin()->format('Y-m-d'),
            'second absence ends at 2022-10-14');

        // Third absence
        $this->assertEquals('2022-10-18', $third_absence->debut()->format('Y-m-d'),
            'Third absence starts at 2022-10-18');

        $this->assertEquals('2022-10-18', $third_absence->fin()->format('Y-m-d'),
            'Third absence ends at 2022-10-18');
    }


    public function testThreeDaysAbsenceEverySixDays(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent= $builder->build(
            Agent::class,
            array( 'login' => 'akhalan', 'actif' => 'Actif', 'sites' => json_encode(["1"]))
        );

        $_SESSION['oups']['CSRFToken'] = '00000';

        // Absence from 2022-10-10 to 2022-10-13
        // Every 6 days
        // Ends after 2 repeats
        $a = new \absences();
        $a->debut = '10/10/2022';
        $a->fin = '13/10/2022';
        $a->hre_debut = '00:00:00';
        $a->hre_fin = '23:59:59';
        $a->perso_ids = array($agent->id());
        $a->commentaires = 'This is an absence';
        $a->motif = 'Formation';
        $a->motif_autre = '';
        $a->CSRFToken = '00000';
        $a->rrule = 'FREQ=DAILY;WKST=MO;INTERVAL=6;BYDAY=MO;COUNT=2';
        $a->valide = '1';
        $a->pj1 = '';
        $a->pj2 = '';
        $a->so = '';
        $a->add();

        $absences = $entityManager->getRepository(Absence::class)
                                  ->findBy(
                                      array('perso_id' => $agent->id()),
                                      array('debut' => 'ASC')
                                  );

        $this->assertEquals(2, count($absences), '5 absences generated from 2022-10-10');

        $first_absence = $absences[0];
        $second_absence = $absences[1];


        // First absence
        $this->assertEquals('2022-10-10', $first_absence->debut()->format('Y-m-d'),
            'First absence starts at 2022-10-10');

        $this->assertEquals('2022-10-13', $first_absence->fin()->format('Y-m-d'),
            'First absence ends at 2022-10-13');

        // Second absence
        $this->assertEquals('2022-10-16', $second_absence->debut()->format('Y-m-d'),
            'First absence starts at 2022-10-16');

        $this->assertEquals('2022-10-19', $second_absence->fin()->format('Y-m-d'),
            'First absence ends at 2022-10-19');
    }

    public function testEveryTwoMonthFirstMonday(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent= $builder->build(
            Agent::class,
            array( 'login' => 'akhalan', 'actif' => 'Actif', 'sites' => json_encode(["1"]))
        );

        $_SESSION['oups']['CSRFToken'] = '00000';

        // Absence from 2022-10-10 to 2022-10-10
        // Every first monday of month
        // Ends after 2 repeats
        $a = new \absences();
        $a->debut = '10/10/2022';
        $a->fin = '10/10/2022';
        $a->hre_debut = '00:00:00';
        $a->hre_fin = '23:59:59';
        $a->perso_ids = array($agent->id());
        $a->commentaires = 'This is an absence';
        $a->motif = 'Formation';
        $a->motif_autre = '';
        $a->CSRFToken = '00000';
        $a->rrule = 'FREQ=MONTHLY;WKST=MO;INTERVAL=1;BYDAY=1MO;COUNT=2';
        $a->valide = '1';
        $a->pj1 = '';
        $a->pj2 = '';
        $a->so = '';
        $a->add();

        $absences = $entityManager->getRepository(Absence::class)
                                  ->findBy(
                                      array('perso_id' => $agent->id()),
                                      array('debut' => 'ASC')
                                  );

        $this->assertEquals(2, count($absences), '5 absences generated from 2022-10-10');

        $first_absence = $absences[0];
        $second_absence = $absences[1];


        // First absence
        $this->assertEquals('2022-10-10', $first_absence->debut()->format('Y-m-d'),
            'First absence starts at 2022-10-10');

        $this->assertEquals('2022-10-10', $first_absence->fin()->format('Y-m-d'),
            'First absence ends at 2022-10-10');

        // Second absence
        $this->assertEquals('2022-11-07', $second_absence->debut()->format('Y-m-d'),
            'First absence starts at 2022-11-07');

        $this->assertEquals('2022-11-07', $second_absence->fin()->format('Y-m-d'),
            'First absence ends at 2022-11-07');
    }

    public function testEveryTwoMonthTen(): void
    {
        global $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $agent= $builder->build(
            Agent::class,
            array( 'login' => 'akhalan', 'actif' => 'Actif', 'sites' => json_encode(["1"]))
        );

        $_SESSION['oups']['CSRFToken'] = '00000';

        // Absence from 2022-10-10 to 2022-10-10
        // Every 2 month, the 10
        // Ends after 3 repeats
        $a = new \absences();
        $a->debut = '10/10/2022';
        $a->fin = '10/10/2022';
        $a->hre_debut = '00:00:00';
        $a->hre_fin = '23:59:59';
        $a->perso_ids = array($agent->id());
        $a->commentaires = 'This is an absence';
        $a->motif = 'Formation';
        $a->motif_autre = '';
        $a->CSRFToken = '00000';
        $a->rrule = 'FREQ=MONTHLY;WKST=MO;INTERVAL=2;BYMONTHDAY=10;UNTIL=20230220T000000Z';
        $a->valide = '1';
        $a->pj1 = '';
        $a->pj2 = '';
        $a->so = '';
        $a->add();

        $absences = $entityManager->getRepository(Absence::class)
                                  ->findBy(
                                      array('perso_id' => $agent->id()),
                                      array('debut' => 'ASC')
                                  );

        $this->assertEquals(3, count($absences), '5 absences generated from 2022-10-10');

        $first_absence = $absences[0];
        $second_absence = $absences[1];
        $third_absence = $absences[2];


        // First absence
        $this->assertEquals('2022-10-10', $first_absence->debut()->format('Y-m-d'),
            'First absence starts at 2022-10-10');

        $this->assertEquals('2022-10-10', $first_absence->fin()->format('Y-m-d'),
            'First absence ends at 2022-10-10');

        // Second absence
        $this->assertEquals('2022-12-10', $second_absence->debut()->format('Y-m-d'),
            'First absence starts at 2022-12-10');

        $this->assertEquals('2022-12-10', $second_absence->fin()->format('Y-m-d'),
            'First absence ends at 2022-12-10');

        // Third absence
        $this->assertEquals('2023-02-10', $third_absence->debut()->format('Y-m-d'),
            'First absence starts at 2023-02-10');

        $this->assertEquals('2023-02-10', $third_absence->fin()->format('Y-m-d'),
            'First absence ends at 2023-02-10');
    }
}