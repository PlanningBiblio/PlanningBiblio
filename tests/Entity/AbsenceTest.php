<?php

namespace App\Tests\Entity;

use App\Entity\Absence;
use PHPUnit\Framework\TestCase;

class AbsenceTest extends TestCase
{
    private Absence $absence;

    protected function setUp(): void
    {
        $this->absence = new Absence();
    }

    public function testUserId(): void
    {
        $this->absence->setUserId(10);
        $this->assertEquals(10, $this->absence->getUserId());
    }

    public function testStartAndEndDates(): void
    {
        $start = new \DateTime('2025-01-01');
        $end = new \DateTime('2025-01-10');

        $this->absence->setStart($start);
        $this->absence->setEnd($end);

        $this->assertSame($start, $this->absence->getStart());
        $this->assertSame($end, $this->absence->getEnd());
    }

    public function testReason(): void
    {
        $this->absence->setReason('Maladie');
        $this->assertEquals('Maladie', $this->absence->getReason());
    }

    public function testOtherReason(): void
    {
        $this->absence->setOtherReason('Raison exceptionnelle');
        $this->assertEquals('Raison exceptionnelle', $this->absence->getOtherReason());
    }

    public function testComment(): void
    {
        $this->absence->setComment('Commentaire test');
        $this->assertEquals('Commentaire test', $this->absence->getComment());
    }

    public function testStatus(): void
    {
        $this->absence->setStatus('EN_ATTENTE');
        $this->assertEquals('EN_ATTENTE', $this->absence->getStatus());
    }

    public function testRequestDate(): void
    {
        $date = new \DateTime('2025-02-01');
        $this->absence->setRequestDate($date);

        $this->assertSame($date, $this->absence->getRequestDate());
    }

    public function testValidationLevels(): void
    {
        $dateN1 = new \DateTime('2025-02-02');
        $dateN2 = new \DateTime('2025-02-03');

        $this->absence->setValidLevel1(1);
        $this->absence->setValidLevel2(2);
        $this->absence->setValidLevel1Date($dateN1);
        $this->absence->setValidLevel2Date($dateN2);

        $this->assertEquals(1, $this->absence->getValidLevel1());
        $this->assertEquals(2, $this->absence->getValidLevel2());
        $this->assertSame($dateN1, $this->absence->getValidLevel1Date());
        $this->assertSame($dateN2, $this->absence->getValidLevel2Date());
    }

    public function testAttachments(): void
    {
        $this->absence->setAttachment1(100);
        $this->absence->setAttachment2(200);
        $this->absence->setAttachmentNA(300);

        $this->assertEquals(100, $this->absence->getAttachment1());
        $this->assertEquals(200, $this->absence->getAttachment2());
        $this->assertEquals(300, $this->absence->getAttachmentNA());
    }

    public function testCalendarFields(): void
    {
        $this->absence->setCalName('Calendar Test');
        $this->absence->setICalKey('ICAL123');
        $this->absence->setUid('UID456');
        $this->absence->setRRule('FREQ=DAILY');
        $this->absence->setLastModified('20250211T120000Z');

        $this->assertEquals('Calendar Test', $this->absence->getCalName());
        $this->assertEquals('ICAL123', $this->absence->getICalKey());
        $this->assertEquals('UID456', $this->absence->getUid());
        $this->assertEquals('FREQ=DAILY', $this->absence->getRRule());
        $this->assertEquals('20250211T120000Z', $this->absence->getLastModified());
    }

    public function testGroup(): void
    {
        $this->absence->setGroup('GROUPE_A');
        $this->assertEquals('GROUPE_A', $this->absence->getGroup());
    }

    public function testOriginId(): void
    {
        $this->absence->setOriginId(999);
        $this->assertEquals(999, $this->absence->getOriginId());
    }

    public function testIdIsNullByDefault(): void
    {
        $this->assertNull($this->absence->getId());
    }
}
