<?php

namespace App\Planno;

// For datePl
require_once __DIR__ . '/../../legacy/Common/function.php';

use App\Entity\Absence;
use App\Entity\Holiday;
use App\Planno\DateTime\TimeSlot;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class PresentSet
{
    private EntityManagerInterface $entityManager;

    /** @var mixed[] */
    private array $config;

    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->config = $GLOBALS['config'];
    }

    /**
     * @return array{id: mixed, nom: non-falsy-string, site: (non-falsy-string | null), heures: non-falsy-string}[]
     */
    public function all(string $date, int $site = 0): array
    {
        $query = $this->entityManager->createQuery(<<<'DQL'
            SELECT a FROM App\Entity\Agent a
            WHERE a.actif LIKE :actif
                AND (a.depart >= :date OR a.depart IS NULL)
                AND a.id != 2
            ORDER BY a.nom, a.prenom
        DQL);
        $query->setParameter('actif', 'Actif');
        $query->setParameter('date', $date);
        $agents = $query->getResult();

        if ($this->config['PlanningHebdo']) {
            $tempsPlanningHebdo = self::getPlanningHebdo($date);
        }

        $presents = array();
        foreach ($agents as $agent) {
            // Exclude agents who are not working on the request site
            if ($this->config['Multisites-nombre'] > 1 and $site != 0 ) {
                $agentSites = $agent->getSites();
                if (!is_array($agentSites) or !in_array($site, $agentSites)) {
                    continue;
                }
            }

            $heures = null;
            $temps = array();
            $week_number = 0;

            $agentId = $agent->getId();

            if ($this->config['PlanningHebdo']) {
                if (array_key_exists($agentId, $tempsPlanningHebdo)) {
                    $temps = $tempsPlanningHebdo[$agentId]['temps'];
                    $week_number = $tempsPlanningHebdo[$agentId]['nb_semaine'];
                }
            } else {
                $temps = $agent->getWorkingHours();
            }

            $datePl = new \datePl($date);
            $jour = $datePl->planning_day_index_for($agentId, $week_number);

            // Si l'emploi du temps est renseigné
            if (!empty($temps) and array_key_exists($jour, $temps)) {
                // S'il y a une heure de début (matin ou midi)
                if ($temps[$jour][0] or $temps[$jour][2]) {
                    $heures=$temps[$jour];
                }
            }

            // S'il y a des horaires correctement renseignés
            $siteAgent=null;
            if ($heures) {
                if ($this->config['Multisites-nombre']>1) {
                    if (!empty($heures[4])) {
                        $siteAgent = $heures[4] == -1 ? "Tout site" : $this->config['Multisites-site'.$heures[4]];
                    }
                }
                $siteAgent=$siteAgent?$siteAgent.", ":null;

                $schedule = [];
                if (!empty($heures[0]) and !empty($heures[1])) {
                    $schedule[] = [$heures[0], $heures[1]];
                } elseif (!empty($heures[0]) and !empty($heures[5])) {
                    $schedule[] = [$heures[0], $heures[5]];
                } elseif (!empty($heures[0]) and !empty($heures[3])) {
                    $schedule[] = [$heures[0], $heures[3]];
                }

                if (!empty($heures[2]) and !empty($heures[5])) {
                    $schedule[] = [$heures[2], $heures[5]];
                } elseif (!empty($heures[2]) and !empty($heures[3])) {
                    $schedule[] = [$heures[2], $heures[3]];
                }

                if (!empty($heures[6]) and !empty($heures[3])) {
                    $schedule[] = [$heures[6], $heures[3]];
                }

                // Remove hours where the agent is away
                $awayTimeSlots = $this->getAgentAwayTimeSlots($agentId, $date);
                $schedule = array_filter(
                    $schedule,
                    function ($s) use ($date, $awayTimeSlots): bool {
                        $begin = new DateTime(sprintf('%s %s', $date, $s[0]));
                        $end = new DateTime(sprintf('%s %s', $date, $s[1]));
                        foreach ($awayTimeSlots as $awayTimeSlot) {
                            if ($awayTimeSlot->includes($begin) && $awayTimeSlot->includes($end)) {
                                return false;
                            }
                        }
                        return true;
                    }
                );

                if ($schedule) {
                    $schedule = array_map(fn ($s) => sprintf('%s - %s', heure2($s[0]), heure2($s[1])), $schedule);
                    $presents[] = [
                        "id" => $agentId,
                        "nom" => $agent->getLastname() . " " . $agent->getFirstname(),
                        "site" => $siteAgent,
                        "heures" => implode(' & ', $schedule),
                    ];
                }
            }
        }

        return $presents;
    }

    /**
     * @return mixed[]
     */
    private static function getPlanningHebdo(string $date): array
    {
        // if module PlanningHebdo: search related plannings.
        require_once __DIR__ . '/../../legacy/Class/class.planningHebdo.php';

        $p = new \planningHebdo();
        $p->debut = $date;
        $p->fin = $date;
        $p->valide = true;
        $p->fetch();

        $tempsPlanningHebdo = array();

        if (!empty($p->elements)) {
            foreach ($p->elements as $elem) {
                $tempsPlanningHebdo[$elem["perso_id"]] = $elem;
            }
        }

        return $tempsPlanningHebdo;
    }

    /**
     * @return TimeSlot[]
     */
    public function getAgentAwayTimeSlots(int $agentId, string $date): array
    {
        $timeSlots = [];

        if ($this->config['Conges-Enable']) {
            /** @var \App\Repository\HolidayRepository */
            $holidayRepository = $this->entityManager->getRepository(Holiday::class);
            $holidays = $holidayRepository->get("$date 00:00:00", "$date 23:59:59", true, $agentId);

            foreach ($holidays as $holiday) {
                $timeSlots[] = new TimeSlot($holiday->getStart(), $holiday->getEnd());
            }
        }

        $start = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00');
        $end = DateTime::createFromFormat('Y-m-d H:i:s', $date . ' 23:59:59');

        /** @var \App\Repository\AbsenceRepository */
        $absenceRepository = $this->entityManager->getRepository(Absence::class);
        $absences = $absenceRepository->get($start, $end, true, $agentId);
        foreach ($absences as $absence) {
            $timeSlots[] = new TimeSlot($absence->getStart(), $absence->getEnd());
        }

        $timeSlots = TimeSlot::merge($timeSlots);

        return $timeSlots;
    }
}

