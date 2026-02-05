<?php

namespace App\Tests\Entity;

use App\Entity\HolidayInfo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HolidayInfoTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $holiday = new HolidayInfo();

        $start = new \DateTime('2026-07-01');
        $end = new \DateTime('2026-07-15');
        $entryDate = new \DateTime('2026-02-10 09:00');

        $holiday
            ->setStart($start)
            ->setEnd($end)
            ->setComment('Congés estivaux')
            ->setEntryDate($entryDate);

        $entityManager->persist($holiday);
        $entityManager->flush();
        $entityManager->clear();

        $id = $holiday->getId();
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $holiday = $entityManager->getRepository(HolidayInfo::class)->find($id);

        $this->assertSame($start, $holiday->getStart());
        $this->assertSame($end, $holiday->getEnd());
        $this->assertSame('Congés estivaux', $holiday->getComment());
        $this->assertSame($entryDate, $holiday->getEntryDate());
    }
}
