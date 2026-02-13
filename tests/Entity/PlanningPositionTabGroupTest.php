<?php

namespace App\Tests\Entity;

use App\Entity\PlanningPositionTabGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlanningPositionTabGroupTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entity = new PlanningPositionTabGroup();

        $deleteDate = new \DateTime('2026-02-15 09:00:00');

        $entity
            ->setName('Test Group')
            ->setMonday(1)
            ->setTuesday(2)
            ->setWednesday(3)
            ->setThursday(4)
            ->setFriday(5)
            ->setSaturday(6)
            ->setSunday(7)
            ->setSite(2)
            ->setDelete($deleteDate);

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager
            ->getRepository(PlanningPositionTabGroup::class)
            ->find($id);

        $this->assertSame('Test Group', $entity->getName());
        $this->assertSame(1, $entity->getMonday());
        $this->assertSame(2, $entity->getTuesday());
        $this->assertSame(3, $entity->getWednesday());
        $this->assertSame(4, $entity->getThursday());
        $this->assertSame(5, $entity->getFriday());
        $this->assertSame(6, $entity->getSaturday());
        $this->assertSame(7, $entity->getSunday());
        $this->assertSame(2, $entity->getSite());
        $this->assertSame(
            $deleteDate->format('Y-m-d H:i:s'),
            $entity->getDelete()?->format('Y-m-d H:i:s')
        );
    }
}
