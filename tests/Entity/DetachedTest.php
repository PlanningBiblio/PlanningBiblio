<?php

namespace App\Tests\Entity;

use App\Entity\Detached;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DetachedTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $detached = $this->entityManager->getRepository(Detached::class)->findAll();
        foreach ($detached as $d) {
            $this->entityManager->remove($d);
        }
        $this->entityManager->flush();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * Test entity getters, setters, and initial state.
     */
    public function testEntityGettersAndSetters(): void
    {
        $detached = new Detached();
        $date = new DateTimeImmutable('2026-10-05');
        $userId = 42;

        // Verify initial state is null
        $this->assertNull($detached->getId());
        $this->assertNull($detached->getDate());
        $this->assertEquals(0, $detached->getUserId());

        // Test mutators and fluid interface
        $this->assertSame($detached, $detached->setDate($date));
        $this->assertSame($detached, $detached->setUserId($userId));

        // Test accessors
        $this->assertSame($date, $detached->getDate());
        $this->assertSame($userId, $detached->getUserId());
    }

    /**
     * Test full database persistence lifecycle: persist, flush, and retrieve.
     */
    public function testPersistence(): void
    {
        $detached = new Detached();
        $date = new DateTimeImmutable('2026-10-05');
        $userId = 99;

        $detached->setDate($date);
        $detached->setUserId($userId);

        // Save the entity to the database
        $this->entityManager->persist($detached);
        $this->entityManager->flush();

        // Clear the entity manager to force a real database query on recovery
        $this->entityManager->clear();

        // Retrieve the entity from the database using its generated ID
        $generatedId = $detached->getId();
        $this->assertNotNull($generatedId, 'The ID should be generated after flush.');

        /** @var Detached|null $persistedDetached */
        $persistedDetached = $this->entityManager
            ->getRepository(Detached::class)
            ->find($generatedId);

        // Assertions to verify data integrity after persistence
        $this->assertNotNull($persistedDetached, 'The entity should be found in the database.');
        $this->assertSame($userId, $persistedDetached->getUserId(), 'The userId value should match.');
        
        // Compare formatted dates to avoid object reference inequality issues
        $this->assertSame(
            $date->format('Y-m-d'),
            $persistedDetached->getDate()?->format('Y-m-d'),
            'The date value should match.'
        );
    }
}
