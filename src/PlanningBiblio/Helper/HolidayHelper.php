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

        $mode = $this->config('Conges-Mode');
        if ($force && ($force == 'heures' || $force == 'jours')) {
            $mode = $force;
        }

        if ($mode == 'heures') {
            return heure4($hours, true);
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
            if ($this->isClosingDay($current)) {
                if ($week_helper->isWorkingDay($date_current)) {
                    $per_week[$week_id]['requested_days']--;
                }
                $current = date("Y-m-d", strtotime("+1 day", strtotime($current)));
                continue;
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

            if($today > 0 && $this->config('Conges-Mode') == 'jours' && !$this->data['is_recover']) {
                // 3600 = 1h, 12600 = 3,5h, 25200 = 7h
                // the default time for switching from half-day to full-day is 4 hours (14400 seconds)
                $switching_time = (float) ($this->config['Conges-fullday-switching-time'] ?? 4);
                $switching_time = $switching_time * 3600;

                if (is_numeric($this->config('Conges-fullday-reference-time'))) {
                    $reference_time = $this->config('Conges-fullday-reference-time') * 3600;
                    $regul = $reference_time - $today;
                    $regul_hours = $regul / 3600;
                    $regul_total += $regul_hours;

                    // TEST MT32844
                    error_log("\n");
                    error_log($current."\n");
                    error_log($regul_hours."\n");
                    error_log($regul_total."\n");

                    // NOTE : les jours cochés "fermeture" dans la table "jours_feries" ne doivent pas décompter de crédit de congés (c'est déjà le cas)
                    // MAIS ils doivent être pris en compte pour la régul s'ils sont habituellement travaillés (présence non-vide).
                    // Les jours fermés sont écartés à un plus haut niveau et ne passe donc pas dans cette boucle.
                    // TODO : voir comment les récupérer pour les comptabiliser

                    // NOTE : les jours de présence vides sont ignorés, pas de débit de congés, pas de régul : c'est parfait.

                    // TODO : display $total_regul in the holiday form (human readable) under "Nombre de jours :"
                    // TODO : When the holiday is validated (level 2), store a new comp-time (table recuperations) with :
                    //  -- perso_id = $perso_id
                    //  -- date = [Holiday_begining_date]
                    //  -- date2 = null
                    //  -- heures = $regul_total
                    //  -- etat = null
                    //  -- commentaires = "Régularisation congés du [Holiday_begining_date] au [holiday_ending_date]"
                    //  -- saisies = current_time
                    //  -- saisies_par = $_SESSION['login_id']
                    //  -- modif = $_SESSION['login_id']
                    //  -- modification = current_time
                    //  -- valide_n1 = 0
                    //  -- validation_n1 = null
                    //  -- valide = $_SESSION['login_id']
                    //  -- validation = current_time
                    //  -- refus = null
                    //  -- solde_prec = le solde précédent
                    //  -- solde_actuel = le nouveau solde
                    //  -- holiday_id (nouveau champ, voir plus bas) = ID du congé

                    // TODO : à cette même occasion, il faut additionner $regul_total au champ comp_time de la table personnel (si total_regul <0, ça fera automatiquement la soustraction : OK)
                    
                    // NOTE : la valeur de $regul_total peut être négative. ça ne devrait pas poser problème pour le champ comp_time de la table personnel, ni pour l'enregistrement dans la table recuperations : à vérifier
                    // TEST : enregistrement d'un crédit négatif depuis la fiche agent = OK

                    // TODO : Si un congé validé a généré une régul et qu'il est ensuite supprimé, il faut supprimer déduire du champ comp_time de la table personnel la valeur créditée (ou recréditer ce qui a été débité).
                    // Pour ceci :
                    // -- Ajouter un champ "holiday_id" dans la table "recuperations" et y stocker l'ID du congés qui a généré la régul
                    // -- Lors de la suppression du congés, nous pourrons alors vérifier la table recuperations, trouver l'ID correspondant, et s'il existe, adapter la valeur du champ comp_time de la table personnel.
                    // TODO : Cette mise à jour de compteurs devra générer un log  : ajouter la ligne adéquate dans la table congés avec le champ information = $_SESSION['login_id']
                    // Voir la ligne créée dans la table congés lors de la modification des crédits depuis la fiche agent : faire pareil.
                    // Résultat : les lignes "Mise à jour des crédits" sont visibles dans les tables de congés et de récup.
                }

                $today = $today <= $switching_time ? 12600 : 25200;
            }

            $per_week[$week_id]['times'] += number_format($today / 3600, 2, '.', '');

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
    private function getTimes($planning, $date)
    {
        // Sinon, on calcule les heures d'absence
        $d = new \datePl($date, $planning['nb_semaine']);
        $week = $d->semaine3;

        $day = $d->position ? $d->position : 7;
        $day = $day + (($week - 1) * 7) - 1;

        if ($this->config('PlanningHebdo-PauseLibre')) {
            $wh = new WorkingHours($planning['times'], $planning['breaktimes']);
        } else {
            $wh = new WorkingHours($planning['times']);
        }
        return $wh->hoursOf($day);
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
