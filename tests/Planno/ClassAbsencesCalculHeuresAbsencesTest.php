<?php

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\WorkingHour;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

require_once(__DIR__ . '/../../legacy/Class/class.absences.php');

class ClassAbsencesCalculHeuresAbsencesTest extends TestCase
{
    protected Agent $agent;
    protected WorkingHour $workingHour;

    public function setUp(): void
    {
        global $entityManager;

        $workingHours = [
            ["09:00:00","","","17:00:00"],
            ["09:00:00","","","17:00:00"],
            ["09:00:00","","","17:00:00"],
            ["09:00:00","","","17:00:00"],
            ["09:00:00","","","17:00:00"],
            ["09:00:00","","","17:00:00"],
        ];

        $builder = new FixtureBuilder();
        $this->agent = $builder->build(Agent::class, [
            'temps' => $workingHours,
        ]);
        $this->workingHour = new WorkingHour;
        $this->workingHour
             ->setUser($this->agent->getId())
             ->setStart(new DateTime('2026-01-01'))
             ->setEnd(new DateTime('2026-12-31'))
             ->setWorkingHours($workingHours)
             ->setBreaktime([1,1,1,1,1,1])
             ->setValidLevel2(1);
        $entityManager->persist($this->workingHour);
        $entityManager->flush();

        $GLOBALS['config']['PlanningHebdo'] = true;
    }

    public function tearDown(): void
    {
        global $entityManager;

        $entityManager->remove($this->workingHour);
        $entityManager->remove($this->agent);
        $entityManager->flush();
    }

    public function testCalculHeuresAbsenceExtendingToNextWeek(): void
    {
        global $entityManager;

        $_SESSION['oups']['CSRFToken'] = '00000';

        $date = '2026-02-03'; // Wednesday
        $absenceStart = '2026-02-07 00:00:00'; // Saturday
        $absenceEnd = '2026-02-13 23:59:59'; // Friday of the next week

        $absenceHours = $this->calculHeuresAbsences($date);
        $this->assertEquals($absenceHours, []);

        $absence = $this->createAbsence($this->agent->getId(), $absenceStart, $absenceEnd);

        $absenceHours = $this->calculHeuresAbsences($date);
        $this->assertEquals(
            [$this->agent->getId() => 7],
            $absenceHours,
            'calculHeuresAbsences should only consider absence within the week, not the whole absence period'
        );

        $entityManager->remove($absence);
        $entityManager->flush();
    }

    /**
     * @return int[]
     */
    protected function calculHeuresAbsences(string $date): array
    {
        $this->clearHeuresAbsencesCache();

        $absences = new absences;
        $absences->CSRFToken = '00000';

        return $absences->calculHeuresAbsences($date);
    }

    protected function createAbsence(int $agentId, string $start, string $end): Absence
    {
        $builder = new FixtureBuilder();
        $absence = $builder->build(
            Absence::class,
            [
                'perso_id' => $agentId,
                'debut' => new DateTime($start),
                'fin' => new DateTime($end),
                'valide' => 1,
                'groupe' => '',
            ]
        );

        return $absence;
    }

    /**
     * Empty `heures_absences` table to force `calculHeuresAbsences` to
     * recalculate absence hours.
     */
    protected function clearHeuresAbsencesCache(): void
    {
        global $entityManager;
        $query = $entityManager->createQuery('DELETE FROM \App\Entity\HoursAbsence');
        $query->execute();
    }
}

