<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Absence;
use App\Entity\Agent;
use App\Entity\Holiday;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CalendarViewController extends BaseController
{
    #[Route('/calendar/view', name: 'calendar-view.index')]
    public function index(): Response
    {
        $start = $this->initDate('start', 'calendarViewStart', 'last monday');
        $end = $this->initDate('end', 'calendarViewEnd', 'second sunday');

        $agents = $this->entityManager->getRepository(Agent::class)->get();
        $absences = $this->entityManager->getRepository(Absence::class)->get($start, $end);
        $holidays = $this->entityManager->getRepository(Holiday::class)->get($start, $end);

        $allAbsences = [];
        $allDates = [];
        $current = clone $start;

        while($current <= $end) {
            $allDates[] = clone $current;
            foreach($agents as $agent) {
                foreach($absences as $absence) {
                    $cell = &$allAbsences[$agent->getId()][$current->format('Y-m-d')];

                    if ($absence->getUserId() == $agent->getId()
                        and $absence->getStart() <= $current
                        and $absence->getEnd() >= $current
                        and $absence->getValidLevel2() > 0
                    ) {
                        $cell = 'absence-validated';
                    }
                    if ($absence->getUserId() == $agent->getId()
                        and $absence->getStart() <= $current
                        and $absence->getEnd() >= $current
                        and $absence->getValidLevel2() <= 0
                        and $cell != 'validated'
                    ) {
                        $cell = 'absence-not-validated';
                    }

                }
            }
            $current->modify('+1 day');
        }

        $this->templateParams([
            'agents' => $agents,
            'allAbsences' => $allAbsences,
            'allDates' => $allDates,
            'end' => $end->format('d/m/Y'),
            'start' => $start->format('d/m/Y'),
        ]);

        return $this->output('calendar_view/index.html.twig');
    }
}
