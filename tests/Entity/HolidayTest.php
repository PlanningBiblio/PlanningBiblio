<?php

namespace App\Tests\Entity;

use App\Entity\Holiday;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class HolidayTest extends KernelTestCase
{
    public function testHolidayEntity(): void
    {
        $kernel = self::bootKernel();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $start = new \DateTime(date('Y-m-d') . ' 00:00:00');
        $end = new \DateTime(date('Y-m-d') . ' 23:59:59');
        $changeDate = new \DateTime(date('Y-m-d') . ' 15:12:00');
        $deleteDate = new \DateTime(date('Y-m-d') . ' 14:29:00');
        $entryDate = new \DateTime(date('Y-m-d') . ' 13:30:00');
        $infoDate = new \DateTime(date('Y-m-d') . ' 16:23:00');
        $validationLevel1Date = new \DateTime(date('Y-m-d') . ' 12:00:00');
        $validationLevel2Date = new \DateTime(date('Y-m-d') . ' 12:30:00');

        $holiday = new Holiday();
        $holiday->setUser(9);
        $holiday->setStart($start);
        $holiday->setEnd($end);
        $holiday->setValidLevel1(12);
        $holiday->setValidLevel1Date($validationLevel1Date);
        $holiday->setValidLevel2(13);
        $holiday->setValidLevel2Date($validationLevel2Date);
        $holiday->setEntry(9);
        $holiday->setEntryDate($entryDate);
        $holiday->setChange(10);
        $holiday->setChangeDate($changeDate);
        $holiday->setDelete(13);
        $holiday->setDeleteDate($deleteDate);
        $holiday->setInfo(14);
        $holiday->setInfoDate($infoDate);
        $holiday->setHalfDay(1);
        $holiday->setHalfDayStart('Halfday start text');
        $holiday->setHalfDayEnd('Halfday end text');
        $holiday->setDebit('debit text');
        $holiday->setHours('hours text');
        $holiday->setPreviousCredit(1.5);
        $holiday->setActualCredit(2.3);
        $holiday->setPreviousCompTime(3.1);
        $holiday->setActualCompTime(6.2);
        $holiday->setPreviousRemainder(7.6);
        $holiday->setActualRemainder(8.3);
        $holiday->setPreviousAnticipation(5.5);
        $holiday->setActualAnticipation(1.2);
        $holiday->setComment('comment text');
        $holiday->setRefusal('refus text');
        $holiday->setRegulationId(125);
        $holiday->setOriginId(122);

        $entityManager->persist($holiday);
        $entityManager->flush();
        $entityManager->clear();

        $id = $holiday->getId();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $holiday = $entityManager->getRepository(Holiday::class)->find($id);

        $this->assertGreaterThan(0, $holiday->getId());
        $this->assertEquals(9, $holiday->getUser());
        $this->assertEquals($start, $holiday->getStart());
        $this->assertEquals($end, $holiday->getEnd());
        $this->assertEquals(12, $holiday->getValidLevel1());
        $this->assertEquals($validationLevel1Date, $holiday->getValidLevel1Date());
        $this->assertEquals(13, $holiday->getValidLevel2());
        $this->assertEquals($validationLevel2Date, $holiday->getValidLevel2Date());
        $this->assertEquals(9, $holiday->getEntry());
        $this->assertEquals($entryDate, $holiday->getEntryDate());
        $this->assertEquals(10, $holiday->getChange());
        $this->assertEquals($changeDate, $holiday->getChangeDate());
        $this->assertEquals(13, $holiday->getDelete());
        $this->assertEquals($deleteDate, $holiday->getDeleteDate());
        $this->assertEquals(14, $holiday->getInfo());
        $this->assertEquals($infoDate, $holiday->getInfoDate());
        $this->assertEquals(1, $holiday->getHalfDay());
        $this->assertEquals('Halfday start text', $holiday->getHalfDayStart());
        $this->assertEquals('Halfday end text', $holiday->getHalfDayEnd());
        $this->assertEquals('debit text', $holiday->getDebit());
        $this->assertEquals('hours text', $holiday->getHours());
        $this->assertEquals(1.5, $holiday->getPreviousCredit());
        $this->assertEquals(2.3, $holiday->getActualCredit());
        $this->assertEquals(3.1, $holiday->getPreviousCompTime());
        $this->assertEquals(6.2, $holiday->getActualCompTime());
        $this->assertEquals(7.6, $holiday->getPreviousRemainder());
        $this->assertEquals(8.3, $holiday->getActualRemainder());
        $this->assertEquals(5.5, $holiday->getPreviousAnticipation());
        $this->assertEquals(1.2, $holiday->getActualAnticipation());
        $this->assertEquals('comment text', $holiday->getComment());
        $this->assertEquals('refus text', $holiday->getRefusal());
        $this->assertEquals(125, $holiday->getRegulationId());
        $this->assertEquals(122, $holiday->getOriginId());
    }
}
