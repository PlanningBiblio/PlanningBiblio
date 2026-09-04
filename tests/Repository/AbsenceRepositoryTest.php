<?php

namespace App\Tests\Repository;

use App\Entity\Absence;
use App\Entity\Agent;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\FixtureBuilder;

class AbsenceRepositoryTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Absence::class);

        $agent = new Agent();
        $agent->setLogin('jdupont');
        $em->persist($agent);
        $em->flush();
        $userId = $agent->getId();

        $agent = new Agent();
        $agent->setLogin('amartin');
        $em->persist($agent);
        $em->flush();
        $userId2 = $agent->getId();

        $start1 = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 00:00:00');
        $end1 = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 23:59:59');
        $start2 = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 09:00:00');
        $end2 = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00');
        $start3 = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00');

        $absences =[];
        $absences[] = ['start' => $start1, 'end' => $end1, 'userId' => $userId, 'reason' => 'Absence 1', 'validationLevel2' => 0];
        $absences[] = ['start' => $start1, 'end' => $end1, 'userId' => $userId, 'reason' => 'Absence 2', 'validationLevel2' => 3];
        $absences[] = ['start' => $start1, 'end' => $end1, 'userId' => $userId, 'reason' => 'Absence 3', 'validationLevel2' => -3];
        $absences[] = ['start' => $start1, 'end' => $end1, 'userId' => $userId2, 'reason' => 'Absence 4', 'validationLevel2' => 3];
        $absences[] = ['start' => $start1, 'end' => $end2, 'userId' => $userId, 'reason' => 'Absence 5', 'validationLevel2' => 3];
        $absences[] = ['start' => $start2, 'end' => $end1, 'userId' => $userId, 'reason' => 'Absence 6', 'validationLevel2' => 3];
        $absences[] = ['start' => $start2, 'end' => $end2, 'userId' => $userId, 'reason' => 'Absence 7', 'validationLevel2' => 3];
        $absences[] = ['start' => $start3, 'end' => $end1, 'userId' => $userId, 'reason' => 'Absence 8', 'validationLevel2' => 3];

        foreach ($absences as $a) {
            $builder->build(Absence::class, [
                'debut' => $a['start'],
                'fin' => $a['end'],
                'perso_id' => $a['userId'],
                'motif' => $a['reason'],
                'valide' => $a['validationLevel2'],
                'groupe' => '',
            ]);
        }
    }

    public static function tearDownAfterClass(): void
    {
        $builder = new FixtureBuilder();
        $builder->delete(Agent::class);
        $builder->delete(Absence::class);
    }

    public function testAbsenceRepositoryGet1(): void
    {
        $kernel = self::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $userId = $em->getRepository(Agent::class)->findOneByLogin('jdupont')->getId();

        $start = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 00:00:00');
        $end = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 23:59:59');

        $absences = $em->getRepository(Absence::class)->get($start, $end, true, $userId);

        $this->assertCount(5, $absences);

        $reasons = [];
        foreach($absences as $a) {
            $reasons[] = $a->getReason();
        }

        $this->assertContains('Absence 2', $reasons);
        $this->assertContains('Absence 5', $reasons);
        $this->assertContains('Absence 6', $reasons);
        $this->assertContains('Absence 7', $reasons);
        $this->assertContains('Absence 8', $reasons);
    }

    public function testAbsenceRepositoryGet2(): void
    {
        $kernel = self::bootKernel();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $userId = $em->getRepository(Agent::class)->findOneByLogin('jdupont')->getId();

        $start = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 00:00:00');
        $end = DateTime::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00');

        $absences = $em->getRepository(Absence::class)->get($start, $end);

        $this->assertCount(6, $absences);

        $reasons = [];
        foreach($absences as $a) {
            $reasons[] = $a->getReason();
        }

        $this->assertNotContains('Absence 3', $reasons);
        $this->assertNotContains('Absence 8', $reasons);
    }
}
