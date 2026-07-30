<?php

namespace App\Tests\Planno;

use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\WorkingHour;
use App\Planno\DateTime\TimeSlot;
use App\Planno\PresentSet;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\FixtureBuilder;

class PresentSetTest extends KernelTestCase
{
    public function testAll(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $em = $container->get(EntityManagerInterface::class);

        $configRepository = $em->getRepository('App\Entity\Config');
        $configRepository->setParam('PlanningHebdo', 0);

        $presentSet = $container->get(PresentSet::class);

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, ['actif' => 'Actif']);

        $presents = $presentSet->all('2026-07-31');
        $this->assertCount(1, $presents);
        $this->assertEquals(
            [
                'id' => $agent->getId(),
                'nom' => sprintf('%s %s', $agent->getLastname(), $agent->getFirstname()),
                'site' => null,
                'heures' => '09h00 - 13h00 & 14h00 - 17h00',
            ],
            $presents[0]
        );

        $absence = $builder->build(
            Absence::class,
            [
                'perso_id' => $agent->getId(),
                'debut' => new DateTime('2026-07-31 14:00'),
                'fin' => new DateTime('2026-07-31 17:00'),
                'valide' => 1,
                'groupe' => '',
            ],
        );

        $presents = $presentSet->all('2026-07-31');
        $this->assertCount(1, $presents);
        $this->assertEquals(
            [
                'id' => $agent->getId(),
                'nom' => sprintf('%s %s', $agent->getLastname(), $agent->getFirstname()),
                'site' => null,
                'heures' => '09h00 - 13h00',
            ],
            $presents[0]
        );

        $absence = $builder->build(
            Absence::class,
            [
                'perso_id' => $agent->getId(),
                'debut' => new DateTime('2026-07-31 09:00'),
                'fin' => new DateTime('2026-07-31 13:00'),
                'valide' => 1,
                'groupe' => '',
            ],
        );

        $presents = $presentSet->all('2026-07-31');
        $this->assertCount(0, $presents);
    }

    public function testAllWithPlanningHebdo(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $em = $container->get(EntityManagerInterface::class);

        $configRepository = $em->getRepository('App\Entity\Config');
        $configRepository->setParam('PlanningHebdo', 1);

        $presentSet = $container->get(PresentSet::class);

        $builder = new FixtureBuilder();
        $agent = $builder->build(Agent::class, ['actif' => 'Actif']);

        $workingHour = new WorkingHour();
        $workingHour->setUser($agent->getId());
        $workingHour->setStart(new DateTime('2026-01-01'));
        $workingHour->setEnd(new DateTime('2026-12-31'));
        $workingHour->setValidLevel2(1);
        $workingHour->setCurrent(true);
        $workingHour->setWorkingHours([
            ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
            ['09:00:00', '13:00:00', '14:00:00', '17:00:00', '1'],
        ]);
        $em->persist($workingHour);
        $em->flush();

        $presents = $presentSet->all('2026-07-31');
        $this->assertCount(1, $presents);
        $this->assertEquals(
            [
                'id' => $agent->getId(),
                'nom' => sprintf('%s %s', $agent->getLastname(), $agent->getFirstname()),
                'site' => null,
                'heures' => '09h00 - 13h00 & 14h00 - 17h00',
            ],
            $presents[0]
        );

        $absence = $builder->build(
            Absence::class,
            [
                'perso_id' => $agent->getId(),
                'debut' => new DateTime('2026-07-31 14:00'),
                'fin' => new DateTime('2026-07-31 17:00'),
                'valide' => 1,
                'groupe' => '',
            ],
        );

        $presents = $presentSet->all('2026-07-31');
        $this->assertCount(1, $presents);
        $this->assertEquals(
            [
                'id' => $agent->getId(),
                'nom' => sprintf('%s %s', $agent->getLastname(), $agent->getFirstname()),
                'site' => null,
                'heures' => '09h00 - 13h00',
            ],
            $presents[0]
        );

        $absence = $builder->build(
            Absence::class,
            [
                'perso_id' => $agent->getId(),
                'debut' => new DateTime('2026-07-31 09:00'),
                'fin' => new DateTime('2026-07-31 13:00'),
                'valide' => 1,
                'groupe' => '',
            ],
        );

        $presents = $presentSet->all('2026-07-31');
        $this->assertCount(0, $presents);
    }
}
