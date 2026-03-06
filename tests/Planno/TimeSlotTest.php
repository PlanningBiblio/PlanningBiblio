<?php

namespace App\Tests\Planno;

use App\Planno\TimeSlot;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class TimeSlotTest extends TestCase
{
    public function testCreateAllDay(): void
    {
        $timeSlot = TimeSlot::createAllDay(new DateTime('2026-03-04 10:30'));
        $this->assertEquals('2026-03-04T00:00:00.000+00:00', $timeSlot->start->format(DateTimeInterface::RFC3339_EXTENDED));
        $this->assertEquals('2026-03-04T23:59:59.999+00:00', $timeSlot->end->format(DateTimeInterface::RFC3339_EXTENDED));

        $timeSlot = TimeSlot::createAllDay(new DateTime('2026-03-04 10:30'), new DateTime('2026-03-07 10:30'));
        $this->assertEquals('2026-03-04T00:00:00.000+00:00', $timeSlot->start->format(DateTimeInterface::RFC3339_EXTENDED));
        $this->assertEquals('2026-03-07T23:59:59.999+00:00', $timeSlot->end->format(DateTimeInterface::RFC3339_EXTENDED));
    }

    public function testIntersectsWith(): void
    {
        $timeSlot = TimeSlot::createAllDay(new DateTime('2026-03-04'), new DateTime('2026-03-07'));

        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-01'), new DateTime('2026-03-10')),
            'start is before, end is after'
        );
        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-01'), new DateTime('2026-03-07')),
            'end is inside'
        );
        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-04'), new DateTime('2026-03-10')),
            'start is inside'
        );
        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-04'), new DateTime('2026-03-07')),
            'start and end are inside'
        );
        $this->assertFalse(
            $timeSlot->intersectsWith(new DateTime('2026-04-01'), new DateTime('2026-04-02')),
            'start and end are after'
        );

        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-04 00:00:00'), new DateTime('2026-03-07 23:59:59')),
            'start and end are inside'
        );

        $timeSlot = new TimeSlot(new DateTime('2026-03-04 10:30'), new DateTime('2026-03-04 12:00'));

        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-04 10:00'), new DateTime('2026-03-04 14:00')),
            'start is before, end is after'
        );
        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-04 10:30'), new DateTime('2026-03-04 14:00')),
            'start is inside, end is after'
        );
        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-04 10:00'), new DateTime('2026-03-04 11:00')),
            'start is before, end is inside'
        );
        $this->assertTrue(
            $timeSlot->intersectsWith(new DateTime('2026-03-04 11:00'), new DateTime('2026-03-04 11:30')),
            'start is inside, end is inside'
        );
        $this->assertFalse(
            $timeSlot->intersectsWith(new DateTime('2026-03-04 12:00:00.000001'), new DateTime('2026-03-04 12:30')),
            'start is after, end is after'
        );
    }
}
