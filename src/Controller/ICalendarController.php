<?php

namespace App\Controller;

use App\Controller\BaseController;
use App\Entity\Agent;
use App\Planno\PlanningExportUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class ICalendarController extends BaseController
{
    #[Route(path: 'ical', name: 'ical.index', methods: ['GET'])]
    public function index(Request $request, Session $session): \Symfony\Component\HttpFoundation\Response{

        $module = 'Ical export';

        if (!$this->config('ICS-Export')) {
            return $this->returnError("L'exportation ICS est désactivée", $module, 403);
        }

        $interval_get = $request->get('interval');
        $code = $request->get('code');
        $id = $request->get('id');
        $login = $request->get('login');
        $mail = $request->get('mail');
        $get_absences = $request->get('absences');

        $agent = null;

        // Définition de l'id de l'agent si l'argument login est donné
        if (!$id and $login) {
            $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(array('login' => $login));
            if ($agent) {
                $id = $agent->getId();
            } else {
                return $this->returnError("Impossible de trouver l'id associé au login $login", $module, 400);
            }
        }

        // Définition de l'id de l'agent si l'argument mail est donné
        if (!$id and $mail) {
            $agent = $this->entityManager->getRepository(Agent::class)->findOneBy(array('mail' => $mail));
            if ($agent) {
                $id = $agent->getId();
            } else {
                return $this->returnError("Impossible de trouver l'id associé au mail $mail", $module, 400);
            }
        }

        if (!$agent && $id) {
            $agent = $this->entityManager->getRepository(Agent::class)->find($id);
            if (empty($agent)) {
                return $this->returnError("id inconnu ($id)", $module, 400);
            }
        }

        if (!$id) {
            return $this->returnError("L'id de l'agent n'est pas fourni", $module, 400);
        }

        if ($this->config('ICS-Code')) {
            $agent_ics_code = $agent->getICSCode();
            if ($agent_ics_code != $code) {
                return $this->returnError('Accès refusé', $module, 401);
            }
        }

        $icsInterval = null;
        $start = null;

        if ($this->config('ICS-Interval') != '' && is_numeric($this->config('ICS-Interval'))) {
            $icsInterval = $this->config('ICS-Interval');
        }

        if ($interval_get != '' && is_numeric($interval_get)) {
            $icsInterval = $interval_get;
        }

        if ($icsInterval) {
            $icsInterval = (int) $icsInterval;
            $start = new \DateTime();
            $start->modify("-$icsInterval days");
        }

        $planningExport = new PlanningExportUtils($this->entityManager);
        $data = $planningExport->export([$id], $start, $get_absences);

        // Nom de l'agent pour X-WR-CALNAME
        $agent = nom($id);

        // Tableau $ical
        $ical=array();
        $ical[] = 'BEGIN:VCALENDAR';
        $ical[] = 'X-WR-CALNAME:Service Public ' . $agent;
        $ical[] = 'PRODID:Planning-Biblio-Calendar';
        $ical[] = 'VERSION:2.0';
        $ical[] = 'METHOD:PUBLISH';
        $ical[] = 'BEGIN:VTIMEZONE';
        $ical[] = 'TZID:' . date('e');
        $ical[] = 'BEGIN:STANDARD';
        $ical[] = 'DTSTART:16010101T030000';
        $ical[] = 'TZOFFSETTO:+0100';
        $ical[] = 'TZOFFSETFROM:+0200';
        $ical[] = 'RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=10;BYDAY=-1SU';
        $ical[] = 'TZNAME:' . date('T');
        $ical[] = 'END:STANDARD';
        $ical[] = 'BEGIN:DAYLIGHT';
        $ical[] = 'DTSTART:16010101T020000';
        $ical[] = 'TZOFFSETTO:+0200';
        $ical[] = 'TZOFFSETFROM:+0100';
        $ical[] = 'RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=3;BYDAY=-1SU';
        $ical[] = 'TZNAME:' . date('T');
        $ical[] = 'END:DAYLIGHT';
        $ical[] = 'END:VTIMEZONE';

        // Complète le tableau $ical
        foreach ($data as $elem) {
            $params = [
                'id' => $elem['userId'],
                'start' => $elem['start'],
                'end' => $elem['end'],
                'site' => $elem['siteName'],
                'siteId' => $elem['site'],
                'floor' => $elem['floor'],
                'position' => $elem['position'],
                'positionId' => $elem['poste'],
                'organizer' => $elem['organizer'],
                'lastModified' => $elem['lastModified'],
                'createdAt' => $elem['createdAt'],
                'reason' => $elem['reason'],
                'comment' => $elem['comment'],
                'status' => $elem['status'],
            ];

            $event = \CJICS::createIcsEvent($params);
            $ical = array_merge($ical, $event);
        }

        $ical[] = 'END:VCALENDAR';

        $ical = implode("\n", $ical);

        $response = new Response();
        $response->setContent($ical);
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'inline; filename=calendar.ics');

        return $response;
    }
}
