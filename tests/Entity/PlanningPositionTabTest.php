<?php

namespace App\Tests\Entity;

use App\Entity\PlanningPositionTab;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlanningPositionTabTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entity = new PlanningPositionTab();

        $deleteDate = new \DateTime('2026-02-15 10:00:00');

        $entity
            ->setTable(5)
            ->setName('Test Tab')
            ->setSite(2)
            ->setDelete($deleteDate);

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager
            ->getRepository(PlanningPositionTab::class)
            ->find($id);

        $this->assertSame(5, $entity->getTable());
        $this->assertSame('Test Tab', $entity->getName());
        $this->assertSame(2, $entity->getSite());
        $this->assertSame(
            $deleteDate->format('Y-m-d H:i:s'),
            $entity->getDelete()?->format('Y-m-d H:i:s')
        );
    }
}
