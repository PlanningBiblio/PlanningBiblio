<?php

namespace App\Tests\Entity;

use App\Entity\PlanningPositionModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PlanningPositionModelTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entity = new PlanningPositionModel();

        $start = new \DateTime('08:00:00');
        $end = new \DateTime('16:00:00');

        $entity
            ->setModelId(1)
            ->setUser(10)
            ->setPosition(3)
            ->setComment('Test comment')
            ->setStart($start)
            ->setEnd($end)
            ->setTable('Planning')
            ->setDay('1')
            ->setSite(2);

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager
            ->getRepository(PlanningPositionModel::class)
            ->find($id);

        $this->assertSame(1, $entity->getModelId());
        $this->assertSame(10, $entity->getUser());
        $this->assertSame(3, $entity->getPosition());
        $this->assertSame('Test comment', $entity->getComment());
        $this->assertSame('08:00:00', $entity->getStart()->format('H:i:s'));
        $this->assertSame('16:00:00', $entity->getEnd()->format('H:i:s'));
        $this->assertSame('Planning', $entity->getTable());
        $this->assertSame('1', $entity->getDay());
        $this->assertSame(2, $entity->getSite());
    }
}
