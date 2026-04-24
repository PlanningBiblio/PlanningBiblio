<?php

namespace App\Tests\Class\Legacy;

use App\Entity\Agent;
use App\Entity\WorkingHour;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\FixtureBuilder;

require_once(__DIR__ . '/../../../legacy/Class/class.planningHebdo.php');

class ClassPlanningHebdoTest extends KernelTestCase
{
    private $entityManager;
    private $userId;

    public static function setUpBeforeClass(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(WorkingHour::class);

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

        // Working Hours
        $start = date_create('january 1st');
        $end = date_create('december 31th');

        $wh = new WorkingHour();
        $wh->setUser($userId);
        $wh->setStart($start);
        $wh->setEnd($end);
        $wh->setWorkingHours([['09:00:00', '12:00:00', '13:00:00', '17:00:00', '1']]);
        $entityManager->persist($wh);
        $entityManager->flush();

        $wh = new WorkingHour();
        $wh->setUser($userId2);
        $wh->setStart($start);
        $wh->setEnd($end);
        $wh->setWorkingHours([['10:00:00', '13:00:00', '14:00:00', '18:00:00', '1']]);
        $entityManager->persist($wh);
        $entityManager->flush();
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(WorkingHour::class);
    }

    protected function setUp(): void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->userId = $this->entityManager->getRepository(Agent::class)->findOneBy(['login' => 'jdupont'])->getId();
    }

    public function testFetchAll(): void
    {
        $c = new \planningHebdo();
        $c->fetch();

        $this->assertCount(2, $c->elements);
    }

    public function testFetchByUserId(): void
    {
        $c = new \planningHebdo();
        $c->perso_id = $this->userId;
        $c->fetch();

        $this->assertCount(1, $c->elements);
        $this->assertEqualsCanonicalizing([['09:00:00', '12:00:00', '13:00:00', '17:00:00', '1']], $c->elements[0]['temps']);
    }

    public function testFetchByOldDates(): void
    {
        $start = date('Y-m-d', strtotime('24 month ago'));
        $end = date('Y-m-d', strtotime('14 month ago'));

        $c = new \planningHebdo();
        $c->debut = $start;
        $c->fin = $end;
        $c->fetch();

        $this->assertCount(0, $c->elements);
    }

    public function testFetchByDates(): void
    {
        $start = date('Y-m-d');
        $end = date('Y-m-d');

        $c = new \planningHebdo();
        $c->debut = $start;
        $c->fin = $end;
        $c->fetch();

        $this->assertCount(2, $c->elements);
    }

    public function testFetchByDatesAndUserId(): void
    {
        $start = date('Y-m-d');
        $end = date('Y-m-d');

        $c = new \planningHebdo();
        $c->debut = $start;
        $c->fin = $end;
        $c->perso_id = $this->userId;
        $c->fetch();

        $this->assertCount(1, $c->elements);
        $this->assertEqualsCanonicalizing([['09:00:00', '12:00:00', '13:00:00', '17:00:00', '1']], $c->elements[0]['temps']);
    }

    public function testFetchById(): void
    {
        $id = $this->entityManager->getRepository(WorkingHour::class)->findOneBy([
            'perso_id' => $this->userId,
        ])->getId();

        $c = new \planningHebdo();
        $c->id = $id;
        $c->fetch();

        $this->assertCount(1, $c->elements);
        $this->assertEqualsCanonicalizing([['09:00:00', '12:00:00', '13:00:00', '17:00:00', '1']], $c->elements[0]['temps']);
    }
}
