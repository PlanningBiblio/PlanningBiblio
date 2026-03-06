<?php

namespace App\Planno;

use DateTimeInterface;
use DateTimeImmutable;
use DateTime;

class TimeSlot
{
    public readonly DateTimeImmutable $start;
    public readonly DateTimeImmutable $end;

    public function __construct(DateTimeInterface $start, ?DateTimeInterface $end = null)
    {
        $this->start = DateTimeImmutable::createFromInterface($start);
        $this->end = DateTimeImmutable::createFromInterface($end ?? $start);
    }

    /**
     * Create a timeslot that represents full day(s), ie. the start date's time
     * is the beginning of the day and the end date's time is the end of the day
     *
     * @param DateTimeInterface $start Start date, the time component is ignored
     * @param DateTimeInterface $end End date, the time component is ignored.
     *                               Defaults to the end of $start's day
     * @return self
     */
    public static function createAllDay(DateTimeInterface $start, ?DateTimeInterface $end = null): self
    {
        return new self(
            DateTime::createFromInterface($start)->setTime(0, 0),
            DateTime::createFromInterface($end ?? $start)->modify('+1 day 00:00 -1 microsecond'),
        );
    }

    /**
     * Returns true if timeslot intersects with the given date range
     *
     * @param DateTimeInterface $start Start of date range
     * @param DateTimeInterface $end End of date range
     *
     * @return bool
     */
    public function intersectsWith(DateTimeInterface $start, DateTimeInterface $end): bool
    {
        return $start >= $this->start && $start <= $this->end
            || $end >= $this->start && $end <= $this->end
            || $start < $this->start && $end > $this->end;
    }

    /**
     * Returns true if given $date is within the timeslot
     *
     * @param DateTimeInterface $date
     *
     * @return bool
     */
    public function contains(DateTimeInterface $date): bool
    {
        return $date >= $this->start && $date <= $this->end;
    }
}
