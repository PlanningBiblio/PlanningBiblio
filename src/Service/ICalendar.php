<?php

namespace App\Service;

use App\Planno\TimeSlot;
use DateTime;
use DateTimeInterface;
use ICal\ICal;

class ICalendar
{
    /**
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
            $timeSlots[] = new TimeSlot(
                DateTime::createFromFormat('Ymd\THis\Z', $event->dtstart),
                DateTime::createFromFormat('Ymd\THis\Z', $event->dtend)
            );
        }

        return $timeSlots;
    }
}
