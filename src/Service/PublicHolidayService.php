<?php

namespace App\Service;

use App\Entity\ClosingDay;
use DateInterval;
use DateTime;

class PublicHolidayService
{
    public static function getFrenchHolidaysByDateRange(DateTime $start, DateTime $end): array
    {
        $date = $start;
        $days = [];

        while ($date < $end) {
            if ($name = self::getFrenchHolidays($date)) {
                $day = new ClosingDay();
                $day->setComment('Ajouté automatiquement')
                    ->setDate(clone $date)
                    ->setName($name)
                    ->setPublicHoliday(true);

                $days[] = $day;
            }
            $date->modify('+1 day');
        }

        return $days;
    }

    public static function getFrenchHolidays(DateTime $date): ?string
    {
        // Movable religious holidays
        $dateTime = new DateTime($date->format('Y') . '-03-21');
        $days = easter_days($date->format('Y'));
        $dateTime->add(new DateInterval("P{$days}D"));

        $pak = $dateTime->format('m-d');
        $lpa = $dateTime->modify('+1 day')->format('m-d');
        $asc = $dateTime->modify('+38 day')->format('m-d');
        $pen = $dateTime->modify('+10 day')->format('m-d');
        $lpe = $dateTime->modify('+1 day')->format('m-d');

        return match($date->format('m-d')) {
            // Fixed-date public holidays
            '01-01' => "Jour de l'an",
            '05-01' => 'Fête du travail',
            '05-08' => '8 mai 1945',
            '07-14' => 'Fête nationale',
            '08-15' => 'Assomption',
            '11-01' => 'La Toussaint',
            '11-11' => 'Armistice',
            '12-25' => 'Noël',
            // Movable religious holidays
            $pak => 'Pâques',
            $lpa => 'Lundi de Pâques',
            $asc => "Jeudi de l'Ascension",
            $pen => 'Pentecôte', 
            $lpe => 'Lundi Pentecôte',
            // No match found
            default => null,
        };
    }
}
