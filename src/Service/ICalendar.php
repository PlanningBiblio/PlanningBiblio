<?php

namespace App\Service;

use App\Planno\DateTime\TimeSlot;
use DateTime;
use DateTimeInterface;
use ICal\ICal;

class ICalendar
{
    /**
     * Given an initial time slot and a recurrence rule (rrule), this returns a
     * list of all corresponding future time slots. The first element of the
     * list is the initial time slot.
     *
     * If rrule does not have an explicit end (set with "until" or "count"),
     * only time slots within the 2 next years or returned (see defaultSpan
     * option of ICal).
     *
     * @return TimeSlot[]
     */
    public function getRecurringEventTimeSlots(TimeSlot $initialTimeSlot, string $rrule): array
    {
        $ical = new ICal();

        $ics = sprintf(
            <<<'EOF'
            BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:Planno
            BEGIN:VEVENT
            UID:%s
            DTSTAMP:%s
            DTSTART:%s
            DTEND:%s
            RRULE:%s
            END:VEVENT
            END:VCALENDAR
            EOF,
            random_int(0, PHP_INT_MAX),
            (new DateTime)->format('Ymd\THis\Z'),
            $initialTimeSlot->start->format('Ymd\THis\Z'),
            $initialTimeSlot->end->format('Ymd\THis\Z'),
            $rrule
        );

        $ical->initString($ics);

        $timeSlots = [];
        foreach ($ical->events() as $event) {
            $timeSlots[] = TimeSlot::createFromFormat('Ymd\THis\Z', $event->dtstart, $event->dtend);
        }

        return $timeSlots;
    }
}
