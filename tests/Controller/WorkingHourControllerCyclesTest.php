<?php

namespace App\Tests\Controller;

use App\Entity\Agent;
use App\Entity\Config;
use App\Entity\WorkingHour;
use App\Entity\WorkingHourCycle;
use Tests\PLBWebTestCase;

class WorkingHourControllerCyclesTest extends PLBWebTestCase
{
    public static function setUpBeforeClass(): void
    {
        global $entityManager;

        $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'dateDebutPlHebdo'])
            ->setValue('29/12/2025');

        $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'nb_semaine'])
            ->setValue('3');

        $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'PlanningHebdo-resetCycles'])
            ->setValue('1');

//        $entityManager->createQuery('DELETE FROM \App\Entity\Agent')->execute();
        $entityManager->createQuery('DELETE FROM \App\Entity\WorkingHour')->execute();
        $entityManager->createQuery('DELETE FROM \App\Entity\WorkingHourCycle')->execute();

        // Create agents
        $agent = new Agent();
        $agent->setLogin('agent1')
            ->setACL([6, 99, 100, 201, 301, 302, 501, 1001, 1002, 1101, 1201]);

        $alex = new Agent();
        $alex->setLogin('alex');
        $aurelie = new Agent();
        $aurelie->setLogin('aurelie');
        $delphine = new Agent();
        $delphine->setLogin('delphine');

        $entityManager->persist($agent);
        $entityManager->persist($alex);
        $entityManager->persist($aurelie);
        $entityManager->persist($delphine);
        $entityManager->flush();

        // Create Working hours
        $start = new \DateTime('- 1 year');
        $end = new \DateTime('+ 1 year');

        // Alex, site n°1
        $alexWorkingHour = new WorkingHour();
        $alexWorkingHour->setUser($alex->getId());
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

        // Aurélie, site n°1
        $aurelieWorkingHour = new WorkingHour();
        $aurelieWorkingHour->setUser($aurelie->getId());
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

        // Delphine, site n°2
        $delphineWorkingHour = new WorkingHour();
        $delphineWorkingHour->setUser($delphine->getId());
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

        $entityManager->persist($alexWorkingHour);
        $entityManager->persist($aurelieWorkingHour);
        $entityManager->persist($delphineWorkingHour);
        $entityManager->flush();
    }

    private function testWeekDisplay($tests): void
    {
        global $entityManager;
        $agent = $entityManager->getRepository(Agent::class)->findOneBy(['login' => 'agent1']);
        $this->setUpPantherClient();
        $this->login($agent);

        foreach ($tests as $test) {
            $crawler = $this->client->request('GET', "/{$test['site']}/{$test['date']}");
            $this->assertSelectorTextContains('#week_planning', "Semaine {$test['week']}", "{$test['date']}, site N°{$test['site']} should be week {$test['week']}");
        }
    }

    public function testWeekDisplayWithoutCycleReset(): void
    {
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

    public function testWeekDisplayWithCycleReset(): void
    {
        global $entityManager;

        $cycle = new WorkingHourCycle();
        $cycle->setDate(new \DateTime('2026-01-05'));
        $cycle->setWeek(1);

        $entityManager->persist($cycle);
        $entityManager->flush();

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

        $entityManager->persist($cycle);
        $entityManager->flush();

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
        global $entityManager;

        $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'PlanningHebdo-resetCycles'])
            ->setValue('0');
        $entityManager->flush();

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
    }

    public static function tearDownAfterClass(): void
    {
        global $entityManager;

        $entityManager->createQuery('DELETE FROM \App\Entity\WorkingHour')->execute();

        foreach (['agent1', 'alex', 'aurelie', 'delphine'] as $login) {
            $agent = $entityManager->getRepository(Agent::class)->findOneBy(['login' => $login]);
            $entityManager->remove($agent);
        }

        $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'dateDebutPlHebdo'])
            ->setValue('');

        $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'nb_semaine'])
            ->setValue('1');

        $entityManager->getRepository(Config::class)
            ->findOneBy(['nom' => 'PlanningHebdo-resetCycles'])
            ->setValue('0');

        $entityManager->flush();
    }
}
