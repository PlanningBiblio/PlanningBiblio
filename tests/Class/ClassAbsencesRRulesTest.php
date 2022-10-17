<?php

use PHPUnit\Framework\TestCase;

use Tests\FixtureBuilder;

use App\Model\Absence;
use App\Model\Agent;

require_once(__DIR__ . '/../../public/absences/class.absences.php');

class ClassAbsencesRRulesTest extends TestCase
{
    public function testEveryTwoWeeks()
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
}
