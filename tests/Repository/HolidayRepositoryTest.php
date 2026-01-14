<?php

namespace App\Tests\Repository;

use App\Entity\Agent;
use App\Entity\Holiday;
use PHPUnit\Framework\TestCase;
use Tests\FixtureBuilder;

class HolidayRepositoryTest extends TestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        global $entityManager;
        $this->entityManager = $entityManager;

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);

        $start = new \DateTime('2025-12-20');
        $end = new \DateTime('2026-01-10');

        $builder->build(Agent::class, array(
            'login' => 'amy',
            'conges_credit' => 21.1,
            'conges_reliquat' => 22.22,
            'conges_anticipation' => 23.33,
            'comp_time' => 24,
        ));

        $builder->build(Holiday::class, array(
            'perso_id' => 10,
            'debut' => $start,
            'fin' => $end,
            'heures' => 13,
            'origin_id' => 14,
        )); 
    }

    public function testInsertWithUpdateWithoutOriginId(): void
    {
        $amy = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'amy']);
        $repo = $this->entityManager->getRepository(Holiday::class);
        $originHolidayId = $repo->findOneBy(['perso_id' => 10])->getId();

        $credits = array(
            'conges_credit' => 31.1,
            'conges_reliquat' => 32.22,
            'conges_anticipation' => 33.33,
            'comp_time' => 10,
        );

        $repo->insert($amy->getId(), $credits, 'update', true);

        $holiday = $repo->findOneBy(['perso_id' => $amy->getId()]);
        $originHoliday = $repo->find($originHolidayId);

        $this->assertEquals(new \DateTime(date('Y-m-d') . ' 00:00:00'), $holiday->getStart());
        $this->assertEquals(new \DateTime(date('Y-m-d') . ' 00:00:00'), $holiday->getEnd());
        $this->assertEquals($amy->getHolidayCredit(), $holiday->getPreviousCredit());
        $this->assertEquals($amy->getHolidayCompTime(), $holiday->getPreviousCompTime());
        $this->assertEquals($amy->getHolidayRemainder(), $holiday->getPreviousRemainder());
        $this->assertEquals($amy->getHolidayAnticipation(), $holiday->getPreviousAnticipation());
        $this->assertEquals($credits['conges_credit'], $holiday->getActualCredit());
        $this->assertEquals($credits['comp_time'], $holiday->getActualCompTime());
        $this->assertEquals($credits['conges_reliquat'], $holiday->getActualRemainder());
        $this->assertEquals($credits['conges_anticipation'], $holiday->getActualAnticipation());
        $this->assertEquals(999999999, $holiday->getInfo());
        $this->assertEquals((new \DateTime())->getTimestamp(), $holiday->getInfoDate()->getTimestamp());
    }

    public function testInsertWithOriginId(): void
    {
        $amy = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'amy']);
        $repo = $this->entityManager->getRepository(Holiday::class);
        $originHolidayId = $repo->findOneBy(['perso_id' => 10])->getId();

        $credits = array(
            'conges_credit' => 31.1,
            'conges_reliquat' => 32.22,
            'conges_anticipation' => 33.33,
            'comp_time' => 10,
        );

        $repo->insert($amy->getId(), $credits,  'update',true, $originHolidayId);

        $holiday = $repo->findOneBy(['perso_id' => $amy->getId()]);
        $originHoliday = $repo->find($originHolidayId);

        $this->assertEquals($originHoliday->getStart(), $holiday->getStart());
        $this->assertEquals($originHoliday->getEnd(), $holiday->getEnd());
        $this->assertEquals(14, $holiday->getHours());
        $this->assertEquals($originHolidayId, $holiday->getOriginId());
    }
}
