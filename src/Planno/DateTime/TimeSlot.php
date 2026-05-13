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

        return $start <= $this->end && $end >= $this->start;
    }
}
