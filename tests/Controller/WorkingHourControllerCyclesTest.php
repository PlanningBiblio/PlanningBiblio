<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\WorkingHour;
use App\Entity\WorkingHourCycle;
use Tests\PLBWebTestCase;

class WorkingHourControllerCyclesTest extends PLBWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    private function configure(): void
    {
        parent::setData('data7');

        $this->setParam('nb_semaine', 3);
        $this->setParam('dateDebutPlHebdo', '29/12/2025');
        $this->setParam('PlanningHebdo-resetCycles', 2);

        $this->builder->delete(WorkingHour::class);
        $this->builder->delete(WorkingHourCycle::class);

        // Create Working hours
        $start = new \DateTime('- 1 year');
        $end = new \DateTime('+ 1 year');

        // Alex (9), site n°1
        $alexWorkingHour = new WorkingHour();
        $alexWorkingHour->setUser(9);
        $alexWorkingHour->setStart($start);
        $alexWorkingHour->setEnd($end);
        $alexWorkingHour->setValidLevel2(1);
        $alexWorkingHour->setWorkingHours([
            // Week 1, Alex doesn't work on monday, saturday and sunday
            0 => ['', '', '', '', ''],
            1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', '1'],
            2 => ['09:00:00', '13:00:00', '', '', '1'],
            3 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            4 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            // Week 2, Alex doesn't work on monday, tuesday, wednesday and sunday
            7 => ['', '', '', '', ''],
            8 => ['', '', '', '', ''],
            9 => ['', '', '', '', ''],
            10 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            11 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            12 => ['09:00:00', '13:00:00', '14:00:00', '18:00:00', '1'],
            // Week 3, Alex doesn't work on monday, thursday, saturday and sunday
            14 => ['', '', '', '', ''],
            15 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', '1'],
            16 => ['09:00:00', '13:00:00', '', '', '1'],
            17 => ['', '', '', '', ''],
            18 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
        ]);

        $this->entityManager->persist($alexWorkingHour);

        // Aurélie (14), site n°1
        $aurelieWorkingHour = new WorkingHour();
        $aurelieWorkingHour->setUser(14);
        $aurelieWorkingHour->setStart($start);
        $aurelieWorkingHour->setEnd($end);
        $aurelieWorkingHour->setValidLevel2(1);
        $aurelieWorkingHour->setWorkingHours([
            // Week 1, Aurélie doesn't work on monday, thursday, saturday and sunday
            0 => ['', '', '', '', ''],
            1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', '1'],
            2 => ['09:00:00', '13:00:00', '', '', '1'],
            3 => ['', '', '', '', ''],
            4 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            // Week 2, Aurélie doesn't work on monday, saturday and sunday
            7 => ['', '', '', '', ''],
            8 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', '1'],
            9 => ['09:00:00', '13:00:00', '', '', '1'],
            10 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            11 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            // Week 3, Aurélie doesn't work on monday, tuesday, wednesday and sunday
            14 => ['', '', '', '', ''],
            15 => ['', '', '', '', ''],
            16 => ['', '', '', '', ''],
            17 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            18 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            19 => ['09:00:00', '13:00:00', '14:00:00', '18:00:00', '1'],
        ]);

        $this->entityManager->persist($aurelieWorkingHour);

        // Delphine (15), site n°2
        $delphineWorkingHour = new WorkingHour();
        $delphineWorkingHour->setUser(15);
        $delphineWorkingHour->setStart($start);
        $delphineWorkingHour->setEnd($end);
        $delphineWorkingHour->setValidLevel2(1);
        $delphineWorkingHour->setWorkingHours([
            // Week 1, Delphine doesn't work on monday, saturday and sunday
            0 => ['', '', '', '', ''],
            1 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', '2'],
            2 => ['09:00:00', '13:00:00', '', '', '1'],
            3 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '2'],
            4 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '2'],
            // Week 2, Delphine doesn't work on monday, tuesday, wednesday and sunday
            7 => ['', '', '', '', ''],
            8 => ['', '', '', '', ''],
            9 => ['', '', '', '', ''],
            10 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '2'],
            11 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '2'],
            12 => ['09:00:00', '13:00:00', '14:00:00', '18:00:00', '2'],
            // Week 3, Delphine doesn't work on monday, thursday, saturday and sunday
            14 => ['', '', '', '', ''],
            15 => ['09:00:00', '12:00:00', '13:00:00', '17:00:00', '2'],
            16 => ['09:00:00', '13:00:00', '', '', '1'],
            17 => ['', '', '', '', ''],
            18 => ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '2'],
        ]);

        $this->entityManager->persist($delphineWorkingHour);

        // Use agent 9 and log in
        $agent = $this->entityManager->getRepository(Agent::class)->find(9);
        $agent->setACL([6, 99, 100, 201, 301, 302, 501, 1001, 1002, 1101, 1201]);

        $this->entityManager->persist($agent);

        $this->entityManager->flush();
    }

    private function testWeekDisplay($tests)
    {
        $this->setUpPantherClient();
        $agent = $this->entityManager->getRepository(Agent::class)->find(9);
        $this->login($agent);

        foreach ($tests as $test) {
            $crawler = $this->client->request('GET', "/{$test['site']}/{$test['date']}");
            $this->assertSelectorTextContains('#week_planning', "Semaine {$test['week']}", "{$test['date']}, site N°{$test['site']} should be week {$test['week']}");
        }
    }

    public function testWeekDisplayWithoutCycleReset(): void
    {
        $this->configure();

        $tests = [
            [
                'site' => 1,
                'date' => '2026-01-06',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-01-06',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-01-13',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-13',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-01-20',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-20',
                'week' => 1,
            ],
        ];

        $this->testWeekDisplay($tests);
    }

    public function testWeekDisplayWithCycleResetForAllSites(): void
    {
        $this->setParam('PlanningHebdo-resetCycles', 1);

        $cycle = new WorkingHourCycle();
        $cycle->setDate(new \DateTime('2026-01-05'));
        $cycle->setWeek(1);

        $this->entityManager->persist($cycle);
        $this->entityManager->flush();

        $tests = [
            [
                'site' => 1,
                'date' => '2026-01-06',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-06',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-01-13',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-01-13',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-01-20',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-20',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-01-27',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-27',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-02-03',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-02-03',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-02-10',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-02-10',
                'week' => 3,
            ],
        ];

        $this->testWeekDisplay($tests);

        $cycle = new WorkingHourCycle();
        $cycle->setDate(new \DateTime('2026-01-27'));
        $cycle->setWeek(3);

        $this->entityManager->persist($cycle);
        $this->entityManager->flush();

        $tests = [
            [
                'site' => 1,
                'date' => '2026-01-06',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-06',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-01-13',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-01-13',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-01-20',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-20',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-01-27',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-27',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-02-03',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-02-03',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-02-10',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-02-10',
                'week' => 2,
            ],
        ];

        $this->testWeekDisplay($tests);
    }

    public function testWeekDisplayWithCycleResetSiteBySite(): void
    {
        $this->setParam('PlanningHebdo-resetCycles', 2);

        $this->builder->delete(WorkingHourCycle::class);

        $cycle = new WorkingHourCycle();
        $cycle->setDate(new \DateTime('2026-01-05'));
        $cycle->setSites([1,3,4]);
        $cycle->setWeek(1);

        $this->entityManager->persist($cycle);
        $this->entityManager->flush();

        $tests = [
            [
                'site' => 1,
                'date' => '2026-01-06',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-06',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-01-13',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-01-13',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-01-20',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-20',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-01-27',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-27',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-02-03',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-02-03',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-02-10',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-02-10',
                'week' => 1,
            ],
        ];

        $this->testWeekDisplay($tests);

        $cycle = new WorkingHourCycle();
        $cycle->setDate(new \DateTime('2026-01-27'));
        $cycle->setSites([2]);
        $cycle->setWeek(3);

        $this->entityManager->persist($cycle);
        $this->entityManager->flush();

        $tests = [
            [
                'site' => 1,
                'date' => '2026-01-06',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-06',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-01-13',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-01-13',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-01-20',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-20',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-01-27',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-27',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-02-03',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-02-03',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-02-10',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-02-10',
                'week' => 2,
            ],
        ];

        $this->testWeekDisplay($tests);
    }

    public function testWeekDisplayWithCycleResetBackToAllSites(): void
    {
        $this->setParam('PlanningHebdo-resetCycles', 1);

        // We don't change cycles for this tests, we want sites defined in cycles to be ignore.

        $tests = [
            [
                'site' => 1,
                'date' => '2026-01-06',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-06',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-01-13',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-01-13',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-01-20',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-20',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-01-27',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-27',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-02-03',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-02-03',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-02-10',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-02-10',
                'week' => 2,
            ],
        ];

        $this->testWeekDisplay($tests);
    }

    public function testWeekDisplayDisableCycleReset(): void
    {
        $this->setParam('PlanningHebdo-resetCycles', 0);

        // We don't change cycles for this tests, we want them to be ignore.

        $tests = [
            [
                'site' => 1,
                'date' => '2026-01-06',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-01-06',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-01-13',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-01-13',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-01-20',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-01-20',
                'week' => 1,
            ],
            [
                'site' => 1,
                'date' => '2026-01-27',
                'week' => 2,
            ],
            [
                'site' => 2,
                'date' => '2026-01-27',
                'week' => 2,
            ],
            [
                'site' => 1,
                'date' => '2026-02-03',
                'week' => 3,
            ],
            [
                'site' => 2,
                'date' => '2026-02-03',
                'week' => 3,
            ],
            [
                'site' => 1,
                'date' => '2026-02-10',
                'week' => 1,
            ],
            [
                'site' => 2,
                'date' => '2026-02-10',
                'week' => 1,
            ],
        ];

        $this->testWeekDisplay($tests);

        // Reset data for next tests
        parent::setData();
    }
}
