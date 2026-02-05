<?php

namespace App\Tests\Entity;

use App\Entity\OverTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OverTimeTest extends KernelTestCase
{
    public function testGettersAndSetters(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $overtime = new OverTime();

        $date1 = new \DateTime('2026-02-01');
        $date2 = new \DateTime('2026-02-02');
        $now = new \DateTime();

        $overtime
            ->setUser(10)
            ->setDate($date1)
            ->setDate2($date2)
            ->setHours(7.5)
            ->setStatus('pending')
            ->setComment('Récup exceptionnelle')
            ->setEntry(3)
            ->setEntryDate($now)
            ->setChange(4)
            ->setValidLevel1(5)
            ->setValidLevel1Date($now)
            ->setValidLevel2(6)
            ->setValidLevel2Date($now)
            ->setPreviousCredit(12.0)
            ->setActualCredit(19.5)
            ->setModification($now)
            ->setRefusal(null);
        
        $entityManager->persist($overtime);
        $entityManager->flush();
        $entityManager->clear();

        $id = $overtime->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $overtime = $entityManager->getRepository(OverTime::class)->find($id);

        $this->assertSame(10, $overtime->getUser());
        $this->assertSame($date1, $overtime->getDate());
        $this->assertSame($date2, $overtime->getDate2());
        $this->assertSame(7.5, $overtime->getHours());
        $this->assertSame('pending', $overtime->getStatus());
        $this->assertSame('Récup exceptionnelle', $overtime->getComment());
        $this->assertSame(3, $overtime->getEntry());
        $this->assertSame($now, $overtime->getEntryDate());
        $this->assertSame(4, $overtime->getChange());
        $this->assertSame(5, $overtime->getValidLevel1());
        $this->assertSame($now, $overtime->getValidLevel1Date());
        $this->assertSame(6, $overtime->getValidLevel2());
        $this->assertSame($now, $overtime->getValidLevel2Date());
        $this->assertSame(12.0, $overtime->getPreviousCredit());
        $this->assertSame(19.5, $overtime->getActualCredit());
        $this->assertSame($now, $overtime->getModification());
        $this->assertNull($overtime->getRefusal());
    }
}
