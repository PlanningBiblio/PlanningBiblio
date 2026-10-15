<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Config;
use App\Entity\Holiday;
use App\Entity\WorkingHour;
use App\Planno\WorkingHours;
use DateTime;
use DateTimeImmutable;
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
        $changeDates = $request->query->getInt('changeDates');

        $start = $this->initDate('start', 'calendarViewStart', 'last monday', 'd/m/Y');
        $end = $this->initDate('end', 'calendarViewEnd', 'second sunday', 'd/m/Y');
        $displayAllAbsences = $this->initBoolean('all-absences', 'calendarViewAllAbsences', 'start');
        // FIXME: Whenever possible, replace the following conditions with the DataTable's Select Columns method. #}
        $showDept = $this->initBoolean('show-dept', 'calendarViewShowDept', 'start') ? 'checked' : null;
        $showSites = $this->initBoolean('show-sites', 'calendarViewShowSites', 'start') ? 'checked' : null;
        $showStatus = $this->initBoolean('show-status', 'calendarViewShowStatus', 'start') ? 'checked' : null;

        $start->modify('midnight');
        $end->modify('+1 day midnight -1 second');
        $diffDates = date_diff($start, $end)->format('%a');

        if ($changeDates) {
            $scope = $diffDates > 1 ? 'week' : 'day';
            $start = $start->modify($changeDates . " $scope");
            $end = $end->modify($changeDates . " $scope");
            $session->set('calendarViewStart', $start->format('d/m/Y'));
            $session->set('calendarViewEnd', $end->format('d/m/Y'));
        }

        $allAgents = $this->entityManager->getRepository(Agent::class)->get('Actif');
        $absences = $this->entityManager->getRepository(Absence::class)->get($start, $end);
        $config = $this->entityManager->getRepository(Config::class)->getAll();
        $holidays = $this->entityManager->getRepository(Holiday::class)->get($start, $end);
        $workingHours = $this->entityManager->getRepository(WorkingHour::class)->get($start, $end, true);

        // Get used departments, sites and status
        $departments = [];
        $sites = [];
        $status = [];

        foreach ($allAgents as $elem) {
            $departments[] = ['id' => $elem->getService(), 'name' => $elem->getService()];
            if (empty($elem->getService())) {
                $departments[] = ['id' => '', 'name' => $translator->trans('No department')];  
            }

            $status[] = ['id' => $elem->getStatus(), 'name' => $elem->getStatus()];
            if (empty($elem->getStatus())) {
                $status[] = ['id' => '', 'name' => $translator->trans('No status')];  
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

        $status = array_unique($status, SORT_REGULAR);
        array_multisort(array_column($status, 'name'), SORT_ASC, SORT_STRING, $status);

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
        $selectedStatus = $this->initArray('status', 'calendarViewStatus', array_column($status, 'id'));

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

            if ($siteIsSelected and in_array($agent->getService(), $selectedDepartments) and in_array($agent->getStatus(), $selectedStatus)) {
                $agents[] = $agent;
            }
        }

        // Preparing the display
        // Daily display
        $columns = [''];

        // Half-day display
        if ($diffDates < 14) {
            $columns = ['A.M.', 'P.M.'];
            $medianTime = '12:00:00';
        }

        // Hourly display
        if ($diffDates < 1) {
            $columns = [];
            $columnHour = DateTime::createFromFormat('H', '06');
            for ($i = 0; $i < 17; $i++) {
                $columns[] = $columnHour->format('H:i:s');
                $columnHour->modify('+1 hour');
            }
        }

        $columnsPerDay = count($columns);

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

            // For each date
            foreach ($allDates as $current) {
                $searchStart = (clone $current)->modify('midnight');
                $searchEnd = (clone $current)->modify('+1 day midnight -1 second');

                $day = $current->format('N');
                $date = new \datePl($current->format('Y-m-d'));
                $weekId = $date->semaine3;
                $dayIndex = ($day + (7 * $weekId) -7) - 1;

                // Defines the median time for the display of 2 columns per day
                if ($columnsPerDay == 2) {
                    $medianTimeDt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $current->format('Y-m-d') . ' ' . $medianTime);
                }

                // For each column
                foreach ($columns as $key => $columnStartStr) {
                    $cells[$key] = &$line[$current->format('Y-m-d')][$key];

                    // Zebra on Sundays
                    if (!$this->config['Dimanche'] and $current->format('N') == 7) {
                        $cells[$key] = 'zebra';
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
                                // Full day check
                                if ($columnsPerDay == 1) {
                                    $cells[$key] = 'attendee';
                                }
                                // Half day check
                                elseif ($columnsPerDay == 2) {
                                    foreach ($hours as $hour) {
                                        if ($key == 0 and $hour[0] < $medianTime) {
                                            $cells[$key] = 'attendee';
                                        }
                                        if ($key == 1 and $hour[1] > $medianTime) {
                                            $cells[$key] = 'attendee';
                                        }
                                    }
                                // Hourly check
                                } else {
                                    $columnEnd = DateTimeImmutable::createFromFormat('H:i:s', $columnStartStr)->modify('+ 1 hour')->format('H:i:s');
                                    foreach ($hours as $hour) {
                                        if ($hour[0] <= $columnStartStr and $hour[1] >= $columnEnd) {
                                            $cells[$key] = 'attendee';
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Mark absences
                    if ($cells[$key] == 'attendee' or $displayAllAbsences) {
                        foreach($absences as $absence) {
                            // Mark validated absences
                            if ($absence->getUserId() == $agent->getId()
                                and $absence->getStart() <= $searchEnd
                                and $absence->getEnd() >= $searchStart
                                and $absence->getValidLevel2() > 0
                            ) {
                                // Full day check
                                if ($columnsPerDay == 1) {
                                    $cells[0] = 'absence-validated';
                                }
                                // Half day check
                                elseif ($columnsPerDay == 2) {
                                    if ($key == 0 and $absence->getStart() < $medianTimeDt) {
                                        $cells[0] = 'absence-validated';
                                    }
                                    if ($key == 1 and $absence->getEnd() > $medianTimeDt) {
                                        $cells[1] = 'absence-validated';
                                    }
                                // Hourly check
                                } else {
                                    $columnStart = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $current->format('Y-m-d') . ' ' . $columnStartStr);
                                    $columnEnd = $columnStart->modify('+1 hour');
                                    if ($absence->getStart() < $columnEnd and $absence->getEnd() > $columnStart) {
                                        $cells[$key] = 'absence-validated';
                                    }
                                }
                            }

                            // Mark not validated absences
                            if ($absence->getUserId() == $agent->getId()
                                and $absence->getStart() <= $searchEnd
                                and $absence->getEnd() >= $searchStart
                                and $absence->getValidLevel2() <= 0
                            ) {
                                // Full day check
                                if ($columnsPerDay == 1 and $cells[0] != 'validated') {
                                    $cells[0] = 'absence-not-validated';
                                }
                                // Half day check
                                elseif ($columnsPerDay == 2) {
                                    if ($key == 0 and $absence->getStart() < $medianTimeDt and $cells[0] != 'absence-validated') {
                                        $cells[0] = 'absence-not-validated';
                                    }
                                    if ($key == 1 and $absence->getEnd() > $medianTimeDt and $cells[1] != 'absence-validated') {
                                        $cells[1] = 'absence-not-validated';
                                    }
                                // Hourly check
                                } else {
                                    $columnStart = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $current->format('Y-m-d') . ' ' . $columnStartStr);
                                    $columnEnd = $columnStart->modify('+1 hour');
                                    if ($absence->getStart() < $columnEnd and $absence->getEnd() > $columnStart and $cells[$key] != 'absence-validated') {
                                        $cells[$key] = 'absence-not-validated';
                                    }
                                }
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
            'columns' => $columns,
            'columnsPerDay' => $columnsPerDay,
            'departments' => $departments,
            'displayAllAbsences' => $displayAllAbsences ? 'checked' : null,
            'end' => $end->format('d/m/Y'),
            'selectedDepartments' => $selectedDepartments,
            'selectedSites' => $selectedSites,
            'selectedStatus' => $selectedStatus,
            'showDept' => $showDept,
            'showSites' => $showSites,
            'showStatus' => $showStatus,
            'sites' => $sites,
            'status' => $status,
            'start' => $start->format('d/m/Y'),
            'title' => 'Calendar view',
        ]);

        return $this->output('calendar_view/index.html.twig');
    }
}
