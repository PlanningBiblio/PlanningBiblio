<?php

namespace App\Tests\Entity;

use App\Entity\HiddenTables;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HiddenTablesTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testInitialState(): void
    {
        $entity = new HiddenTables();

        $this->assertNull($entity->getId(), 'Initial ID should be null');
        $this->assertSame([], $entity->getHiddenTables(), 'Hidden tables should be empty by default');
        $this->assertSame(0, $entity->getTableId(), 'Default table ID should be 0');
        $this->assertSame(0, $entity->getUserId(), 'Default user ID should be 0');
    }

    public function testSetAndGetHiddenTables(): void
    {
        $entity = new HiddenTables();
        $tables = ['userIds', 'orders', 'products'];

        $result = $entity->setHiddenTables($tables);

        $this->assertSame($entity, $result, 'Setter should return the same instance (fluent interface)');
        $this->assertSame($tables, $entity->getHiddenTables(), 'Hidden tables should match the given array');
    }

    public function testSetHiddenTablesOverwrite(): void
    {
        $entity = new HiddenTables();

        $entity->setHiddenTables(['userIds']);
        $entity->setHiddenTables(['logs', 'sessions']);

        $this->assertSame(['logs', 'sessions'], $entity->getHiddenTables(), 'Hidden tables should be overwritten');
    }

    public function testSetAndGetTableId(): void
    {
        $entity = new HiddenTables();

        $result = $entity->setTableId(42);

        $this->assertSame($entity, $result, 'Setter should be fluent');
        $this->assertSame(42, $entity->getTableId(), 'Table ID should match the given value');
    }

    public function testSetAndGetUserId(): void
    {
        $entity = new HiddenTables();

        $result = $entity->setUserId(1001);

        $this->assertSame($entity, $result, 'Setter should be fluent');
        $this->assertSame(1001, $entity->getUserId(), 'User ID should match the given value');
    }

    public function testMultiplePropertyAssignments(): void
    {
        $entity = new HiddenTables();

        $entity
            ->setUserId(123)
            ->setTableId(99)
            ->setHiddenTables(['customers', 'invoices']);

        $this->assertSame(123, $entity->getUserId(), 'User ID should be correctly assigned');
        $this->assertSame(99, $entity->getTableId(), 'Table ID should be correctly assigned');
        $this->assertSame(['customers', 'invoices'], $entity->getHiddenTables(), 'Hidden tables should match assigned values');
    }

    public function testPersistWithDefaultValues(): void
    {
        $entity = new HiddenTables();

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $id = $entity->getId();
        $entity = $this->entityManager->find(HiddenTables::class, $id);

        $this->assertSame([], $entity->getHiddenTables(), 'Hidden tables default should be an empty array');
        $this->assertSame(0, $entity->getTableId(), 'Table default value should be 0');
        $this->assertSame(0, $entity->getUserId(), 'UserId default should be 0');

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function testPersistWithDefineValues(): void
    {
        $entity = new HiddenTables();

        $entity->setHiddenTables([1,3]);
        $entity->setTableId(3);
        $entity->setUserId(9);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $id = $entity->getId();
        $entity = $this->entityManager->find(HiddenTables::class, $id);

        $this->assertSame([1,3], $entity->getHiddenTables(), 'Hidden tables should be [1,3]');
        $this->assertSame(3, $entity->getTableId(), 'Table value should be 3');
        $this->assertSame(9, $entity->getUserId(), 'UserId should be 9');

        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }
}
