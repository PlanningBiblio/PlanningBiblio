<?php

namespace App\Tests\Entity;

use App\Entity\PublicHoliday;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PublicHolidayTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entity = new PublicHoliday();

        $date = new \DateTime('2026-05-01');

        $entity
            ->setYear('2025-2026')
            ->setDate($date)
            ->setDayOff(1)
            ->setDayClosed(0)
            ->setName('Day off')
            ->setComment('Public holiday in France');

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->clear();

        $id = $entity->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $entity = $entityManager
            ->getRepository(PublicHoliday::class)
            ->find($id);

        $this->assertSame('2025-2026', $entity->getYear());
        $this->assertSame('2026-05-01', $entity->getDate()->format('Y-m-d'));
        $this->assertSame(1, $entity->getDayOff());
        $this->assertSame(0, $entity->getDayClosed());
        $this->assertSame('Day off', $entity->getName());
        $this->assertSame('Public holiday in France', $entity->getComment());
    }
}
