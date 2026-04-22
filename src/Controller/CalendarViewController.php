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
use Symfony\Component\Routing\Attribute\Route;

final class CalendarViewController extends BaseController
{
    #[Route('/calendar/view/{reset?}', name: 'calendar-view.index')]
    public function index(Request $request): Response
    {
        $reset = $request->attributes->get('reset') == 'reset';

        $start = $this->initDate('start', 'calendarViewStart', 'last monday', 'd/m/Y', $reset);
        $end = $this->initDate('end', 'calendarViewEnd', 'next sunday', 'd/m/Y', $reset);
        $displayAllAbsences = $this->initBoolean('all-absences', 'calendarViewAllAbsences', false, $reset);

        $agents = $this->entityManager->getRepository(Agent::class)->get();
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

                // Mark attendees
                foreach($workingHours as $wh) {
                    if ($wh->getUser() == $agent->getId()
                        and $wh->getStart() <= $current
                        and $wh->getEnd() >= $current
                    ) {
                        $times = $wh->getWorkingHours();
                        $hoursHelper = new WorkingHours($times);
                        $hours = $hoursHelper->hoursOf($dayIndex);

                        // TODO, check AM / PM if $halfDays = true
                        if (!empty($hours)) {
                            $cell0 = 'attendee';
                        }
                    }
                }

                if ($cell0 == 'attendee' or $displayAllAbsences) {
                    foreach($absences as $absence) {
                        // Mark validated absences
                        if ($absence->getUserId() == $agent->getId()
                            and $absence->getStart() <= $current
                            and $absence->getEnd() >= $current
                            and $absence->getValidLevel2() > 0
                        ) {
                            $cell0 = 'absence-validated';
                        }

                        // Mark not validated absences
                        if ($absence->getUserId() == $agent->getId()
                            and $absence->getStart() <= $current
                            and $absence->getEnd() >= $current
                            and $absence->getValidLevel2() <= 0
                            and $cell0 != 'validated'
                        ) {
                            $cell0 = 'absence-not-validated';
                        }
                    }
                }
                // tmp
                $cell1 = $cell0;
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
