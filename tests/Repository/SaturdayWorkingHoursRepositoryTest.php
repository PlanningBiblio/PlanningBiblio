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

    public function testUpdate()
    {
        $builder = new FixtureBuilder();
        $builder->delete(SaturdayWorkingHours::class);

        $repos = $this->entityManager->getRepository(SaturdayWorkingHours::class);

        $perso_id = 99;
        $other_perso_id = 999;

        $builder->build(SaturdayWorkingHours::class, array('semaine' => $this->expiredDate, 'perso_id' => $perso_id, 'tableau' => 3));// keep
        $builder->build(SaturdayWorkingHours::class, array('semaine' => $this->start, 'perso_id' => $perso_id));// delete
        $builder->build(SaturdayWorkingHours::class, array('semaine' => $this->end, 'perso_id' => $other_perso_id));// keep it because perso_id does not match

        $weeks = [
            [$this->date->format('Y-m-d'), 1],
            [$this->start->format('Y-m-d'), 4],
            $this->end->format('Y-m-d'),
            [$this->expiredDate->format('Y-m-d'), 3]
        ];

        $resultsBefore = $repos->findBy(['perso_id' => $perso_id]);

        $this->assertCount(2, $resultsBefore);

        $repos->update($weeks, $this->start->format('Y-m-d'), $this->end->format('Y-m-d'), $perso_id);
        
        $this->entityManager->clear();
        $repos = $this->entityManager->getRepository(SaturdayWorkingHours::class);
        $resultsAfter = $repos->findBy(['perso_id' => $perso_id]);

        $this->assertCount(5, $repos->findBy(['perso_id' => $perso_id]));
        $this->assertCount(1, $repos->findBy(['perso_id' => $other_perso_id]));

        foreach ($resultsAfter as $entry) {
            $this->assertEquals($perso_id, $entry->getUserId());

            $week = $entry->getWeek()->format('Y-m-d');

            if ($week === $this->date->format('Y-m-d')) {
                $this->assertEquals(1, $entry->getTable());

            } elseif ($week === $this->start->format('Y-m-d')) {
                $this->assertEquals(4, $entry->getTable());

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
