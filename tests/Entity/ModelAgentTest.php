<?php

namespace App\Tests\Entity;

use App\Entity\ModelAgent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ModelAgentTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entity = new ModelAgent();

        $start = new \DateTime('08:00');
        $end = new \DateTime('16:00');

        $entity
            ->setModelId(5)
            ->setUserId(12)
            ->setSkill(3)
            ->setDay('monday')
            ->setStart($start)
            ->setEnd($end)
            ->setTable('planning')
            ->setComment('Remplacement')
            ->setSite(2);

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager->getRepository(ModelAgent::class)->find($id);

        $this->assertSame(5, $entity->getModelId());
        $this->assertSame(12, $entity->getUserId());
        $this->assertSame(3, $entity->getSkill());
        $this->assertSame('monday', $entity->getDay());
        $this->assertSame($start, $entity->getStart());
        $this->assertSame($end, $entity->getEnd());
        $this->assertSame('planning', $entity->getTable());
        $this->assertSame('Remplacement', $entity->getComment());
        $this->assertSame(2, $entity->getSite());
    }
}
