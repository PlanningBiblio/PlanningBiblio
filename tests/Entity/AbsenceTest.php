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

    public function testDefaultValues(): void
    {
        $this->assertNull($this->absence->getId());
        $this->assertFalse($this->absence->isAttachment1());
        $this->assertFalse($this->absence->isAttachment2());
        $this->assertFalse($this->absence->isAttachmentNA());
    }

    public function testUserId(): void
    {
        $this->absence->setUserId(10);
        $this->assertSame(10, $this->absence->getUserId());
    }

    public function testDates(): void
    {
        $start = new \DateTime('2025-01-01 09:00:00');
        $end = new \DateTime('2025-01-10 17:00:00');
        $request = new \DateTime('2024-12-01 16:33:00');
        $valid1 = new \DateTime('2024-12-05 13:25:00');
        $valid2 = new \DateTime('2024-12-06 15:42:00');

        $this->absence
            ->setStart($start)
            ->setEnd($end)
            ->setRequestDate($request)
            ->setValidLevel1Date($valid1)
            ->setValidLevel2Date($valid2);

        $this->assertSame($start, $this->absence->getStart());
        $this->assertSame($end, $this->absence->getEnd());
        $this->assertSame($request, $this->absence->getRequestDate());
        $this->assertSame($valid1, $this->absence->getValidLevel1Date());
        $this->assertSame($valid2, $this->absence->getValidLevel2Date());
    }

    public function testStrings(): void
    {
        $this->absence
            ->setReason('Sickness absence')
            ->setOtherReason('Hospitalization')
            ->setComment('Test comment')
            ->setGroup('1563458069-541')
            ->setCalName('My Personnal Calendar')
            ->setICalKey('20190304T070000_20190213T183126Z_20280110T070000_20190213T183126Z')
            ->setUid('20190304T070000_20190213T183126Z')
            ->setRRule('FREQ=WEEKLY;WKST=MO;BYDAY=MO')
            ->setLastModified('20250211T120000Z');

        $this->assertSame('Sickness absence', $this->absence->getReason());
        $this->assertSame('Hospitalization', $this->absence->getOtherReason());
        $this->assertSame('Test comment', $this->absence->getComment());
        $this->assertSame('1563458069-541', $this->absence->getGroup());
        $this->assertSame('My Personnal Calendar', $this->absence->getCalName());
        $this->assertSame('20190304T070000_20190213T183126Z_20280110T070000_20190213T183126Z', $this->absence->getICalKey());
        $this->assertSame('20190304T070000_20190213T183126Z', $this->absence->getUid());
        $this->assertSame('FREQ=WEEKLY;WKST=MO;BYDAY=MO', $this->absence->getRRule());
        $this->assertSame('20250211T120000Z', $this->absence->getLastModified());
    }

    public function testValidationLevels(): void
    {
        $this->absence
            ->setValidLevel1(-10)
            ->setValidLevel2(2);

        $this->assertSame(-10, $this->absence->getValidLevel1());
        $this->assertSame(2, $this->absence->getValidLevel2());
    }

    public function testOriginId(): void
    {
        $this->absence->setOriginId(99);
        $this->assertSame(99, $this->absence->getOriginId());
    }

    public function testAttachments(): void
    {
        $this->absence
            ->setAttachment1(true)
            ->setAttachment2(true)
            ->setAttachmentNA(true);

        $this->assertTrue($this->absence->isAttachment1());
        $this->assertTrue($this->absence->isAttachment2());
        $this->assertTrue($this->absence->isAttachmentNA());
    }

    public function testFluentInterface(): void
    {
        $result = $this->absence->setReason('Test');
        $this->assertInstanceOf(Absence::class, $result);
    }
}
