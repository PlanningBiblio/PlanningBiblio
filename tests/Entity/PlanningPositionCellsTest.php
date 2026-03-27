<?php

namespace App\Tests\Entity;

use App\Entity\PlanningPositionCells;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlanningPositionCellsTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entity = new PlanningPositionCells();

        $entity
            ->setNumber(10)
            ->setTable(2)
            ->setLine(3)
            ->setColumn(4);

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager
            ->getRepository(PlanningPositionCells::class)
            ->find($id);

        $this->assertSame(10, $entity->getNumber());
        $this->assertSame(2, $entity->getTable());
        $this->assertSame(3, $entity->getLine());
        $this->assertSame(4, $entity->getColumn());
    }
}
