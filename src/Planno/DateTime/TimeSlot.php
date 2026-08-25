<?php

namespace App\Planno\DateTime;

use DateTimeInterface;
use DateTimeImmutable;
use DateTime;

class TimeSlot
{
    public readonly DateTimeImmutable $start;
    public readonly DateTimeImmutable $end;

    public function __construct(DateTimeInterface $start, ?DateTimeInterface $end = null)
    {
        if ($end && $end < $start) {
            throw new \Exception(
                sprintf(
                    'Cannot create a TimeSlot with an end date prior to start date (start: %s, end: %s)',
                    $start->format(DateTime::RFC3339_EXTENDED),
                    $end->format(DateTime::RFC3339_EXTENDED)
                )
            );
        }

        $end ??= $start;

        $this->start = DateTimeImmutable::createFromInterface($start);
        $this->end = DateTimeImmutable::createFromInterface($end);
    }

    /**
     * Create a timeslot that represents full day(s), ie. the start date's time
     * is the beginning of the day and the end date's time is the end of the day
     *
     * @param DateTimeInterface $start Start date, the time component is ignored
     * @param DateTimeInterface $end End date, the time component is ignored.
     *                               Defaults to the end of $start's day
     */
    public static function createAllDay(DateTimeInterface $start, ?DateTimeInterface $end = null): self
    {
        return new self(
            DateTime::createFromInterface($start)->setTime(0, 0),
            DateTime::createFromInterface($end ?? $start)->setTime(0, 0)->modify('+1 day -1 microsecond'),
        );
    }

    /**
     * Create a time slot from datetime strings
     *
     * @see DateTime::createFromFormat
     */
    public static function createFromFormat(string $format, string $start, string $end): self
    {
        return new self(
            DateTime::createFromFormat($format, $start),
            DateTime::createFromFormat($format, $end),
        );
    }

    /**
     * Merge intersecting time slots and return an array of time slots that do
     * not intersect
     *
     * @param TimeSlot[] $timeSlots
     *
     * @return TimeSlot[]
     */
    public static function merge(array $timeSlots): array
    {
        $mergedTimeSlots = [];
        while (!empty($timeSlots)) {
            $initialTimeSlot = array_shift($timeSlots);
            $nextTimeSlots = [];
            $intersectingTimeSlot = null;

            // Find the first intersecting time slot
            foreach ($timeSlots as $timeSlot) {
                if (!$intersectingTimeSlot && (
                    $initialTimeSlot->intersectsWith($timeSlot->start, $timeSlot->end)
                    || $initialTimeSlot->start == $timeSlot->end
                    || $initialTimeSlot->end == $timeSlot->start
                    )
                ) {
                    $intersectingTimeSlot = $timeSlot;
                } else {
                    $nextTimeSlots[] = $timeSlot;
                }
            }

            // If found, merge it with the initial time slot, and put the
            // result in the list of time slots for the next iteration.
            // Else, it means the initial time slot does not intersect with any
            // other time slot, so put it in the result list
            if ($intersectingTimeSlot) {
                $mergedTimeSlot = new TimeSlot(
                    min($initialTimeSlot->start, $intersectingTimeSlot->start),
                    max($initialTimeSlot->end, $intersectingTimeSlot->end)
                );
                array_unshift($nextTimeSlots, $mergedTimeSlot);
            } else {
                $mergedTimeSlots[] = $initialTimeSlot;
            }

            $timeSlots = $nextTimeSlots;
        }

        return $mergedTimeSlots;
    }

    /**
     * Returns true if timeslot intersects with the given date range
     *
     * @param DateTimeInterface $start Start of date range
     * @param DateTimeInterface $end End of date range
     */
    public function intersectsWith(DateTimeInterface $start, DateTimeInterface $end): bool
    {
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return $start < $this->end && $end > $this->start;
    }

    /**
     * Returns true if timeslot includes the given date
     *
     * @param DateTimeInterface $date
     */
    public function includes(DateTimeInterface $date): bool
    {
        return $this->start <= $date && $date <= $this->end;
    }
}
