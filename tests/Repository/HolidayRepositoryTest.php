<?php

namespace App\Tests\Repository;

use App\Entity\Agent;
use App\Entity\Holiday;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

class HolidayRepositoryTest extends TestCase
{
    private $entityManager;

    private $start;
    private $end;

    protected function setUp(): void
    {
        global $entityManager;
        $this->entityManager = $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $this->start = new \DateTime('2025-12-20');
        $this->end = new \DateTime('2026-01-10');

        $builder->build(Agent::class, array(
            'login' => 'amy',
            'conges_credit' => 21.1,
            'conges_reliquat' => 22.22,
            'conges_anticipation' => 23.33,
            'comp_time' => 24,
        ));

        $builder->build(Holiday::class, array(
            'perso_id' => 10,
            'debut' => $this->start,
            'fin' => $this->end,
            'heures' => 13,
            'origin_id' => 14,
        )); 
    }

    public function testInsertWithOriginId(): void
    {
        $repo = $this->entityManager->getRepository(Holiday::class);
        $amy = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'amy']);
        $originHolidayId = $repo->findOneBy(['perso_id' => 10])->getId();

        $credits = array(
            'conges_credit' => 31.1,
            'conges_reliquat' => 32.22,
            'conges_anticipation' => 33.33,
            'comp_time' => 10,
        );

        $repo->insert($amy->getId(), $credits,  'update',true, $originHolidayId, null);

        $holiday = $repo->findOneBy(['perso_id' => $amy->getId()]);

        $this->assertEquals($this->start, $holiday->getStart());
        $this->assertEquals($this->end, $holiday->getEnd());
        $this->assertEquals(14, $holiday->getHours());
        $this->assertEquals($originHolidayId, $holiday->getOriginId());
    }
}
