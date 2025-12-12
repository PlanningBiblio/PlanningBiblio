<?php

use Tests\FixtureBuilder;

use PHPUnit\Framework\TestCase;
use App\Entity\SaturdayWorkingHours;

class SaturdayWorkingHoursRepositoryTest extends TestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $date;
    private $start;
    private $end;
    private $expiredDate;

    protected function setUp(): void
    {
        global $entityManager;
        $this->entityManager = $entityManager;

        $this->date = new \DateTime();
        $this->start = (clone $this->date)->modify('-5 days');
        $this->end = (clone $this->date)->modify('+5 days');
        $this->expiredDate = (clone $this->date)->modify('-10 days');
    }

    public function testDeleteBetweenWeeks()
    {
        $builder = new FixtureBuilder();
        $builder->delete(SaturdayWorkingHours::class);

        $perso_id = 30;
        $other_perso_id = 31;

        $builder->build(SaturdayWorkingHours::class, array('semaine' => $this->date, 'perso_id' => $perso_id));
        $builder->build(SaturdayWorkingHours::class, array('semaine' => $this->expiredDate, 'perso_id' => $perso_id));
        $builder->build(SaturdayWorkingHours::class, array('semaine' => $this->start, 'perso_id' => $perso_id));
        $builder->build(SaturdayWorkingHours::class, array('semaine' => $this->end, 'perso_id' => $other_perso_id));

        $repos = $this->entityManager->getRepository(SaturdayWorkingHours::class);

        $repos->deleteBetweenWeeks($this->start, $this->end, $perso_id);

        $this->assertCount(2, $repos->findBy(['perso_id' => $perso_id]));
        $this->assertCount(1, $repos->findBy(['perso_id' => $other_perso_id]));
    }

    public function testInsert()
    {
        $builder = new FixtureBuilder();
        $builder->delete(SaturdayWorkingHours::class);

        $perso_id = 20;
        $weeks = [
            [$this->date, 1],
            [$this->start, 1],
            $this->end,
            [$this->expiredDate, 3]
        ];

        $repo = $this->entityManager->getRepository(SaturdayWorkingHours::class);
        $repo->insert($weeks, $perso_id);

        $results = $repo->findBy(['perso_id' => $perso_id]);

        $this->assertCount(4, $results);

        foreach ($results as $entry) {
            $this->assertEquals($perso_id, $entry->getUserId());

            $week = $entry->getWeek()->format('Y-m-d');

            if ($week === $this->date->format('Y-m-d')) {
                $this->assertEquals(1, $entry->getTable());

            } elseif ($week === $this->start->format('Y-m-d')) {
                $this->assertEquals(1, $entry->getTable());

            } elseif ($week === $this->end->format('Y-m-d')) {
                $this->assertEquals(2, $entry->getTable());

            } elseif ($week === $this->expiredDate->format('Y-m-d')) {
                $this->assertEquals(3, $entry->getTable());

            } else {
                $this->fail('Unexpected semaine: ' . $week);
            }
        }
    }

}
