<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Holiday;
use App\Entity\WorkingHour;
use App\Planno\WorkingHours;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarViewController extends BaseController
{
    #[Route('/absence/calendar/view/{reset?}', name: 'calendar-view.index')]
    public function index(Request $request, Session $session): Response
    {
        $changeDates = $request->query->get('changeDates');
        $reset = $request->query->getBoolean('reset');

        $start = $this->initDate('start', 'calendarViewStart', 'last monday', 'd/m/Y', $reset);
        $end = $this->initDate('end', 'calendarViewEnd', 'second sunday', 'd/m/Y', $reset);
        $displayAllAbsences = $this->initBoolean('all-absences', 'calendarViewAllAbsences', false, $reset);

        if ($changeDates) {
            $start = $start->modify($changeDates);
            $end = $end->modify($changeDates);
            $session->set('calendarViewStart', $start->format('d/m/Y'));
            $session->set('calendarViewEnd', $end->format('d/m/Y'));
        }

        $agents = $this->entityManager->getRepository(Agent::class)->get('Actif');
        $absences = $this->entityManager->getRepository(Absence::class)->get($start, $end);
        $holidays = $this->entityManager->getRepository(Holiday::class)->get($start, $end);
        $workingHours = $this->entityManager->getRepository(WorkingHour::class)->get($start, $end, true);

        $diff = date_diff($start, $end);
        $halfDays = $diff->format('%a') < 14;

        $allAbsences = [];
        $allDates = [];

        $current = clone $start;
        while($current <= $end) {
            $allDates[] = clone $current;
            $current->modify('+1 day');
        }

        // For each agents (for each line)
        foreach($agents as $agent) {
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
            'displayAllAbsences' => $displayAllAbsences ? 'checked' : null,
            'end' => $end->format('d/m/Y'),
            'halfDays' => $halfDays,
            'start' => $start->format('d/m/Y'),
        ]);

        return $this->output('calendar_view/index.html.twig');
    }
}
