<?php

namespace App\Tests\Entity;

use App\Entity\Detached;
use App\Repository\DetachedRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DetachedRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private ?DetachedRepository $detachedRepository = null;

    protected function setUp(): void
    {
        // Start the Symfony kernel to access the container and services
        self::bootKernel();

        // Get the EntityManager from the container
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Fetch the Repository via the EntityManager since it extends EntityRepository
        /** @var DetachedRepository $detachedRepository */
        $detachedRepository = $this->entityManager->getRepository(Detached::class);
        $this->detachedRepository = $detachedRepository;

        // Begin a transaction to isolate changes and avoid cluttering the database
        $this->entityManager->getConnection()->beginTransaction();
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
        $this->detachedRepository = null;
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // Boot the kernel statically to access the database connection
        self::bootKernel();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();

        // Get the exact table name mapped to the Detached entity
        $tableName = $entityManager->getClassMetadata(Detached::class)->getTableName();

        // Execute truncate query securely depending on the platform
        $platform = $connection->getDatabasePlatform();
        
        if (method_exists($platform, 'getTruncateTableSQL')) {
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0;');
            $connection->executeStatement($platform->getTruncateTableSQL($tableName));
            $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1;');
        } else {
            $connection->executeStatement("DELETE FROM {$tableName}");
        }

        $entityManager->close();
    }

    /**
     * Test entity getters, setters, and initial state.
     */
    public function testEntityGettersAndSetters(): void
    {
        $detached = new Detached();
        $date = new DateTimeImmutable('2026-10-05');
        $userId = 42;

        $this->assertNull($detached->getId());
        $this->assertNull($detached->getDate());
        $this->assertNull($detached->getUserId());

        $this->assertSame($detached, $detached->setDate($date));
        $this->assertSame($detached, $detached->setUserId($userId));

        $this->assertSame($date, $detached->getDate());
        $this->assertSame($userId, $detached->getUserId());
    }

    /**
     * Test full database persistence lifecycle.
     */
    public function testPersistence(): void
    {
        $detached = new Detached();
        $date = new DateTimeImmutable('2026-10-05');
        $userId = 99;

        $detached->setDate($date);
        $detached->setUserId($userId);

        $this->entityManager->persist($detached);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $generatedId = $detached->getId();
        $this->assertNotNull($generatedId);

        /** @var Detached|null $persistedDetached */
        $persistedDetached = $this->detachedRepository->find($generatedId);

        $this->assertNotNull($persistedDetached);
        $this->assertSame($userId, $persistedDetached->getUserId());
        $this->assertSame($date->format('Y-m-d'), $persistedDetached->getDate()?->format('Y-m-d'));
    }

    /**
     * Test findUserIds plucks the correct user IDs matching the target Monday.
     */
    public function testFindUserIdsFiltersByMondayThisWeek(): void
    {
        // 2026-10-07 is a Wednesday. The target Monday for this week is 2026-10-05.
        $targetMonday = new DateTimeImmutable('2026-10-05');
        $currentWednesday = new DateTimeImmutable('2026-10-07');
        $nextWeekDate = new DateTimeImmutable('2026-10-14');

        // Record 1: Matches the target Monday
        $detached1 = (new Detached())->setDate($targetMonday)->setUserId(101);
        $this->entityManager->persist($detached1);

        // Record 2: Matches the target Monday (different user)
        $detached2 = (new Detached())->setDate($targetMonday)->setUserId(102);
        $this->entityManager->persist($detached2);

        // Record 3: Out of scope (Next week)
        $detached3 = (new Detached())->setDate($nextWeekDate)->setUserId(203);
        $this->entityManager->persist($detached3);

        $this->entityManager->flush();
        $this->entityManager->clear();

        // Execute query providing the Wednesday date
        $userIds = $this->detachedRepository->findUserIds($currentWednesday);

        // Assertions
        $this->assertCount(2, $userIds, 'Should return exactly 2 user IDs.');
        $this->assertContains(101, $userIds);
        $this->assertContains(102, $userIds);
        $this->assertNotContains(203, $userIds, 'Should not contain users from other weeks.');
    }

    /**
     * Test findUserIds handles string format inputs correctly.
     */
    public function testFindUserIdsAcceptsStringInput(): void
    {
        $targetMonday = new DateTimeImmutable('2026-10-05');
        
        $detached = (new Detached())->setDate($targetMonday)->setUserId(500);
        $this->entityManager->persist($detached);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Pass a string instead of a DateTimeInterface object
        $userIds = $this->detachedRepository->findUserIds('2026-10-08');

        $this->assertCount(1, $userIds);
        $this->assertSame([500], $userIds);
    }
}