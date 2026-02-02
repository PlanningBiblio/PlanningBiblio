<?php

namespace App\Tests;

use App\Entity\Agent;
use App\Entity\Holiday;
use PHPStan\Type\Php\GettypeFunctionReturnTypeExtension;
use Tests\PLBWebTestCase;
use Tests\FixtureBuilder;

use function PHPUnit\Framework\assertEquals;

class HolidayRepositoryTest extends PLBWebTestCase
{
   public function testInsertCreatesHolidayWhenCreditsChange()
   {
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

}
