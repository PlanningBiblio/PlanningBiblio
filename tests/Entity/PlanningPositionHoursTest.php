<?php

namespace App\Tests\Entity;

use App\Entity\PlanningPositionHours;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlanningPositionHoursTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entity = new PlanningPositionHours();

        $start = new \DateTime('08:00:00');
        $end = new \DateTime('12:00:00');

        $entity
            ->setStart($start)
            ->setEnd($end)
            ->setTable(1)
            ->setNumber(5);

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager
            ->getRepository(PlanningPositionHours::class)
            ->find($id);

        $this->assertSame('08:00:00', $entity->getStart()->format('H:i:s'));
        $this->assertSame('12:00:00', $entity->getEnd()->format('H:i:s'));
        $this->assertSame(1, $entity->getTable());
        $this->assertSame(5, $entity->getNumber());
    }
}
