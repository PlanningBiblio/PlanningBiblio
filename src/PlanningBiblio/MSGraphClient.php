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
    private $incomingICalKeys;
    private $localEvents;
    private $localICalKeys;
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
               array_push($this->incomingEvents, $response->body);
                //var_dump($response);
            }
        }
        //TODO: Un seul tableau avec icalkey = event ?
        // Getting all ical keys from graph events
        $this->incomingICalKeys = array();
        foreach ($this->incomingEvents as $events) {
            foreach ($events->value as $event) {
               $this->incomingICalKeys[$event->iCalUId] = 1; 
            }
        }
    }

    private function getLocalEvents() {
        // Getting events and ical keys from PLB
        $usersSQLIds = join(',', $this->graphUsers);
        $query = "SELECT * FROM " . $this->dbprefix . "absences WHERE cal_name='" . self::CAL_NAME . "' AND perso_id IN($usersSQLIds)";
        $statement = $this->entityManager->getConnection()->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
        $this->localEvents = array();
        $this->localICalKeys = array();
        foreach ($results as $localEvent) {
            array_push($this->localEvents, $localEvent); 
            $this->localICalKeys[$localEvent['ical_key']] = $localEvent['perso_id'];
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
        foreach ($this->localEvents as $localEvent) {
            if (!array_key_exists($localEvent['ical_key'], $this->incomingICalKeys)) {
                echo "delete " . $localEvent['ical_key'] . "\n";
                $statement->bindParam(':ical_key', $localEvent['ical_key']);
                $statement->execute();
            }
        }
    }

    private function insertOrUpdateEvents() {
        // The SQL calls in this function should be replaced by doctrine calls when available

        // For each user,
        // Run through all events, and add the graph event to local events if needed
        foreach ($this->incomingEvents as $events) {
            foreach ($events->value as $event) {

                if (array_key_exists($event->iCalUId, $this->localICalKeys)) {
                    echo "update\n";
                    // TODO: Ne pas faire l'update si la date est la même
                    $query = "UPDATE " . $this->dbprefix . "absences SET last_modified=:last_modified, motif=:motif WHERE ical_key=:ical_key";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $statement->bindParam(':ical_key', $event->iCalUId);
                    $statement->bindParam(':last_modified', $event->lastModifiedDateTime);
                    $statement->bindParam(':motif', $event->subject);
                    $statement->execute();
                } else {
                    echo "insert\n";
                    $query = "INSERT INTO " . $this->dbprefix . "absences (perso_id, cal_name, ical_key, last_modified, motif) VALUES (:perso_id, :cal_name, :ical_key, :last_modified, :motif)";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $perso_id = 3;
                    $statement->bindParam(':perso_id', $perso_id);
                    $statement->bindParam(':cal_name', self::CAL_NAME);
                    $statement->bindParam(':ical_key', $event->iCalUId);
                    $statement->bindParam(':last_modified', $event->lastModifiedDateTime);
                    $statement->bindParam(':motif', $event->subject);
                    $statement->execute();
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

}
