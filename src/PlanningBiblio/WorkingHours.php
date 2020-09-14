<?php

namespace App\PlanningBiblio;

class WorkingHours
{
    private $times;
    private $breaks;

    function __construct($times, $breaks = array())
    {
        $this->times = $times;
        $this->breaks = $breaks;
    }

    public function hoursOf($day)
    {

        $pause2 = $GLOBALS['config']['PlanningHebdo-Pause2'];
        if (!is_array($this->times)
            or empty($this->times)
            or !array_key_exists($day, $this->times)) {
            return array();
        }

        // Constitution des groupes de plages horaires
        $tab = array();
        $heures=$this->times[$day];

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

        // 2ème créneau : cas N° 1 et 9
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
      
        return $tab;
    }

}
