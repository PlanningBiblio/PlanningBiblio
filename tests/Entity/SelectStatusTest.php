<?php

namespace App\Tests\Entity;

use App\Entity\SelectStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SelectStatusTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testDefaultValues(): void
    {
        // Create a new entity instance
        $status = new SelectStatus();

        // ID should be null before persistence
        $this->assertNull($status->getId());

        // Default scalar values
        $this->assertSame('', $status->getValue());
        $this->assertSame(0, $status->getRank());
        $this->assertSame('', $status->getColor());
        $this->assertSame(0, $status->getCategory());
    }

    public function testSetAndGetValue(): void
    {
        $status = new SelectStatus();

        $result = $status->setValue('Librarian');

        // Ensure fluent interface
        $this->assertSame($status, $result);

        $this->assertSame('Librarian', $status->getValue());
    }

    public function testSetAndGetRank(): void
    {
        $status = new SelectStatus();

        $result = $status->setRank(10);

        $this->assertSame($status, $result);
        $this->assertSame(10, $status->getRank());
    }

    public function testSetAndGetColor(): void
    {
        $status = new SelectStatus();

        $result = $status->setColor('#FF0000');

        $this->assertSame($status, $result);
        $this->assertSame('#FF0000', $status->getColor());
    }

    public function testSetAndGetCategory(): void
    {
        $status = new SelectStatus();

        $result = $status->setCategory(2);

        $this->assertSame($status, $result);
        $this->assertSame(2, $status->getCategory());
    }

    public function testFullHydration(): void
    {
        $status = new SelectStatus();

        $status
            ->setValue('Intern')
            ->setRank(5)
            ->setColor('#00FF00')
            ->setCategory(1);

        $this->entityManager->persist($status);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $id = $status->getId();
        $status = $this->entityManager->find(SelectStatus::class, $id);

        $this->assertSame('Intern', $status->getValue());
        $this->assertSame(5, $status->getRank());
        $this->assertSame('#00FF00', $status->getColor());
        $this->assertSame(1, $status->getCategory());
    }
}
