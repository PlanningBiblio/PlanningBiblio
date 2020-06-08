<?php

namespace App\PlanningBiblio;

use App\Model\Agent;
use App\PlanningBiblio\OAuth;
use App\PlanningBiblio\Logger;
use App\PlanningBiblio\MSCalendarUtils;
use Unirest\Request;

require_once __DIR__."/../../public/absences/class.absences.php";
require_once(__DIR__ . '/../../public/include/config.php');
require_once(__DIR__ . '/../../public/include/function.php');

class MSGraphClient
{

    private CONST BASE_URL = 'https://graph.microsoft.com/v1.0';
    private CONST CAL_NAME = 'PlanningBiblio-Absences-';
    // Start year for full scan
    private CONST START_YEAR = '2000';

    private $absences;
    private $calendarUtils;
    private $csrftoken;
    private $dbprefix;
    private $entityManager;
    private $full;
    private $graphUsers;
    private $incomingEvents;
    private $localEvents;
    private $logger;
    private $login_suffix;
    private $oauth;
    private $reason_name;

    public function __construct($entityManager, $tenantid, $clientid, $clientsecret, $full)
    {
        $tokenURL = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/token";
        $authURL = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/authorize";
        $options = [
             'scope' => 'https://graph.microsoft.com/.default'
        ];
        $this->absences = new \absences();
        $this->logger = new Logger($entityManager);
        $this->oauth = new OAuth($this->logger, $clientid, $clientsecret, $tokenURL, $authURL, $options);
        $this->msCalendarUtils = new MSCalendarUtils();
        $this->entityManager = $entityManager;
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->reason_name = $_ENV['MS_GRAPH_REASON_NAME'] ?? 'Outlook';
        $this->login_suffix = $_ENV['MS_GRAPH_LOGIN_SUFFIX'] ?? null;
        $this->csrftoken = CSRFToken();
        $this->full = $full;
    }

    public function retrieveEvents() {
        $this->log("Start absences import from MS Graph Calendars");
        $this->log("full scan: $this->full");
        $this->getIncomingEvents();
        if (!$this->incomingEvents) {
            $this->log("No suitable users found for import");
        } else {
            $this->getLocalEvents();
            $this->deleteEvents();
            $this->insertOrUpdateEvents();
        }
        $this->log("End absences import from MS Graph Calendars");
    }

    private function getIncomingEvents() {
        $this->incomingEvents = array();
        $this->graphUsers = array();
        $users = $this->entityManager->getRepository(Agent::class)->findBy(['supprime' => 0]);
        foreach ($users as $user) {
            if ($this->isGraphUser($user)) {
                array_push($this->graphUsers, $user->id());
                $currentYear = date("Y");
                if ($this->full) {
                    $yearCount = 0;
                    while (self::START_YEAR + $yearCount <= $currentYear) {
                        $from = (self::START_YEAR + $yearCount) . "-01-01";
                        $to = (self::START_YEAR + $yearCount) . "-12-31";
                        $this->log("Getting events from $from to $to for user ". $user->login());
                        $response = $this->getCalendarView($user, $from, $to);
                        $this->addToIncomingEvents($user, $response);
                        $yearCount++;
                    }
                } else {
                    $from = date("Y-m-d");
                    $to = date("Y-m-d", strtotime($from. ' + 365 days'));
                    $this->log("Getting events from $from to $to for user ". $user->login());
                    $response = $this->getCalendarView($user, $from, $to);
                    $this->addToIncomingEvents($user, $response);
                }
            }
        }
    }

    private function addToIncomingEvents($user, $response) {
        #TODO: Add pagination (see @odata.nextLink)
        foreach ($response->body->value as $event) {
            $this->incomingEvents[$event->iCalUId]['plb_id'] = $user->id();
            $this->incomingEvents[$event->iCalUId]['plb_login'] = $user->login();
            $this->incomingEvents[$event->iCalUId]['last_modified'] = $event->lastModifiedDateTime;
            $this->incomingEvents[$event->iCalUId]['event'] = $event;
        }
    }

    private function getLocalEvents() {
        $usersSQLIds = join(',', $this->graphUsers);
        $query = "SELECT * FROM " . $this->dbprefix . "absences WHERE motif='" . $this->reason_name . "' AND perso_id IN($usersSQLIds)";
        $statement = $this->entityManager->getConnection()->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
        $this->localEvents = array();
        foreach ($results as $localEvent) {
            $this->localEvents[$localEvent['external_ical_key']] = $localEvent;
        }
    }

    private function getCalendarView($user, $from, $to) {
        $login = $user->login();
        $response = $this->sendGet("/users/$login" . $this->login_suffix . '/calendar/calendarView?startDateTime=' . $from . 'T00:00:00.0000000&endDateTime=' . $to . 'T00:00:00.0000000&$top=1000');
        if ($response->code == 200) {
            return $response;
        } else {
            $this->log("Response: $response->code");
            $this->log($response->raw_body);
        }
        return false;
    }

    private function isGraphUser($user) {
        $login = $user->login();
        $response = $this->sendGet("/users/$login" . $this->login_suffix . '/calendar');
        if ($response->code == 200) {
            return true;
        }
        return false;
    }

