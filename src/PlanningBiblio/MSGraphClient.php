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
    private CONST CAL_NAME = 'ms_graph';

    private $absences;
    private $calendarUtils;
    private $csrftoken;
    private $dbprefix;
    private $entityManager;
    private $graphUsers;
    private $incomingEvents;
    private $localEvents;
    private $logger;
    private $login_suffix;
    private $oauth;
    private $reason_name;

    public function __construct($entityManager, $tenantid, $clientid, $clientsecret)
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
    }

    public function retrieveEvents() {
        $this->log("Start absences import from MS Graph Calendars");
        $this->getIncomingEvents();
        //die();
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
            $response = $this->isGraphUser($user);
            if ($response) {
                array_push($this->graphUsers, $user->id());
                foreach ($response->body->value as $event) {
                    $this->incomingEvents[$event->iCalUId]['plb_id'] = $user->id();
                    $this->incomingEvents[$event->iCalUId]['last_modified'] = $event->lastModifiedDateTime;
                    $this->incomingEvents[$event->iCalUId]['event'] = $event;
                }
            }
        }
    }

    private function getLocalEvents() {
        $usersSQLIds = join(',', $this->graphUsers);
        $query = "SELECT * FROM " . $this->dbprefix . "absences WHERE cal_name='" . self::CAL_NAME . "' AND perso_id IN($usersSQLIds)";
        $statement = $this->entityManager->getConnection()->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
        $this->localEvents = array();
        foreach ($results as $localEvent) {
            $this->localEvents[$localEvent['ical_key']] = $localEvent;
        }
    }

    private function isGraphUser($user) {
        $login = $user->login();
        $response = $this->sendGet("/users/$login" . $this->login_suffix . "/calendar/events");
        if ($response->code == 200) {
            return $response;
        }
        return false;
    }

    private function deleteEvents() {
        // The SQL calls in this function should be replaced by doctrine calls when available
        $query = "DELETE FROM " . $this->dbprefix . "absences WHERE ical_key=:ical_key LIMIT 1";
        $statement = $this->entityManager->getConnection()->prepare($query);
        foreach ($this->localEvents as $ical_key => $localEvent) {
            if (!array_key_exists($ical_key, $this->incomingEvents)) {
                $this->log("deleting user " . $localEvent['perso_id'] . " event " . $localEvent['ical_key']);
                $statement->bindParam(':ical_key', $localEvent['ical_key']);
                $statement->execute();
            }
        }
    }

    private function addRecurrentEvent($event, $perso_id) {
        $rrule = $this->msCalendarUtils->recurrenceToRRule($event->recurrence);
        $a = new \absences();
        $a->CSRFToken = $this->csrftoken;
        $a->perso_id = $perso_id;
        $a->commentaires = $event->subject;
        $a->debut = $this->formatDate($event->start, "d/m/Y");
        $dtstamp = gmdate('Ymd\THis\Z');
        $a->dtstamp = $dtstamp;
        $a->fin = $this->formatDate($event->end, "d/m/Y");
        $a->hre_debut = $this->formatDate($event->start, "H:i:s");
        $a->hre_fin = $this->formatDate($event->end, "H:i:s");
        $a->motif = $this->reason_name;
        $a->rrule = $rrule;
        $a->valide_n2 = 1;
        $a->uid = $event->iCalUId;
        $a->cal_name = self::CAL_NAME;
        $a->ics_add_event();
}

    private function insertOrUpdateEvents() {
        // The SQL calls in this function should be replaced by doctrine calls when available
        foreach ($this->incomingEvents as $eventArray) {
            $incomingEvent = $eventArray['event'];
            if (array_key_exists($incomingEvent->iCalUId, $this->localEvents)) {
                // Event modification
                $localEvent = $this->localEvents[$incomingEvent->iCalUId];
                if ($incomingEvent->lastModifiedDateTime != $localEvent['last_modified']) {

                    $this->log("updating user " . $eventArray['plb_id'] . " event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                    $query = "UPDATE " . $this->dbprefix . "absences SET debut=:debut, fin=:fin, motif=:motif, commentaires=:commentaires, last_modified=:last_modified WHERE ical_key=:ical_key LIMIT 1";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $statement->execute(array(
                        'debut'         => $this->formatDate($incomingEvent->start),
                        'fin'           => $this->formatDate($incomingEvent->end),
                        'motif'         => $this->reason_name,
                        'commentaires'  => $incomingEvent->subject,
                        'ical_key'      => $incomingEvent->iCalUId,
                        'last_modified' => $incomingEvent->lastModifiedDateTime
                    ));
                }
            } else {
                // Event insertion
                if ($incomingEvent->recurrence) {
                    $this->log("inserting user " . $eventArray['plb_id'] . " recurring event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                    $this->addRecurrentEvent($incomingEvent, $eventArray['plb_id']);
                } else {
                    $this->log("inserting user " . $eventArray['plb_id'] . " event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                    $query = "INSERT INTO " . $this->dbprefix . "absences ";
                    $query .= "( perso_id,  debut,  fin,  motif, motif_autre, commentaires, valide, etat, demande, cal_name,  ical_key,  last_modified) VALUES ";
                    $query .= "(:perso_id, :debut, :fin, :motif, '',         :commentaires, 9999,   '',   NOW(),  :cal_name, :ical_key, :last_modified)";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $statement->execute(array(
                        'perso_id'      => $eventArray['plb_id'],
                        'debut'         => $this->formatDate($incomingEvent->start),
                        'fin'           => $this->formatDate($incomingEvent->end),
                        'motif'         => $this->reason_name,
                        'commentaires'  => $incomingEvent->subject,
                        'cal_name'      => self::CAL_NAME,
                        'ical_key'      => $incomingEvent->iCalUId,
                        'last_modified' => $incomingEvent->lastModifiedDateTime
                    ));
                }
            }
        }
    }

    private function sendGet($request) {
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
