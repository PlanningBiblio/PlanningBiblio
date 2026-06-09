<?php

namespace App\Tests\Entity;

use App\Entity\WorkingHourCycle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WorkingHourCycleTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testInitialValues(): void
    {
        // Test default state after instantiation
        $entity = new WorkingHourCycle();

        $this->assertNull($entity->getId(), 'Id should be null by default');

        $this->assertInstanceOf(
            \DateTime::class,
            $entity->getDate(),
            'Date should be initialized in constructor'
        );

        $this->assertSame(
            0,
            $entity->getWeek(),
            'Week should default to 0'
        );
    }

    public function testSetAndGetDate(): void
    {
        // Test setting and retrieving a date
        $entity = new WorkingHourCycle();
        $date = new \DateTime('2024-01-15');

        $result = $entity->setDate($date);

        $this->assertSame($entity, $result, 'setDate should return the same instance (fluent interface)');
        $this->assertSame($date, $entity->getDate(), 'Date should match the one set');
    }

    public function testSetAndGetWeek(): void
    {
        // Test setting and retrieving week value
        $entity = new WorkingHourCycle();
        $week = 12;

        $result = $entity->setWeek($week);

        $this->assertSame($entity, $result, 'setWeek should return the same instance (fluent interface)');
        $this->assertSame($week, $entity->getWeek(), 'Week should match the one set');
    }

    public function testDateIsMutable(): void
    {
        // Test that DateTime mutability affects the entity
        $entity = new WorkingHourCycle();
        $date = new \DateTime('2024-01-15');

        $entity->setDate($date);
        $date->modify('+2 days');

        $this->assertEquals(
            new \DateTime('2024-01-17'),
            $entity->getDate(),
            'Date should reflect external mutation (mutable object)'
        );
    }

    public function testOverrideConstructorDate(): void
    {
        // Ensure constructor date can be overridden
        $entity = new WorkingHourCycle();

        $newDate = new \DateTime('2023-12-25');
        $entity->setDate($newDate);

        $this->assertSame(
            $newDate,
            $entity->getDate(),
            'Date should be overridden correctly'
        );
    }

    public function testChainedSetters(): void
    {
        // Test fluent interface with multiple setters
        $entity = new WorkingHourCycle();

        $date = new \DateTime('2024-06-01');
        $week = 22;

        $entity->setDate($date)
               ->setWeek($week);

        $this->assertSame($date, $entity->getDate(), 'Date should be correctly set');
        $this->assertSame($week, $entity->getWeek(), 'Week should be correctly set');
    }

    public function testPersist(): void
    {
        $entity = new WorkingHourCycle();

        $date = new \DateTime('2024-06-01');
        $week = 22;

        $entity->setDate($date)
               ->setWeek($week);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $id = $entity->getId();
        $entity = $this->entityManager->find(WorkingHourCycle::class, $id);

        $this->assertSame('2024-06-01', $entity->getDate()->format('Y-m-d'));
        $this->assertSame(22, $entity->getWeek());
    }
}