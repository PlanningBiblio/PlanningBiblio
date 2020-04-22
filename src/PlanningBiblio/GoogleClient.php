<?php

namespace App\PlanningBiblio;

use App\Model\Agent;
use App\PlanningBiblio\Logger;
use Unirest\Request;
use Google_Client;
use Google_Service_Calendar;

require_once __DIR__."/../../public/absences/class.absences.php";
require_once(__DIR__ . '/../../public/include/config.php');
require_once(__DIR__ . '/../../public/include/function.php');

class GoogleClient
{

    private CONST CAL_NAME = 'PlanningBiblio-Absences-';

    private $absences;
    private $csrftoken;
    private $client;
    private $dbprefix;
    private $entityManager;
    private $graphUsers;
    private $incomingEvents;
    private $localEvents;
    private $logger;
    private $reason_name;

    public function __construct($entityManager)
    {
        $this->absences = new \absences();
        $this->logger = new Logger($entityManager);
        $this->entityManager = $entityManager;
        $this->dbprefix = $_ENV['DATABASE_PREFIX'];
        $this->reason_name = 'Google';
        $this->csrftoken = CSRFToken();
        $this->client = $this->getClient();
    }

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
public function test() {
    $client = $this->client;
    $service = new Google_Service_Calendar($client);

    // Print the next 10 events on the user's calendar.
    $calendarId = 'primary';
    $optParams = array(
      'maxResults' => 10,
      'orderBy' => 'startTime',
      'singleEvents' => true,
      'timeMin' => date('c'),
    );
    $results = $service->events->listEvents($calendarId, $optParams);
    $events = $results->getItems();

    if (empty($events)) {
        print "No upcoming events found.\n";
    } else {
        print "Upcoming events:\n";
        foreach ($events as $event) {
            $start = $event->start->dateTime;
            if (empty($start)) {
                $start = $event->start->date;
            }
            printf("%s (%s)\n", $event->getSummary(), $start);
        }
    }
}
/*
    public function retrieveEvents() {
        $this->log("Start absences import from MS Graph Calendars");
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
        $query = "SELECT * FROM " . $this->dbprefix . "absences WHERE motif='" . $this->reason_name . "' AND perso_id IN($usersSQLIds)";
        $statement = $this->entityManager->getConnection()->prepare($query);
        $statement->execute();
        $results = $statement->fetchAll();
        $this->localEvents = array();
        foreach ($results as $localEvent) {
            $this->localEvents[$localEvent['external_ical_key']] = $localEvent;
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
            if (array_key_exists($incomingEvent->iCalUId, $this->localEvents)) {
                // Event modification
                $localEvent = $this->localEvents[$incomingEvent->iCalUId];
                if ($incomingEvent->lastModifiedDateTime != $localEvent['last_modified']) {
                    if ($incomingEvent->recurrence) {
                        $this->log("updating user " . $eventArray['plb_id'] . " recurring event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                        $this->addOrUpdateRecurrentEvent($incomingEvent, $eventArray['plb_id']);
                    } else {
                        $this->log("updating user " . $eventArray['plb_id'] . " event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                        $query = "UPDATE " . $this->dbprefix . "absences SET debut=:debut, fin=:fin, motif=:motif, commentaires=:commentaires, last_modified=:last_modified WHERE external_ical_key=:external_ical_key LIMIT 1";
                        $statement = $this->entityManager->getConnection()->prepare($query);
                        $statement->execute(array(
                            'debut'             => $this->formatDate($incomingEvent->start),
                            'fin'               => $this->formatDate($incomingEvent->end),
                            'motif'             => $this->reason_name,
                            'commentaires'      => $incomingEvent->subject,
                            'last_modified'     => $incomingEvent->lastModifiedDateTime,
                            'external_ical_key' => $incomingEvent->iCalUId
                        ));
                    }
                }
            } else {
                // Event insertion
                if ($incomingEvent->recurrence) {
                    $this->log("inserting user " . $eventArray['plb_id'] . " recurring event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                    $this->addOrUpdateRecurrentEvent($incomingEvent, $eventArray['plb_id'], true);
                } else {
                    $this->log("inserting user " . $eventArray['plb_id'] . " event '" . $incomingEvent->subject . "' " . $incomingEvent->iCalUId);
                    $query = "INSERT INTO " . $this->dbprefix . "absences ";
                    $query .= "( perso_id,  debut,  fin,  motif, motif_autre, commentaires, valide, etat, demande, cal_name,  ical_key, external_ical_key, last_modified) VALUES ";
                    $query .= "(:perso_id, :debut, :fin, :motif, '',         :commentaires, 9999,   '',   NOW(),  :cal_name, :ical_key, :external_ical_key, :last_modified)";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $statement->execute(array(
                        'perso_id'      => $eventArray['plb_id'],
                        'debut'         => $this->formatDate($incomingEvent->start),
                        'fin'           => $this->formatDate($incomingEvent->end),
                        'motif'         => $this->reason_name,
                        'commentaires'  => $incomingEvent->subject,
                        'cal_name'      => self::CAL_NAME . $eventArray['plb_id'] . '-' . md5($incomingEvent->iCalUId),
                        'ical_key'      => $incomingEvent->iCalUId,
                        'external_ical_key'      => $incomingEvent->iCalUId,
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
*/
}
