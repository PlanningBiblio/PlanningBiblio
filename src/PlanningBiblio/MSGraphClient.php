<?php

namespace App\PlanningBiblio;

use App\Model\Agent;
use App\PlanningBiblio\OAuth;
use App\PlanningBiblio\Logger;
use Unirest\Request;

class MSGraphClient
{

    private CONST BASE_URL = 'https://graph.microsoft.com/v1.0';
    private CONST CAL_NAME = 'ms_graph';

    private $oauth;
    private $dbprefix;
    private $entityManager;
    private $incomingEvents;
    private $localEvents;
    private $graphUsers;
    private $logger;
    private $reason_name;

    public function __construct($entityManager, $tenantid, $clientid, $clientsecret)
    {
        $tokenURL = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/token";
        $authURL = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/authorize";
        $options = [
             'scope' => 'https://graph.microsoft.com/.default'
        ];
        $this->logger = new Logger($entityManager);
        $this->oauth = new OAuth($this->logger, $clientid, $clientsecret, $tokenURL, $authURL, $options);
        $this->entityManager = $entityManager;
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->reason_name = array_key_exists('MS_GRAPH_REASON_NAME', $_ENV) ? $_ENV['MS_GRAPH_REASON_NAME'] : 'Outlook';
    }

    public function retrieveEvents() {
        $this->log("Start absences import from MS Graph Calendars");
        $this->getIncomingEvents();
        $this->getLocalEvents();
        $this->deleteEvents();
        $this->insertOrUpdateEvents();
        $this->log("End absences import from MS Graph Calendars");
    }

    private function getIncomingEvents() {
        $this->incomingEvents = array();
        $this->graphUsers = array();
        $users = $this->entityManager->getRepository(Agent::class)->findAll();
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
        $email = $user->mail();
        if (!$email) { return false; }
        $response = $this->sendGet("/users/$email/calendar/events");
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

    private function insertOrUpdateEvents() {
        // The SQL calls in this function should be replaced by doctrine calls when available
        foreach ($this->incomingEvents as $eventArray) {
            $incomingEvent = $eventArray['event'];
            if (array_key_exists($incomingEvent->iCalUId, $this->localEvents)) {
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
                $this->log("inserting user " . $eventArray['plb_id'] . " event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                $query = "INSERT INTO " . $this->dbprefix . "absences ";
                $query .= "( perso_id,  debut,  fin,  motif, motif_autre, commentaires, etat, demande, cal_name,  ical_key,  last_modified) VALUES ";
                $query .= "(:perso_id, :debut, :fin, :motif, '',         :commentaires, '',   NOW(),  :cal_name, :ical_key, :last_modified)";
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

    private function sendGet($request) {
        $token = $this->oauth->getToken();
        $headers['Authorization'] = "Bearer $token";
        $response = \Unirest\Request::get(self::BASE_URL . $request, $headers);
        return $response;
    }

    private function log($message) {
        $this->logger->log($message, get_class($this));
    }

    private function formatDate($graphdate) {
        $time = strtotime($graphdate->dateTime . $graphdate->timeZone);
        return date("Y-m-d H:i:s", $time);
    }

}
