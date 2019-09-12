<?php

namespace App\PlanningBiblio;

use App\Model\Agent;
use App\PlanningBiblio\OAuth;
use Unirest\Request;

class MSGraphClient
{

    private $baseUrl = 'https://graph.microsoft.com/v1.0';
    private $oauth;
    private $entityManager;
    private $graphUsersEvents;
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
    }

    public function retrieveEvents() {
        $this->getGraphUsersEvents();
        $this->processEvents();
    }

    private function getGraphUsersEvents() {
        $this->graphUsersEvents = array();
        $this->graphUsers = array();
        $users = $this->entityManager->getRepository(Agent::class)->findAll();
        foreach ($users as $user) {
            $response = $this->isGraphUser($user);
            if ($response) {
                echo $user->mail() . " is graph user\n";
               array_push($this->graphUsers, $user->id());
               array_push($this->graphUsersEvents, $response->body);
                //var_dump($response);
            }
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

    private function processEvents() {
        // The SQL calls in this function should be replaced by doctrine calls when available

        $usersSQLIds = join(',', $this->graphUsers);
        $prefix = $_ENV['DATABASE_PREFIX'];

        $query = "SELECT * FROM ${prefix}absences WHERE cal_name='ms_graph' AND perso_id IN($usersSQLIds)";
        
        $statement = $this->entityManager->getConnection()->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
        $localEvents = array();
        $existingICalKeys = array();
        foreach ($results as $localEvent) {
            array_push($localEvents, $localEvent); 
            if ($localEvent['ical_key']) {
                $existingICalKeys[$localEvent['ical_key']] = $localEvent['perso_id'];
            }
        }

        // For each user,
        // Run through all events, and add the graph event to local events if needed
        foreach ($this->graphUsersEvents as $events) {
            foreach ($events->value as $event) {

                if (array_key_exists($event->iCalUId, $existingICalKeys)) {
                    echo "update\n";
                    // TODO: Ne pas faire l'update si la date est la même
                    $query = "UPDATE ${prefix}absences SET last_modified=:last_modified, motif=:motif WHERE ical_key=:ical_key";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $statement->bindParam(':ical_key', $event->iCalUId);
                    $statement->bindParam(':last_modified', $event->lastModifiedDateTime);
                    $statement->bindParam(':motif', $event->subject);
                    $statement->execute();
                } else {
                    echo "insert\n";
                    $query = "INSERT INTO ${prefix}absences (perso_id, cal_name, ical_key, last_modified, motif) VALUES (:perso_id, :cal_name, :ical_key, :last_modified, :motif)";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $perso_id = 3;
                    $cal_name = 'ms_graph';
                    $statement->bindParam(':perso_id', $perso_id);
                    $statement->bindParam(':cal_name', $cal_name);
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
        $response = \Unirest\Request::get($this->baseUrl . $request, $headers);
        return $response;
    }

}
