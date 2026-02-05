<?php

namespace App\Tests\Entity;

use App\Entity\HiddenTables;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HiddenTablesTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entity = new HiddenTables();

        $entity
            ->setUserId(10)
            ->setTable(3)
            ->setHiddenTables('planning,stats,export');

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager->getRepository(HiddenTables::class)->find($id);

        $this->assertSame(10, $entity->getUserId());
        $this->assertSame(3, $entity->getTable());
        $this->assertSame('planning,stats,export', $entity->getHiddenTables());
    }
}
