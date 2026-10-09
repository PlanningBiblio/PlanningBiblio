<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Config;
use App\Entity\Holiday;
use App\Entity\WorkingHour;
use App\Planno\WorkingHours;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CalendarViewController extends BaseController
{
    #[Route('/absence/calendar/view/{reset?}', name: 'calendar-view.index', methods: ['GET'], requirements: ['reset' => 'reset'])]
    public function index(Request $request, Session $session, TranslatorInterface $translator): Response
    {
        $changeDates = $request->query->get('changeDates');

        $start = $this->initDate('start', 'calendarViewStart', 'last monday', 'd/m/Y');
        $end = $this->initDate('end', 'calendarViewEnd', 'second sunday', 'd/m/Y');
        $displayAllAbsences = $this->initBoolean('all-absences', 'calendarViewAllAbsences');

        if ($changeDates) {
            $start = $start->modify($changeDates);
            $end = $end->modify($changeDates);
            $session->set('calendarViewStart', $start->format('d/m/Y'));
            $session->set('calendarViewEnd', $end->format('d/m/Y'));
        }

        $allAgents = $this->entityManager->getRepository(Agent::class)->get('Actif');
        $absences = $this->entityManager->getRepository(Absence::class)->get($start, $end);
        $config = $this->entityManager->getRepository(Config::class)->getAll();
        $holidays = $this->entityManager->getRepository(Holiday::class)->get($start, $end);
        $workingHours = $this->entityManager->getRepository(WorkingHour::class)->get($start, $end, true);

        $diff = date_diff($start, $end);
        $halfDays = $diff->format('%a') < 14;

        // Get used departments and sites
        $departments = [];
        $sites = [];

        foreach ($allAgents as $elem) {
            $departments[] = ['id' => $elem->getService(), 'name' => $elem->getService()];
            if (empty($elem->getService())) {
                $departments[] = ['id' => '', 'name' => $translator->trans('No department')];  
            }
            foreach($elem->getSites() as $site) {
                $sites[] = $site;
            }
            if (empty($elem->getSites())) {
                $sites[] = 0;
            }
        }

        $departments = array_unique($departments, SORT_REGULAR);
        array_multisort(array_column($departments, 'name'), SORT_ASC, SORT_STRING, $departments);

        $sites = array_unique($sites);
        sort($sites);
        foreach ($sites as &$site) {
            $site = [
                'id' => $site,
                'name' => $site ? $config['Multisites-site' . $site] : $translator->trans('No site'),  
            ];
        }

        $selectedDepartments = $this->initArray('departments', 'calendarViewDept', array_column($departments, 'id'));
        $selectedSites = $this->initArray('sites', 'calendarViewSites', array_column($sites, 'id'));

        // Get filtered agents
        $agents = [];
        foreach ($allAgents as $agent) {
            $siteIsSelected = false;
            if (empty($agent->getSites()) and in_array(0, $selectedSites)) {
                $siteIsSelected = true;
            }
            foreach ($agent->getSites() as $agentSite) {
                if (in_array($agentSite, $selectedSites)) {
                    $siteIsSelected = true;
                }
            }

            if ($siteIsSelected and in_array($agent->getService(), $selectedDepartments)) {
                $agents[] = $agent;
            }
        }

        // Dates and absences
        $allAbsences = [];
        $allDates = [];

        $current = clone $start;
        while($current <= $end) {
            $allDates[] = clone $current;
            $current->modify('+1 day');
        }

        // For each agents (for each line)
        foreach($agents as $key => $agent) {
            $line = &$allAbsences[$agent->getId()];

            // For each dates (for each column)
            foreach ($allDates as $current) {
                // cell0 = All day if halfDays = false, AM if halfDays = true
                // cell1 = Empty if halfDays = false, PM if halfDays = true
                $cell0 = &$line[$current->format('Y-m-d')][0];
                $cell1 = &$line[$current->format('Y-m-d')][1];

                $day = $current->format('N');
                $date = new \datePl($current->format('Y-m-d'));
                $weekId = $date->semaine3;
                $dayIndex = ($day + (7 * $weekId) -7) - 1;
                $mediumHour = \DateTime::createFromFormat('Y-m-d H:i:s', $current->format('Y-m-d') . ' 12:00:00');

                // Zebra on Sundays
                if (!$this->config['Dimanche'] and $current->format('N') == 7) {
                    $cell0 = 'zebra';
                    $cell1 = 'zebra';
                }

                // Mark attendees
                foreach($workingHours as $wh) {
                    if ($wh->getUser() == $agent->getId()
                        and $wh->getStart() <= $current
                        and $wh->getEnd() >= $current
                    ) {
                        $times = $wh->getWorkingHours();
                        $hoursHelper = new WorkingHours($times);
                        $hours = $hoursHelper->hoursOf($dayIndex);

                        if (!empty($hours)) {
                            // Half day check
                            if ($halfDays) {
                                foreach ($hours as $hour) {
                                    if ($hour[0] < '12:00:00') {
                                        $cell0 = 'attendee';
                                    }
                                    if ($hour[1] > '12:00:00') {
                                        $cell1 = 'attendee';
                                    }
                                }
                            // Full day check
                            } else {
                                $cell0 = 'attendee';
                            }
                        }
                    }
                }

                // Mark absences
                if ($cell0 == 'attendee' or $cell1 == 'attendee' or $displayAllAbsences) {
                    foreach($absences as $absence) {
                        // Mark validated absences
                        if ($absence->getUserId() == $agent->getId()
                            and $absence->getStart() <= $current
                            and $absence->getEnd() >= $current
                            and $absence->getValidLevel2() > 0
                        ) {
                            if ($halfDays) {
                                if ($absence->getStart() < $mediumHour) {
                                    $cell0 = 'absence-validated';
                                }
                                if ($absence->getEnd() > $mediumHour) {
                                    $cell1 = 'absence-validated';
                                }
                            } else {
                                $cell0 = 'absence-validated';
                            }
                        }

                        // Mark not validated absences
                        if ($absence->getUserId() == $agent->getId()
                            and $absence->getStart() <= $current
                            and $absence->getEnd() >= $current
                            and $absence->getValidLevel2() <= 0
                        ) {
                            if ($halfDays) {
                                if ($absence->getStart() < $mediumHour and $cell0 != 'absence-validated') {
                                    $cell0 = 'absence-not-validated';
                                }
                                if ($absence->getEnd() > $mediumHour and $cell1 != 'absence-validated') {
                                    $cell1 = 'absence-not-validated';
                                }
                            } elseif ($cell0 != 'validated') {
                                $cell0 = 'absence-not-validated';
                            }
                        }
                    }
                }
            }
        }

        $this->templateParams([
            'agents' => $agents,
            'allAbsences' => $allAbsences,
            'allDates' => $allDates,
            'departments' => $departments,
            'displayAllAbsences' => $displayAllAbsences ? 'checked' : null,
            'end' => $end->format('d/m/Y'),
            'halfDays' => $halfDays,
            'selectedDepartments' => $selectedDepartments,
            'selectedSites' => $selectedSites,
            'sites' => $sites,
            'start' => $start->format('d/m/Y'),
            'title' => 'Calendar view',
        ]);

        return $this->output('calendar_view/index.html.twig');
    }
}
