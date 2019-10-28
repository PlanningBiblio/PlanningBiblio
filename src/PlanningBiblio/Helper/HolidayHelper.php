<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;

include_once(__DIR__ . '/../../../public/joursFeries/class.joursFeries.php');
include_once __DIR__ . '/../../../public/planningHebdo/class.planningHebdo.php';
include_once(__diR__ . '/../../../public/include/function.php');

class HolidayHelper extends BaseHelper
{
    public $data;

    private $error = false;

    public function __construct($data = null)
    {
        if ($data) {
            $this->data = $data;
        }
        parent::__construct();
    }

    public function HumanReadableDuration($hours)
    {
        if ($hours == '') {
          $hours = 0;
        }

        if ($this->config('Conges-Mode') == 'heures') {
            return heure4($hours, true);
        }

        // TODO: should be altered by a plugin.
        $days = round($hours / 7, 1);

        // Handle plurals
        if ($days > 1) {
            return "$days jours";
        }

        return "$days jour";
    }

    public function getCountedHours()
    {
        $debut = $this->data['start'];
        $hre_debut = $this->data['hour_start'];
        $fin = $this->data['end'];
        $hre_fin = $this->data['hour_end'];
        $perso_id = $this->data['perso_id'];

        // Calcul du nombre d'heures correspondant aux congés demandés
        $current = $debut;
        $total = 0;
        $result = array(
            'error'     => false,
            'minutes'   => 0,
            'hours'     => 0,
            'hr_hours'  => '0h00'
        );

        // Pour chaque date
        while ($current<=$fin) {

            // On ignore les jours de fermeture
            if ($this->isTodayHoliday($current)) {
                $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
                continue;
            }

            // On consulte le planning de présence de l'agent
            $planning = $this->getPlanning($current);
            if ($this->error) {
                $result['error'] = true;
                return $result;
            }


            $debutConges = $current == $debut ? $hre_debut : "00:00:00";
            $finConges = $current == $fin ? $hre_fin : "23:59:59";
            $debutConges = strtotime($debutConges);
            $finConges = strtotime($finConges);

            $times = $this->getTimes($planning, $current);

            $today = 0;
            foreach ($times as $t) {
                $t0 = strtotime($t[0]);
                $t1 = strtotime($t[1]);

                $debutConges1 = $debutConges > $t0 ? $debutConges : $t0;
                $finConges1 = $finConges < $t1 ? $finConges : $t1;
                if ($finConges1 > $debutConges1) {
                    $today += $finConges1 - $debutConges1;
                }
            }
            if($this->config('Conges-Mode') == 'jours') {
                // 14400 = 4h, 12600 = 3,5h, 25200 = 7h
                $today = $today <= 14400 ? 12600 : 25200;
            }
            $total += $today;

            $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
        }

        $time = number_format($total / 3600, 2, '.', ''); // 2h30 => 2.5
        $hours_minutes = explode('.', $time);
        $result['hours'] = $hours_minutes[0];
        $result['minutes'] = $hours_minutes[1];
        $result['hr_hours'] = heure4($time); // 2h30 => 2h30
        $result['days'] = round($time / 7, 2);

        return $result;
    }

    private function getTimes($planning, $date)
    {
        // Sinon, on calcule les heures d'absence
        $d = new \datePl($date);
        $week = $d->semaine3;
        $day = $d->position ? $d->position : 7;
        $day = $day + (($week - 1) * 7) - 1;

        return calculPresence($planning, $day);
    }

    private function getPlanning($date)
    {
         // On consulte le planning de présence de l'agent
         $p =new \planningHebdo();
         $p->perso_id = $this->data['perso_id'];
         $p->debut = $date;
         $p->fin = $date;
         $p->valide = true;
         $p->fetch();
         // Si le planning n'est pas validé pour l'une des dates, on affiche un message d'erreur et on arrête le calcul
         if (empty($p->elements)) {
             $this->error = true;
             $this->message = "Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
         }
         return $p->elements[0]['temps'];
    }

    private function isTodayHoliday($date)
    {
        $j = new \joursFeries();
        $j->fetchByDate($date);
        if (!empty($j->elements)) {
            foreach ($j->elements as $elem) {
                if ($elem['fermeture']) {
                    return true;
                }
            }
        }

        return false;
    }
}

