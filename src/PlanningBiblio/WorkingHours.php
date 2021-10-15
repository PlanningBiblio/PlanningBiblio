<?php

namespace App\PlanningBiblio;

class WorkingHours
{
    private $times;
    private $breaks;

    function __construct($times, $breaks = array(), $free_break_period)
    {
        $this->times = $times;
        $this->breaks = $breaks;
        $this->free_break_period = $free_break_period;
    }

    public function hoursOf($day, $current = null)
    {

        /**
        * Tableau affichant les différentes possibilités
        * NB : le paramètre heures[4] est utilisé pour l'affectation du site. Il n'est pas utile ici
        * NB : la 2ème pause n'est pas implémentée depuis le début, c'est pourquoi les paramètres heures[5] et heures[6] viennent s'intercaler avant $heure[3]
        *
        *    Début       Débutpause1 Début       Débutpause2 Début       Fin
        *    Heure 0     Heure 1     Heure 2     Heure 5     Heure 6     Heure 3
        * 1                           [ tableau vide]
        * 2    |-----------|           |-----------|           |-----------|
        * 3    |-----------|           |-----------------------------------|
        * 4    |-----------|                                   |-----------|
        * 5    |-----------|
        * 6    |-----------------------------------|           |-----------|
        * 7    |-----------------------------------|
        * 8    |-----------------------------------------------------------|
        * 9                            |-----------|
        * 10                           |-----------------------------------|
        */


        $pause2 = $GLOBALS['config']['PlanningHebdo-Pause2'];

        if (!is_array($this->times)
            or empty($this->times)
            or !array_key_exists($day, $this->times)) {
            return array();
        }

        
        // Constitution des groupes de plages horaires
        $tab = array();
        $heures = $this->times[$day];

if ($this->free_break_period) {
            var_dump($heures);
        }

        $break = isset($this->breaks[$day]) ? $this->breaks[$day] : 0;

        // 1er créneau : cas N° 2; 3; 4; 5
        if (!empty($heures[0]) and !empty($heures[1])) {
            $tab[] = array($heures[0], $heures[1]);

        // 1er créneau fusionné avec le 2nd : cas N° 6 et 7
        } elseif ($pause2 and !empty($heures[0]) and !empty($heures[5])) {
            $tab[] = array($heures[0], $heures[5]);

        // Journée complète : cas N° 8
        } elseif (!empty($heures[0]) and !empty($heures[3])) {
            $tab[] = array($heures[0], $heures[3]);
        }

        // 2ème créneau : cas N° 2 et 9
        if ($pause2 and !empty($heures[2]) and !empty($heures[5])) {
            $tab[] = array($heures[2], $heures[5]);

        // 2ème créneau fusionné au 3ème : cas N° 3 et 10
        } elseif (!empty($heures[2]) and !empty($heures[3])) {
            $tab[] = array($heures[2], $heures[3]);
        }

        // 3ème créneau : cas N° 2; 4; 6
        if ($pause2 and !empty($heures[6]) and !empty($heures[3])) {
            $tab[] = array($heures[6], $heures[3]);
        }
        //TODO: FIXME here
echo "---------\n";
var_dump($tab);
        if ($break) {
            // Pause normale, Début
            if ($this->free_break_period) {
/*
                // Toujours cas 8?
                // TODO: params
                $free_break_start = "12:00";
                $free_break_end = "14:00";
                $free_break_duration = 1;

                // Free break is substracted on its own period
                echo "Free break is substracted on its own period: ";
                echo "$this->free_break_period\n";
                if ($this->free_break_period == "end") {
                    $day_start = $tab[0][1];
                    $this_free_break_start = $this->substractBreak($free_break_end, $free_break_duration);
                } else {

                }
                $i = 0;
                $day_end = $tab[$i][1];
                // free break start
                $tab[$i][1] = $free_break_start;
                // free break end
                #$tab[$i][2] = $this->addBreak($free_break_start, $free_break_duration);
                // Day end
                #$tab[$i][3] = $day_end;
                $tab[$i][2] = $day_end;

var_dump($tab);
*/
            } else {
                // Free break is substracted at the end of the day
                echo "Free break is substracted at the end of the day\n";
                $substracted = 0;
                foreach (array(2, 1, 0) as $i) {
                    if (isset($tab[$i])) {
                        if (!$substracted) {
    #echo "substract";
                            $tab[$i][1] = $this->substractBreak($tab[$i][1], $break);
                        }
                        $substracted = 1;

                        if ($i -1 >= 0 && strtotime($tab[$i][1]) <= strtotime($tab[$i -1][1])) {
                            $tab[$i -1][1] = $tab[$i][1];
                        }

                        if (strtotime($tab[$i][1]) <= strtotime($tab[$i][0])) {
                            unset($tab[$i]);
                        }
                    }
                }
            }
        }
#var_dump($tab);
        return $tab;

    }

    private function substractBreak($hour, $interval)
    {
         $minutes = $interval * 60;
         $new_hour = date('H:i:s', strtotime("- $minutes minutes $hour"));

         return $new_hour;
    }

    private function addBreak($hour, $interval)
    {
         $minutes = $interval * 60;
         $new_hour = date('H:i:s', strtotime("+ $minutes minutes $hour"));

         return $new_hour;
    }


}
