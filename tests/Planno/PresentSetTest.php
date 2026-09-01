<?php

namespace App\Tests\Planno;

use App\Entity\Absence;
use App\Entity\Agent;
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

        // TODO: Do the same tests but with PlanningHebdo enabled
        $GLOBALS['config']['PlanningHebdo'] = 0;

        $container = static::getContainer();

        $presentSet = $container->get(PresentSet::class);
        $em = $container->get(EntityManagerInterface::class);

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
}
