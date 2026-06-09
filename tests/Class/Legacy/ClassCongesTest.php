<?php

namespace App\Tests\Class\Legacy;

use App\Entity\Agent;
use App\Entity\Holiday;
use App\Entity\OverTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\FixtureBuilder;

require_once(__DIR__ . '/../../../legacy/Class/class.conges.php');

class ClassCongesTest extends KernelTestCase
{
    private $entityManager;
    private $userId;

    public static function setUpBeforeClass(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Holiday::class);
        $builder->delete(OverTime::class);

        // Agents
        $agent = new Agent();
        $agent->setLogin('jdupont');
        $entityManager->persist($agent);
        $entityManager->flush();
        $userId = $agent->getId();

        $agent2 = new Agent();
        $agent2->setLogin('aboiron');
        $entityManager->persist($agent2);
        $entityManager->flush();
        $userId2 = $agent2->getId();

        // Holidays
        $start = date_create('last monday');
        $end = date_create('next sunday');

        $holiday = new Holiday();
        $holiday->setUser($userId);
        $holiday->setStart($start);
        $holiday->setEnd($end);
        $holiday->setDebit('Congés');
        $entityManager->persist($holiday);
        $entityManager->flush();

        $holiday = new Holiday();
        $holiday->setUser($userId2);
        $holiday->setStart($start);
        $holiday->setEnd($end);
        $holiday->setDebit('Congés');
        $entityManager->persist($holiday);
        $entityManager->flush();

        $start = date_create('14 month ago');
        $end = date_create('13 month ago');

        $holiday = new Holiday();
        $holiday->setUser($userId);
        $holiday->setStart($start);
        $holiday->setEnd($end);
        $holiday->setDebit('Récupérations');
        $entityManager->persist($holiday);
        $entityManager->flush();

        // Overtime
        $date = date_create('last saturday');

        $overtTime = new OverTime();
        $overtTime->setUser($userId);
        $overtTime->setDate($date);
        $entityManager->persist($overtTime);
        $entityManager->flush();

        $overtTime = new OverTime();
        $overtTime->setUser($userId2);
        $overtTime->setDate($date);
        $entityManager->persist($overtTime);
        $entityManager->flush();
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Holiday::class);
        $builder->delete(OverTime::class);
   }

    protected function setUp(): void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userId = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont'])->getId();
    }

    public function testFetchAll(): void
    {
        $c = new \conges();
        $c->fetch();

        $this->assertCount(2, $c->elements);
    }

    public function testFetchByUserId(): void
    {
        $c = new \conges();
        $c->perso_id = $this->userId;
        $c->fetch();

        $this->assertCount(1, $c->elements);
    }

    public function testFetchByDates(): void
    {
        $start = date('Y-m-d', strtotime('14 month ago'));
        $end = date('Y-m-d');

        $c = new \conges();
        $c->debut = $start;
        $c->fin = $end;
        $c->fetch();

        $this->assertCount(3, $c->elements);
    }

    public function testFetchByDatesAndUserId(): void
    {
        $start = date('Y-m-d', strtotime('14 month ago'));
        $end = date('Y-m-d');

        $c = new \conges();
        $c->perso_id = $this->userId;
        $c->debut = $start;
        $c->fin = $end;
        $c->fetch();

        $this->assertCount(2, $c->elements);
    }

    public function testFetchByDebit(): void
    {
        $start = date('Y-m-d', strtotime('14 month ago'));
        $end = date('Y-m-d');

        $c = new \conges();
        $c->perso_id = $this->userId;
        $c->debut = $start;
        $c->fin = $end;
        $c->debit = 'Récupérations';
        $c->fetch();

        $this->assertCount(1, $c->elements);
    }

    public function testFetchById(): void
    {
        $start = date('Y-m-d', strtotime('14 month ago'));
        $end = date('Y-m-d', strtotime('13 month ago'));

        $holidayId = $this->entityManager->getRepository(Holiday::class)->findOneBy([
            'perso_id' => $this->userId,
            'debit' => 'Récupérations',
        ])->getId();

        $c = new \conges();
        $c->id = $holidayId;
        $c->fetch();

        $this->assertCount(1, $c->elements);
        $this->assertSame($start, substr($c->elements[0]['debut'], 0, 10));
        $this->assertSame($end, substr($c->elements[0]['fin'], 0, 10));
    }

    public function testGetRecupByUserId(): void
    {
        $c = new \conges();
        $c->perso_id = $this->userId;
        $c->admin = true;
        $c->getRecup();

        $this->assertCount(1, $c->elements);
    }

    public function testGetRecupByDates(): void
    {
        $start = date('Y-m-d', strtotime('1 month ago'));
        $end = date('Y-m-d');

        $c = new \conges();
        $c->debut = $start;
        $c->fin = $end;
        $c->admin = true;
        $c->getRecup();

        $this->assertCount(2, $c->elements);
    }

    public function testGetRecupById(): void
    {
        $OverTimeId = $this->entityManager->getRepository(OverTime::class)->findOneBy([
            'perso_id' => $this->userId,
        ])->getId();

        $c = new \conges();
        $c->recupId = $OverTimeId;
        $c->admin = true;
        $c->getRecup();

        $this->assertCount(1, $c->elements);
    }
}
