<?php

namespace App\Tests\Service;

use App\Planno\DateTime\TimeSlot;
use App\Service\ICalendar;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ICalendarTest extends KernelTestCase
{
    public function testGetRecurringEventDates(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $ical = $container->get(ICalendar::class);

        $initialTimeSlot = TimeSlot::createFromFormat('Y-m-d H:i', '2026-04-07 10:30', '2026-04-07 12:00');

        // Daily
        $timeSlots = $ical->getRecurringEventTimeSlots($initialTimeSlot, 'FREQ=DAILY;COUNT=4');
        foreach ($timeSlots as $timeSlot) {
            $this->assertInstanceOf(TimeSlot::class, $timeSlot);
        }
        $this->assertCount(4, $timeSlots);
        $this->assertEquals('2026-04-07 10:30', $timeSlots[0]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-07 12:00', $timeSlots[0]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-08 10:30', $timeSlots[1]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-08 12:00', $timeSlots[1]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-09 10:30', $timeSlots[2]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-09 12:00', $timeSlots[2]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-10 10:30', $timeSlots[3]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-10 12:00', $timeSlots[3]->end->format('Y-m-d H:i'));

        // Weekly
        $timeSlots = $ical->getRecurringEventTimeSlots($initialTimeSlot, 'FREQ=WEEKLY;COUNT=4');
        foreach ($timeSlots as $timeSlot) {
            $this->assertInstanceOf(TimeSlot::class, $timeSlot);
        }
        $this->assertCount(4, $timeSlots);
        $this->assertEquals('2026-04-07 10:30', $timeSlots[0]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-07 12:00', $timeSlots[0]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-14 10:30', $timeSlots[1]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-14 12:00', $timeSlots[1]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-21 10:30', $timeSlots[2]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-21 12:00', $timeSlots[2]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-28 10:30', $timeSlots[3]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-28 12:00', $timeSlots[3]->end->format('Y-m-d H:i'));

        // Monthly
        $timeSlots = $ical->getRecurringEventTimeSlots($initialTimeSlot, 'FREQ=MONTHLY;COUNT=4');
        foreach ($timeSlots as $timeSlot) {
            $this->assertInstanceOf(TimeSlot::class, $timeSlot);
        }
        $this->assertCount(4, $timeSlots);
        $this->assertEquals('2026-04-07 10:30', $timeSlots[0]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-07 12:00', $timeSlots[0]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-05-07 10:30', $timeSlots[1]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-05-07 12:00', $timeSlots[1]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-06-07 10:30', $timeSlots[2]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-06-07 12:00', $timeSlots[2]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-07-07 10:30', $timeSlots[3]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-07-07 12:00', $timeSlots[3]->end->format('Y-m-d H:i'));

        // Every Tuesday and Thursday until 2026-05-01
        $timeSlots = $ical->getRecurringEventTimeSlots($initialTimeSlot, 'FREQ=WEEKLY;BYDAY=TU,TH;UNTIL=20260501T000000Z');
        foreach ($timeSlots as $timeSlot) {
            $this->assertInstanceOf(TimeSlot::class, $timeSlot);
        }
        $this->assertCount(8, $timeSlots);
        $this->assertEquals('2026-04-07 10:30', $timeSlots[0]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-07 12:00', $timeSlots[0]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-09 10:30', $timeSlots[1]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-09 12:00', $timeSlots[1]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-14 10:30', $timeSlots[2]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-14 12:00', $timeSlots[2]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-16 10:30', $timeSlots[3]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-16 12:00', $timeSlots[3]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-21 10:30', $timeSlots[4]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-21 12:00', $timeSlots[4]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-23 10:30', $timeSlots[5]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-23 12:00', $timeSlots[5]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-28 10:30', $timeSlots[6]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-28 12:00', $timeSlots[6]->end->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-30 10:30', $timeSlots[7]->start->format('Y-m-d H:i'));
        $this->assertEquals('2026-04-30 12:00', $timeSlots[7]->end->format('Y-m-d H:i'));
    }
}
