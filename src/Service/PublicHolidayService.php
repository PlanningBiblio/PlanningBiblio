<?php

namespace App\Service;

use App\Entity\PublicHoliday;
use DateInterval;
use DateTime;

class PublicHolidayService
{
    public static function getFrenchHolidaysByDateRange(DateTime $start, DateTime $end): array
    {
        $date = $start;
        $days = [];

        while ($date < $end) {
            if ($name = self::getFrenchHolidays($date->format('Y-m-d'))) {
                $day = new PublicHoliday();
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

    public static function getFrenchHolidays(String $date): ?string
    {
        $tmp=explode("-", $date);
        $jour = $tmp[2];
        $mois = $tmp[1];
        $annee = $tmp[0];

        // dates fériées fixes
        if ($jour == 1 && $mois == 1) {
            return "Jour de l'an";
        }
        if ($jour == 1 && $mois == 5) {
            return "Fête du travail";
        }
        if ($jour == 8 && $mois == 5) {
            return "8 mai 1945";
        }
        if ($jour == 14 && $mois == 7) {
            return "Fête nationale";
        }
        if ($jour == 15 && $mois == 8) {
            return "Assomption";
        }
        if ($jour == 1 && $mois == 11) {
            return "La Toussaint";
        }
        if ($jour == 11 && $mois == 11) {
            return "Armistice";
        }
        if ($jour == 25 && $mois == 12) {
            return "Noël";
        }

        // fetes religieuses mobiles
        $date = new DateTime("$annee-03-21");
        $days = easter_days($annee);
        $date->add(new DateInterval("P{$days}D"));
        $pak = $date->getTimestamp();

        if ($date->format('m-d') == "$mois-$jour") {
            return "Pâques";
        }

        if ($date->modify('+1 day')->format('m-d') == "$mois-$jour") {
            return "Lundi de Pâques";
        }

        $asc = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 39, date("Y", $pak));
        $jp = date("d", $asc);
        $mp = date("m", $asc);
        if ($jp == $jour && $mp == $mois) {
            return "Jeudi de l'Ascension";
        }

        $pe = mktime(date("H", $pak), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 49, date("Y", $pak));
        $jp = date("d", $pe);
        $mp = date("m", $pe);
        if ($jp == $jour && $mp == $mois) {
            return "Pentecôte";
        }

        $lp = mktime(date("H", $asc), date("i", $pak), date("s", $pak), date("m", $pak), date("d", $pak) + 50, date("Y", $pak));
        $jp = date("d", $lp);
        $mp = date("m", $lp);
        if ($jp == $jour && $mp == $mois) {
            return "Lundi Pentecôte";
        }

        return null;
    }
}
