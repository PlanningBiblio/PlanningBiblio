<?php

namespace App\PlanningBiblio;

use App\Model\Agent;
use App\PlanningBiblio\OAuth;
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

    public function __construct($entityManager, $tenantid, $clientid, $clientsecret)
    {
        $tokenURL = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/token";
        $authURL = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/authorize";
        $options = [
             'scope' => 'https://graph.microsoft.com/.default'
        ];
        $this->oauth = new OAuth($clientid, $clientsecret, $tokenURL, $authURL, $options);
        $this->entityManager = $entityManager;
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
    }

    public function retrieveEvents() {
        $this->getIncomingEvents();
        $this->getLocalEvents();
        $this->deleteEvents();
        $this->insertOrUpdateEvents();
    }

    private function getIncomingEvents() {
        $this->incomingEvents = array();
        $this->graphUsers = array();
        $users = $this->entityManager->getRepository(Agent::class)->findAll();
        foreach ($users as $user) {
            $response = $this->isGraphUser($user);
            if ($response) {
                echo $user->mail() . " is graph user\n";
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
                echo "delete " . $localEvent['ical_key'] . "\n";
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
                    echo "update\n";
                    $query = "UPDATE " . $this->dbprefix . "absences SET debut=:debut, fin=:fin, motif=:motif, commentaires=:commentaires, last_modified=:last_modified WHERE ical_key=:ical_key LIMIT 1";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $statement->bindParam(':debut', $incomingEvent->start->dateTime);
                    $statement->bindParam(':fin', $incomingEvent->end->dateTime);
                    $statement->bindParam(':motif', $incomingEvent->subject);
                    $statement->bindParam(':commentaires', $incomingEvent->bodyPreview);
                    $statement->bindParam(':last_modified', $incomingEvent->lastModifiedDateTime);
                    $statement->bindParam(':ical_key', $incomingEvent->iCalUId);
                    $statement->execute();
                }
            } else {
                echo "insert\n";
                $query = "INSERT INTO " . $this->dbprefix . "absences ";
                $query .= "( perso_id,  debut,  fin,  motif,  commentaires, demande, cal_name,  ical_key,  last_modified) VALUES ";
                $query .= "(:perso_id, :debut, :fin, :motif, :commentaires, NOW(), :cal_name, :ical_key, :last_modified)";
                $statement = $this->entityManager->getConnection()->prepare($query);
                $perso_id = $eventArray['plb_id'];
                $cal_name = self::CAL_NAME;
                $statement->bindParam(':perso_id', $perso_id);
                $statement->bindParam(':debut', $incomingEvent->start->dateTime);
                $statement->bindParam(':fin', $incomingEvent->end->dateTime);
                $statement->bindParam(':motif', $incomingEvent->subject);
                $statement->bindParam(':commentaires', $incomingEvent->bodyPreview);
                $statement->bindParam(':cal_name', $cal_name);
                $statement->bindParam(':ical_key', $incomingEvent->iCalUId);
                $statement->bindParam(':last_modified', $incomingEvent->lastModifiedDateTime);
                $statement->execute();
            }
        }
    }

    private function sendGet($request) {
        $token = $this->oauth->getToken();
        $headers['Authorization'] = "Bearer $token";
        $response = \Unirest\Request::get(self::BASE_URL . $request, $headers);
        return $response;
    }

}
