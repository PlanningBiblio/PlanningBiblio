<?php

namespace App\PlanningBiblio\Helper;

use App\PlanningBiblio\Helper\BaseHelper;
use App\PlanningBiblio\Helper\WeekPlanningHelper;
use App\PlanningBiblio\WorkingHours;
use App\PlanningBiblio\ClosingDay;
use App\Model\Agent;

include_once __DIR__ . '/../../../public/planningHebdo/class.planningHebdo.php';
include_once(__DIR__ . '/../../../public/include/function.php');

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

    public function HumanReadableDuration($hours, $force = null)
    {
        if ($hours == '' || $hours == '0.00') {
          $hours = 0;
        }

        $negative = $hours < 0 ? true : false;
        if ($negative) {
            $hours = abs($hours);
        }


        $mode = $this->config('Conges-Mode');
        if ($force && ($force == 'heures' || $force == 'jours')) {
            $mode = $force;
        }

        if ($mode == 'heures') {
            $human_readable = heure4($hours, true);

            if ($negative) {
                $human_readable = "-$human_readable";
            }

            return $human_readable;
        }

        // TODO: should be altered by a plugin.
        $days = $hours / 7;
        $days = round($days * 2) / 2;

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
        $result = array(
            'error'     => false,
            'minutes'   => 0,
            'hours'     => 0,
            'hr_hours'  => '0h00'
        );

        $per_week = array();
        $regul_total = 0;

        // For each requested date.
        while ($current <= $fin) {

            $date_current = new \DateTime($current);
            $week_id = $date_current->format("W");
            $day_id = $date_current->format("N") - 1;

            // Check agent's planning.
            $planning = $this->getPlanning($current);
            if ($this->error) {
                $result['error'] = true;
                return $result;
            }

            $week_helper = new WeekPlanningHelper($planning['times']);
            $per_week[$week_id]['worked_days'] = $week_helper->NumberWorkingDays();

            if (!isset($per_week[$week_id]['requested_days'])) {
                $per_week[$week_id]['requested_days'] = 0;
                $per_week[$week_id]['times'] = 0;
            }

            if ($week_helper->isWorkingDay($date_current)) {
                $per_week[$week_id]['requested_days']++;
            }

            // We ignore closing day
            $closingday = false;
            if ($this->isClosingDay($current)) {
                $closingday = true;
                if ($week_helper->isWorkingDay($date_current)) {
                    $per_week[$week_id]['requested_days']--;
                }
            }


            $debutConges = $current == $debut ? $hre_debut : "00:00:00";
            $finConges = $current == $fin ? $hre_fin : "23:59:59";

            // Convert free break as fixed break if needed
            $free_break_already_removed = false;
            $has_fixed_break = isset($planning['times'][$day_id][1]) && $planning['times'][$day_id][1] != "";

            if (isset($planning['breaktimes']) && !$has_fixed_break) {

                $free_break_already_removed = true;
                $free_break_start = $this->config('PlanningHebdo-DebutPauseLibre');
                $free_break_end = $this->config('PlanningHebdo-FinPauseLibre')
                $free_break_duration = $planning['breaktimes'][$day_id] * 60;

                if (strtotime($debutConges) >= strtotime($free_break_start) && 
                    strtotime($finConges)   <= strtotime($free_break_end)) {
                    // If the holiday is shorter than the free break, the free break starts at the beginning of the holiday
                    $planning["times"][$day_id][1] = $debutConges;
                    $planning["times"][$day_id][2] = date('H:i:s', strtotime("+ $free_break_duration minutes $debutConges"));
                } elseif (substr($debutConges, 0, 2) >= 12) {
                    // If the holiday is in the afternoon, the free break is at the end of its period.
                    $fixed_free_break_start = date('H:i:s', strtotime("- $free_break_duration minutes $free_break_end"));
                    $planning["times"][$day_id][1] = $fixed_free_break_start;
                    $planning["times"][$day_id][2] = $free_break_end;
                } else {
                    // If the holiday is in the morning, the free break is at the beginning of its period.
                    $fixed_free_break_end = date('H:i:s', strtotime("+ $free_break_duration minutes $free_break_start"));
                    $planning["times"][$day_id][1] = $free_break_start;
                    $planning["times"][$day_id][2] = $fixed_free_break_end;
                }
            }

            $debutConges = strtotime($debutConges);
            $finConges = strtotime($finConges);

        
            $times = $this->getTimes($planning, $current, $free_break_already_removed);

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

            if($today > 0 && $this->config('Conges-Mode') == 'jours' && !$this->data['is_recover']) {
                // 3600 = 1h, 12600 = 3,5h, 25200 = 7h
                // the default time for switching from half-day to full-day is 4 hours (14400 seconds)
                $switching_time = (float) ($this->config['Conges-fullday-switching-time'] ?? 4);
                $switching_time = $switching_time * 3600;

                if (is_numeric($this->config('Conges-fullday-reference-time'))) {
                    $reference_time = $this->config('Conges-fullday-reference-time') * 3600;
                    $reference_time = $today <= $switching_time ? $reference_time / 2 : $reference_time;
                    $rest = $reference_time - $today;
                    $regul_hours = $rest / 3600;
                    $regul_total += $regul_hours;

                }

                $today = $today <= $switching_time ? 12600 : 25200;
            }

            // If this is a closing day, don't check for
            // "normal" hours. Only take into account regularization.
            if (!$closingday) {
                $per_week[$week_id]['times'] += number_format($today / 3600, 2, '.', '');
            }

            $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
        }
        $total = 0;
        foreach ($per_week as $week) {
            $counted = $this->applyWeekTable($week);

            if ($counted > 0) {
                $total += $counted;
                continue;
            }

            $total += $week['times'];
        }

        $total = number_format($total, 2, '.', '');
        $hours_minutes = explode('.', $total);
        $result['hours'] = $hours_minutes[0];
        $result['minutes'] = isset($hours_minutes[1]) ? $hours_minutes[1] : 0;
        $result['hr_hours'] = heure4($total); // 2.5 => 2h30
        $result['rest'] = $regul_total;

        $result['hr_rest'] = heure4($regul_total) ?? '';
        if ($regul_total < 0) {
            $hr_rest = heure4(abs($regul_total));
            $result['hr_rest'] = $hr_rest;
        }

        $result['days'] = $this->hoursToDays($total, $perso_id);

        return $result;
    }

    public function hoursPerDay($perso_id, $holidays_hours_per_year = null)
    {
        if ($this->config('conges-hours-per-day')) {
            if ($holidays_hours_per_year == null) {
                $agent = $this->entityManager->find(Agent::class, $perso_id);
                $holidays_hours_per_year = $agent->conges_annuel();
            }
            $intervals = $this->config['conges-hours-per-day'];
            foreach ($intervals as $hours => $hours_per_day) {
                if ($holidays_hours_per_year >= $hours) {
                    return $hours_per_day;
                }
            }
        } else {
            return 7;
        }
        return -1;
    }

    public function hoursToDays($given_hours, $perso_id, $holidays_hours_per_year = null, $human_readable = false) {
        if (empty($given_hours) and !$human_readable) { return 0; }
        if (empty($given_hours) and $human_readable) { return null; }

        $hours_per_day = ($holidays_hours_per_year == null) ? $this->hoursPerDay($perso_id) : $this->hoursPerDay(null, $holidays_hours_per_year);

        $result = round((float) $given_hours / $hours_per_day, 2);

        if ($human_readable) {
            if (empty($result)) {
                return null;
            }
            return $result  > 1 ? ' / ' . $result . ' jours' : ' / ' . $result . ' jour';
        }

        return $result;
    }

    public function showHoursToDays() {
        if ($this->config('Conges-Mode') != 'heures') {
            return false;
        }

        return $this->config('conges-hours-per-day');
    }

    public function getManagedAgent($adminN2, $deleted_agents = false)
    {
        $access_rights = $GLOBALS['droits'];

        $agents = array();
        $p=new \personnel();
        $p->responsablesParAgent = true;
        if ($deleted_agents) {
            $p->supprime=array(0,1);
        }
        $p->fetch();
        $agents=$p->elements;

        $tmp = array();
        foreach ($agents as $elem) {
            if ($elem['id'] == $_SESSION['login_id']) {
                $tmp[$elem['id']] = $elem;
                continue;
            }

            if ($this->config('Multisites-nombre') == 1) {
                $elem['sites'] = array(1);
            }

            if (is_array($elem['sites'])) {
                foreach ($elem['sites'] as $site_agent) {
                    if (in_array((400+$site_agent), $access_rights) or in_array((600+$site_agent), $access_rights)) {
                        $tmp[$elem['id']] = $elem;
                        continue 2;
                    }
                }
            }
        }
        $agents = $tmp;

        // Filtre pour n'afficher que les agents gérés si l'option "Absences-notifications-agent-par-agent" est cochée
        if ($this->config('Absences-notifications-agent-par-agent') and !$adminN2) {
            $tmp = array();

            foreach ($agents as $elem) {
                if ($elem['id'] == $_SESSION['login_id']) {
                    $tmp[$elem['id']] = $elem;
                } else {
                    foreach ($elem['responsables'] as $resp) {
                        if ($resp['responsable'] == $_SESSION['login_id']) {
                            $tmp[$elem['id']] = $elem;
                            break;
                        }
                    }
                }
            }
            $agents = $tmp;
        }

        return $agents;
    }

    private function applyWeekTable($week)
    {
        if($this->config('Conges-Mode') == 'heures' || $this->data['is_recover']) {
            return 0;
        }

        // Staff member didn't request a full week.
        if ($week['requested_days'] < $week['worked_days']) {
            return 0;
        }

        $perso_id = $this->data['perso_id'];
        $agent = $this->entityManager->find(Agent::class, $perso_id);
        $annual_hours = $agent->conges_annuel() / 7;
        $counting_chart = $this->config('holiday_counting_chart');

        if (empty($counting_chart)) {
            return 0;
        }

        foreach ($counting_chart as $range => $hours) {
            list($from, $to) = explode('.', $range);

            if ( $annual_hours >= $from && $annual_hours <= $to ) {
                return $hours;
            }
        }

        return 0;
    }

    /** NOTE The getTimes function should be on WeekPlanningHelper.
     * Jérôme added something similar on WeekPlanningHelper (getTimes($date, $agent = null, $planning = null))
     * TODO : See if WeekPlanningHelper::getTimes can be used instead of HolidayHelper::getTimes
     */
    private function getTimes($planning, $date, $free_break_already_removed)
    {
        // Sinon, on calcule les heures d'absence
        $d = new \datePl($date, $planning['nb_semaine']);
        $week = $d->semaine3;

        $day = $d->position ? $d->position : 7;
        $day = $day + (($week - 1) * 7) - 1;

        if ($this->config('PlanningHebdo-PauseLibre')) {
            $wh = new WorkingHours($planning['times'], $planning['breaktimes'], $free_break_already_removed);
        } else {
            $wh = new WorkingHours($planning['times']);
        }
#error_log(print_r($planning['breaktimes'], 1));
        return $wh->hoursOf($day, $date);
    }

    /** NOTE The getPlanning function should be on WeekPlanningHelper.
     * Jérôme added something similar on WeekPlanningHelper (getPlanning($date, $agent))
     * TODO : See if WeekPlanningHelper::getPlanning can be used instead of HolidayHelper::getPlanning
     */
    public function getPlanning($date = null)
    {
         $date ?? date('%Y-%m-%d');
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
            return;
         }
         $times = $p->elements[0]['temps'];
         $breaktimes = $p->elements[0]['breaktime'];
         $nb_semaine = $p->elements[0]['nb_semaine'];

         return array('times' => $times, 'breaktimes' => $breaktimes, 'nb_semaine' => $nb_semaine);
    }

    private function isClosingDay($date)
    {
        $j = new ClosingDay();
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
