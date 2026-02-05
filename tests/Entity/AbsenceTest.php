<?php

namespace App\Tests\Entity;

use App\Entity\Absence;
use App\Entity\Agent;
use PHPUnit\Framework\TestCase;

class AbsenceTest extends TestCase
{
    public function testBasicGettersAndSetters(): void
    {
        $absence = new Absence();

        $start = new \DateTime('2026-02-01 09:00:00');
        $end = new \DateTime('2026-02-05 18:00:00');
        $requestDate = new \DateTime('2026-01-20');

        $absence
            ->setStart($start)
            ->setEnd($end)
            ->setReason('Congé')
            ->setOtherReason('Vacances')
            ->setComment('RAS')
            ->setStatus('EN_ATTENTE')
            ->setRequestDate($requestDate)
            ->setUid('uid-123')
            ->setRRule('FREQ=DAILY;COUNT=5')
            ->setGroup('A')
            ->setCalName('Absence calendrier')
            ->setICalKey('ical-key-456')
            ->setLastModified('20260201T090000Z')
            ->setOriginId(42)
            ->setValidLevel1(1)
            ->setValidLevel2(1)
            ->setAttachment1(10)
            ->setAttachment2(11)
            ->setAttachmentNA(0);

        $this->assertSame($start, $absence->getStart());
        $this->assertSame($end, $absence->getEnd());
        $this->assertSame('Congé', $absence->getReason());
        $this->assertSame('Vacances', $absence->getOtherReason());
        $this->assertSame('RAS', $absence->getComment());
        $this->assertSame('EN_ATTENTE', $absence->getStatus());
        $this->assertSame($requestDate, $absence->getRequestDate());
        $this->assertSame('uid-123', $absence->getUid());
        $this->assertSame('FREQ=DAILY;COUNT=5', $absence->getRRule());
        $this->assertSame('A', $absence->getGroup());
        $this->assertSame('Absence calendrier', $absence->getCalName());
        $this->assertSame('ical-key-456', $absence->getICalKey());
        $this->assertSame('20260201T090000Z', $absence->getLastModified());
        $this->assertSame(42, $absence->getOriginId());
        $this->assertSame(1, $absence->getValidLevel1());
        $this->assertSame(1, $absence->getValidLevel2());
        $this->assertSame(10, $absence->getAttachment1());
        $this->assertSame(11, $absence->getAttachment2());
        $this->assertSame(0, $absence->getAttachmentNA());
    }

    public function testUserRelation(): void
    {
        $absence = new Absence();
        $agent = new Agent();

        $agent
            ->setFirstname('John')
            ->setLastname('Doe');

        $absence->setUser($agent);

        $this->assertSame($agent, $absence->getUser());
        $this->assertSame('John', $absence->getUser()->getFirstname());
        $this->assertSame('Doe', $absence->getUser()->getLastname());
    }
}