    private function deleteEvents() {
        // The SQL calls in this function should be replaced by doctrine calls when available
        $query = "DELETE FROM " . $this->dbprefix . "absences WHERE ical_key=:ical_key LIMIT 1";
        $statement = $this->entityManager->getConnection()->prepare($query);
        foreach ($this->localEvents as $ical_key => $localEvent) {
            if (!array_key_exists($ical_key, $this->incomingEvents)) {
                if ($localEvent['uid']) {
                    $this->log("deleting uid " . $localEvent['uid'] .  " user " . $localEvent['perso_id'] . " recurring event " . $localEvent['ical_key']);
                    $a = new \absences();
                    $a->CSRFToken = $this->csrftoken;
                    $a->perso_id = $localEvent['perso_id'];
                    $a->uid = $localEvent['uid'];
                    $a->ics_delete_event();
                } else {
                    $this->log("deleting user " . $localEvent['perso_id'] . " event " . $localEvent['ical_key']);
                    $statement->bindParam(':ical_key', $localEvent['ical_key']);
                    $statement->execute();
                }
            }
        }
    }

    private function addOrUpdateRecurrentEvent($event, $perso_id, $add = false) {
        $rrule = $this->msCalendarUtils->recurrenceToRRule($event->recurrence);
        $a = new \absences();
        $a->CSRFToken = $this->csrftoken;
        $a->cal_name = self::CAL_NAME . $perso_id . '-' . md5($event->iCalUId);
        $a->perso_id = $perso_id;
        $a->commentaires = $event->subject;
        $a->debut = $this->formatDate($event->start, "d/m/Y");
        // $dtstamp = gmdate('Ymd\THis\Z');
        $a->fin = $this->formatDate($event->end, "d/m/Y");
        $a->hre_debut = $this->formatDate($event->start, "H:i:s");
        $a->hre_fin = $this->formatDate($event->end, "H:i:s");
        $a->motif = $this->reason_name;
        $a->rrule = $rrule;
        $a->valide_n2 = 1;
        $a->last_modified = $event->lastModifiedDateTime;
        $a->uid = substr($event->iCalUId, 0, 50);
        $a->external_ical_key = $event->iCalUId;
        $a->perso_ids = array($perso_id);
        if ($add) {
            $a->ics_add_event();
        } else {
            $a->ics_update_event();
        }
}

    private function insertOrUpdateEvents() {
        // The SQL calls in this function should be replaced by doctrine calls when available
        foreach ($this->incomingEvents as $eventArray) {
            $incomingEvent = $eventArray['event'];
            $rrule = null;
            if ($incomingEvent->type == "occurrence") {
                $response = $this->sendGet("/users/" . $eventArray['plb_login'] . $this->login_suffix . '/calendar/events/' . $incomingEvent->seriesMasterId);
                $rrule = $this->msCalendarUtils->recurrenceToRRule($response->body->recurrence);
            }
            if (array_key_exists($incomingEvent->iCalUId, $this->localEvents)) {
                // Event modification
                $localEvent = $this->localEvents[$incomingEvent->iCalUId];
                if ($incomingEvent->lastModifiedDateTime != $localEvent['last_modified']) {
                    $this->log("updating user " . $eventArray['plb_id'] . " event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                    $query = "UPDATE " . $this->dbprefix . "absences SET debut=:debut, fin=:fin, motif=:motif, commentaires=:commentaires, last_modified=:last_modified, rrule=:rrule WHERE external_ical_key=:external_ical_key LIMIT 1";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $statement->execute(array(
                        'debut'             => $this->formatDate($incomingEvent->start),
                        'fin'               => $this->formatDate($incomingEvent->end),
                        'motif'             => $this->reason_name,
                        'commentaires'      => $incomingEvent->subject,
                        'last_modified'     => $incomingEvent->lastModifiedDateTime,
                        'external_ical_key' => $incomingEvent->iCalUId,
                        'rrule'             => $rrule
                    ));
                }
            } else {
                // Event insertion
                $this->log("inserting user " . $eventArray['plb_id'] . " event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                $query = "INSERT INTO " . $this->dbprefix . "absences ";
                $query .= "( perso_id,  debut,  fin,  motif, motif_autre, commentaires, valide, etat, demande, cal_name,  ical_key, external_ical_key, last_modified, rrule) VALUES ";
                $query .= "(:perso_id, :debut, :fin, :motif, '',         :commentaires, 9999,   '',   NOW(),  :cal_name, :ical_key, :external_ical_key, :last_modified, :rrule)";
                $statement = $this->entityManager->getConnection()->prepare($query);
                $statement->execute(array(
                    'perso_id'      => $eventArray['plb_id'],
                    'debut'         => $this->formatDate($incomingEvent->start),
                    'fin'           => $this->formatDate($incomingEvent->end),
                    'motif'         => $this->reason_name,
                    'commentaires'  => $incomingEvent->subject,
                    'cal_name'      => self::CAL_NAME . $eventArray['plb_id'] . '-' . md5($incomingEvent->iCalUId),
                    'ical_key'      => $incomingEvent->iCalUId,
                    'external_ical_key' => $incomingEvent->iCalUId,
                    'last_modified' => $incomingEvent->lastModifiedDateTime,
                    'rrule'         => $rrule
                ));
            }
        }
    }

    private function sendGet($request) {
        echo "$request\n";
        $token = $this->oauth->getToken();
        $headers['Authorization'] = "Bearer $token";
        $response = \Unirest\Request::get(self::BASE_URL . $request, $headers);
        return $response;
    }

    private function log($message) {
        $this->logger->log($message, get_class($this));
    }

    private function formatDate($graphdate, $format = "Y-m-d H:i:s") {
        $time = strtotime($graphdate->dateTime . $graphdate->timeZone);
        return date($format, $time);
    }
}
