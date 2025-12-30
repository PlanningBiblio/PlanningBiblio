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
    }

    private function configure(): void
    {
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

    public function testInsertCreatesHolidayWhenCreditsChange()
    {
        $builder = new FixtureBuilder();
        $builder->delete(Holiday::class);
        
        $holidayRepo = $this->entityManager->getRepository(Holiday::class);
    
        $agent = new Agent();
        $agent->setLogin('alice');
        $agent->setHolidayCredit(5);
        $agent->setHolidayCompTime(2);
        $agent->setHolidayRemainder(1);
        $agent->setHolidayAnticipation(0);

        $this->entityManager->persist($agent);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $credits = [
            'conges_credit' => 10,
            'comp_time' => 4,
            'conges_reliquat' => 1,
            'conges_anticipation' => 0,
            'conges_annuel' => 0,
        ];

        $holidayRepo->insert(
            $agent->getId(),
            $credits,
            'update',
            true // cron = true pour éviter Session
        );

        $holidays = $this->entityManager->getRepository(Holiday::class)->findAll();
        $this->assertCount(1, $holidays);

        $holiday = $holidays[0];

        $this->assertEquals(5, $holiday->getPreviousCredit());
        $this->assertEquals(10, $holiday->getActualCredit());
        $this->assertEquals(2, $holiday->getPreviousCompTime());
        $this->assertEquals(4, $holiday->getActualCompTime());
    }

    public function testInsertDoesNothingWhenCreditsUnchanged(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Holiday::class);

        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy([
            'login' => 'alice',
        ]);

        $credits = [
            'conges_credit' => 5,
            'comp_time' => 2,
            'conges_reliquat' => 1,
            'conges_anticipation' => 0,
            'conges_annuel' => 0,
        ];

        $holidayRepo = $this->entityManager->getRepository(Holiday::class);
        $holidayRepo->insert(
            $agent->getId(),
            $credits,
            'update',
            true
        );

        $holidays = $this->entityManager->getRepository(Holiday::class)->findAll();
        $this->assertCount(0, $holidays);

        // Now test with 'add' action
        $holidayRepo->insert(
            $agent->getId(),
            $credits,
            'add',
            true
        );

        $holidays = $this->entityManager->getRepository(Holiday::class)->findAll();
        $this->assertCount(1, $holidays);

        $holiday = $holidays[0];

        $this->assertEquals(0, $holiday->getPreviousCredit());
        $this->assertEquals(5, $holiday->getActualCredit());
        $this->assertEquals(0, $holiday->getPreviousCompTime());
        $this->assertEquals(2, $holiday->getActualCompTime());
    }

    public function testInsertSetsOriginHolidayId(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Holiday::class);

        $agent = $this->entityManager->getRepository(Agent::class)->findOneBy([
            'login' => 'alice',
        ]);

        $originHoliday = $builder->build(
            Holiday::class,
            [
                'perso_id' => $agent->getId(),
                'debut' => new \DateTime('-10 days'),
                'fin' => new \DateTime('-5 days'),
                'information' => 0,
                'supprime' => 0,
                'valide' => 1,
            ]
        );

        $credits = [
            'conges_credit' => 8,
            'comp_time' => 1.1,
            'conges_reliquat' => 1,
            'conges_anticipation' => 0,
            'conges_annuel' => 0,
        ];

        $holidayRepo = $this->entityManager->getRepository(Holiday::class);
        $holidayRepo->insert(
            $agent->getId(),
            $credits,
            'update',
            true,
            $originHoliday->getId()
        );

        $holidays = $this->entityManager->getRepository(Holiday::class)->findAll();
        $this->assertCount(1, $holidays);

        $holiday = $holidays[0];

        $this->assertEquals($originHoliday->getId(), $holiday->getOriginId());
        $this->assertEquals(0.9, $holiday->getHours());
    }

    public function testInsertWithAddWithoutOriginId(): void
    {
        self::configure();

        $amy = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'amy']);
        $repo = $this->entityManager->getRepository(Holiday::class);
        $originHolidayId = $repo->findOneBy(['perso_id' => 10])->getId();

        $credits = array(
            'conges_credit' => 31.1,
            'conges_reliquat' => 32.22,
            'conges_anticipation' => 33.33,
            'comp_time' => 10,
        );

        $repo->insert($amy->getId(), $credits, 'add', true);

        $holiday = $repo->findOneBy(['perso_id' => $amy->getId()]);
        $originHoliday = $repo->find($originHolidayId);

        $this->assertEquals(new \DateTime(date('Y-m-d') . ' 00:00:00'), $holiday->getStart());
        $this->assertEquals(new \DateTime(date('Y-m-d') . ' 00:00:00'), $holiday->getEnd());
        $this->assertEquals(0, $holiday->getPreviousCredit());
        $this->assertEquals(0, $holiday->getPreviousCompTime());
        $this->assertEquals(0, $holiday->getPreviousRemainder());
        $this->assertEquals(0, $holiday->getPreviousAnticipation());
        $this->assertEquals($credits['conges_credit'], $holiday->getActualCredit());
        $this->assertEquals($credits['comp_time'], $holiday->getActualCompTime());
        $this->assertEquals($credits['conges_reliquat'], $holiday->getActualRemainder());
        $this->assertEquals($credits['conges_anticipation'], $holiday->getActualAnticipation());
        $this->assertEquals(999999999, $holiday->getInfo());
        $this->assertEquals((new \DateTime())->getTimestamp(), $holiday->getInfoDate()->getTimestamp());
    }

    public function testInsertWithUpdateWithoutOriginId(): void
    {
        self::configure();

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

    public function testInsertWithUpdateWithOriginId(): void
    {
        self::configure();

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
