<?php

namespace App\Tests\Entity;

use App\Entity\WorkingHour;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WorkingHourTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testInitialState(): void
    {
        $entity = new WorkingHour();

        $this->assertNull($entity->getId(), 'ID should be null by default');
        $this->assertSame(0, $entity->getUser(), 'Default user should be 0');
        $this->assertNull($entity->getStart(), 'Start date should be null');
        $this->assertNull($entity->getEnd(), 'End date should be null');
        $this->assertSame([], $entity->getWorkingHours(), 'Working hours should be empty');
        $this->assertSame([], $entity->getBreaktime(), 'Breaktime should be empty');
        $this->assertNull($entity->getEntryDate(), 'Entry date should be null');
        $this->assertSame(0, $entity->getChange(), 'Change flag should be 0');
        $this->assertNull($entity->getChangeDate(), 'Change date should be null');
        $this->assertSame(0, $entity->getValidLevel1(), 'Level 1 validation should be 0');
        $this->assertSame(0, $entity->getValidLevel2(), 'Level 2 validation should be 0');
        $this->assertFalse($entity->isCurrent(), 'Current flag should be false');
        $this->assertSame(0, $entity->getReplace(), 'Replace should be 0');
        $this->assertSame(0, $entity->getException(), 'Exception should be 0');
        $this->assertSame(1, $entity->getNumberOfWeeks(), 'Default weeks should be 1');
        $this->assertNull($entity->getKey(), 'Key should be null');
    }

    public function testFluentSetters(): void
    {
        $entity = new WorkingHour();

        $result = $entity->setUser(10);

        $this->assertSame($entity, $result, 'All setters should be fluent');
    }

    /**
     * @dataProvider provideDates
     */
    public function testDateSetters(?\DateTime $start, ?\DateTime $end, ?\DateTime $changeDate): void
    {
        $entity = new WorkingHour();

        $entity->setStart($start);
        $entity->setEnd($end);
        $entity->setChangeDate($changeDate);

        $this->assertSame($start, $entity->getStart(), 'Start date should match');
        $this->assertSame($end, $entity->getEnd(), 'End date should match');
        $this->assertSame($changeDate, $entity->getChangeDate(), 'Change date should match');
    }

    /**
     * @dataProvider provideWorkingHours
     */
    public function testWorkingHoursAndBreaktime(?Array $workingHours, ?Array $breaktimes): void
    {
        $entity = new WorkingHour();

        $entity
            ->setWorkingHours($workingHours)
            ->setBreaktime($breaktimes);

        $this->assertSame($workingHours, $entity->getWorkingHours(), 'Working hours should match');
        $this->assertSame($breaktimes, $entity->getBreaktime(), 'Breaktime should match');
    }

    public function testEntryDateAutoSetWhenNull(): void
    {
        $entity = new WorkingHour();

        $entity->setEntryDate(null);

        $this->assertInstanceOf(\DateTime::class, $entity->getEntryDate(), 'Entry date should be auto-generated');
    }

    public function testEntryDateManualValue(): void
    {
        $entity = new WorkingHour();
        $date = new \DateTime('2024-02-01');

        $entity->setEntryDate($date);

        $this->assertSame($date, $entity->getEntryDate(), 'Entry date should match provided value');
    }

    public function testValidationWorkflow(): void
    {
        $entity = new WorkingHour();
        $date1 = new \DateTime('2024-03-01');
        $date2 = new \DateTime('2024-03-02');

        $entity
            ->setValidLevel1(1)
            ->setValidLevel1Date($date1)
            ->setValidLevel2(2)
            ->setValidLevel2Date($date2);

        $this->assertSame(1, $entity->getValidLevel1());
        $this->assertSame($date1, $entity->getValidLevel1Date());
        $this->assertSame(2, $entity->getValidLevel2());
        $this->assertSame($date2, $entity->getValidLevel2Date());
    }

    public function testFlagsAndCounters(): void
    {
        $entity = new WorkingHour();

        $entity
            ->setChange(1)
            ->setCurrent(true)
            ->setReplace(5)
            ->setException(3)
            ->setNumberOfWeeks(6);

        $this->assertSame(1, $entity->getChange());
        $this->assertTrue($entity->isCurrent());
        $this->assertSame(5, $entity->getReplace());
        $this->assertSame(3, $entity->getException());
        $this->assertSame(6, $entity->getNumberOfWeeks());
    }

    public function testUserAndKey(): void
    {
        $entity = new WorkingHour();

        $entity->setUser(42);
        $entity->setKey('planning-key');

        $this->assertSame(42, $entity->getUser());
        $this->assertSame('planning-key', $entity->getKey());
    }

    /**
     * @dataProvider provideWorkingHours
     */
    public function testPersistAndRetrieveWorkingHour(?Array $workingHours, ?Array $breaktimes): void
    {
        $workingHour = new WorkingHour();

        $start = new \DateTime('2024-01-01');
        $end = new \DateTime('2024-01-07');

        $workingHour
            ->setUser(123)
            ->setStart($start)
            ->setEnd($end)
            ->setWorkingHours($workingHours)
            ->setBreaktime($breaktimes)
            ->setEntryDate(new \DateTime('2024-01-01'))
            ->setChange(1)
            ->setValidLevel1(1)
            ->setValidLevel2(2)
            ->setCurrent(true)
            ->setReplace(5)
            ->setException(2)
            ->setNumberOfWeeks(3)
            ->setKey('test-key');

        $this->entityManager->persist($workingHour);
        $this->entityManager->flush();

        $id = $workingHour->getId();

        // Clear EntityManager to force reload from DB
        $this->entityManager->clear();

        $repository = $this->entityManager->getRepository(WorkingHour::class);

        $savedEntity = $repository->find($id);

        // Assert
        $this->assertNotNull($savedEntity, 'Entity should be found in database');
        $this->assertSame(123, $savedEntity->getUser(), 'User should be persisted');
        $this->assertSame('test-key', $savedEntity->getKey(), 'Key should be persisted');
        $this->assertSame(3, $savedEntity->getNumberOfWeeks(), 'Number of weeks should be persisted');
        $this->assertTrue($savedEntity->isCurrent(), 'Current flag should be persisted');

        $this->assertEquals($start, $savedEntity->getStart(), 'Start date should be persisted');
        $this->assertEquals($end, $savedEntity->getEnd(), 'End date should be persisted');

        $this->assertSame($workingHours, $savedEntity->getWorkingHours(), 'Working hours should be persisted');
        $this->assertSame($breaktimes, $savedEntity->getBreaktime(), 'Breaktime should be persisted');

        $this->entityManager->remove($savedEntity);
        $this->entityManager->flush();
    }

    public static function provideDates(): array
    {
        return [
            'dates set' => [new \DateTime('2024-01-01'), new \DateTime('2024-01-07'), new \DateTime('2024-01-14')],
            'null dates' => [null, null, null],
        ];
    }

    public static function provideWorkingHours(): array
    {
        return [
            '2 weeks' => [
                    [
                        0 => ['09:00:00','13:00:00','13:45:00','18:00:00','3'],
                        1 =>['09:00:00','13:00:00','13:45:00','18:00:00','3'],
                        2 => ['09:00:00','13:00:00','13:45:00','15:15:00','3'],
                        3 => ['09:00:00','13:00:00','13:45:00','18:00:00','3'],
                        4 => ['09:00:00','13:00:00','13:45:00','16:30:00','3'],
                        5 => ['','','','','3'],
                        7 => ['09:00:00','13:00:00','13:45:00','18:00:00','3'],
                        8 => ['09:00:00','13:00:00','13:45:00','18:00:00','3'],
                        9 => ['09:00:00','13:00:00','13:45:00','15:15:00','3'],
                        10 => ['09:00:00','13:00:00','13:45:00','18:00:00','3'],
                        11 => ['09:00:00','13:00:00','13:45:00','16:30:00','3'],
                        12 => ['','','','','3'],
                    ],
                    [2, 1.5, 1, 0, 0, 0, 2, 1.5, 1, 0, 0, 0],
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Close EntityManager properly to avoid memory leaks
        if ($this->entityManager !== null) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
}
