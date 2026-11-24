<?php

namespace App\Planno;

use App\Entity\Agent;
use App\Entity\Config;
use App\Planno\OAuth;
use App\Planno\MSCalendarUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Unirest\Request;

class MSGraphClient
{
    use \App\Traits\LoggerTrait;

    private $base_url = 'https://graph.microsoft.com/v1.0';
    private $cal_name = 'MSGraph-';
    // Start year for full scan
    private $start_year = '2000';

    private $msCalendarUtils;
    private $dbprefix;
    private $entityManager;
    private $full;
    private $graphUsers;
    private $incomingEvents;
    private $localEvents;
    private $login_suffix;
    private $oauth;
    private $reason_name;
    private $ignoredStatuses;
    private $stdout;
    private $user_id;
    // Cache: Planno login => Graph user key (object id or URL-encoded UPN)
    private $graphKeys = [];
    // Disabled after the first lookup refused by Graph (e.g. missing User.Read.All)
    private $mailLookupEnabled = true;

    public function __construct($entityManager, $tenantid, $clientid, $clientsecret, $full, $stdout, $user_id)
    {
        $tokenURL = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/token";
        $authURL = "https://login.microsoftonline.com/$tenantid/oauth2/v2.0/authorize";
        $options = [
             'scope' => 'https://graph.microsoft.com/.default'
        ];

        $config = $entityManager->getRepository(Config::class)->findBy(
            array('categorie' => 'Microsoft Graph API'),
        );

        $config = new ArrayCollection($config);

        $absenceReason = $config->filter(function($element) {return $element->getName() == 'MSGraph-AbsenceReason';})->first()->getValue();
        $loginSuffix = $config->filter(function($element) {return $element->getName() == 'MSGraph-LoginSuffix';})->first()->getValue();
        $ignoredStatuses = $config->filter(function($element) {return $element->getName() == 'MSGraph-IgnoredStatuses';})->first()->getValue();

        $this->oauth = new OAuth($clientid, $clientsecret, $tokenURL, $authURL, $options, $stdout);
        $this->msCalendarUtils = new MSCalendarUtils();
        $this->entityManager = $entityManager;
        $this->dbprefix = '';
        $this->reason_name = $absenceReason ?? 'Outlook';
        $this->login_suffix = $loginSuffix ?? null;
        $this->ignoredStatuses = !empty($ignoredStatuses) ? explode(';', $ignoredStatuses) : ['free', 'tentative'];
        $this->full = $full;
        $this->stdout = $stdout;
        $this->user_id = $user_id;
    }

    public function retrieveEvents(): void {
        $this->logMessage("Start absences import from MS Graph Calendars");
        $this->logMessage("full scan: $this->full");
        $this->getIncomingEvents();
        if (!empty($this->graphUsers)) {
            $this->getLocalEvents();
            $this->deleteEvents();
            $this->insertOrUpdateEvents();
        }
        $this->logMessage("End absences import from MS Graph Calendars");
    }

    private function getDateRange(): array {
        $range = array();
        $today = date("Y-m-d");
        $range['from'] = date("Y-m-d", strtotime($today . '- 15 days'));
        $range['to'] = date("Y-m-d", strtotime($today . ' + 365 days'));
        return $range;
    }

    private function getIncomingEvents(): void {
        $this->incomingEvents = array();
        $this->graphUsers = array();
        if ($this->user_id) {
            $users = array($this->entityManager->find(Agent::class, $this->user_id));
        } else {
            $users = $this->entityManager->getRepository(Agent::class)->findBy(['supprime' => 0, 'check_ms_graph' => 1]);
        }
        foreach ($users as $user) {
            if (!$this->isGraphUser($user)) {
                continue;
            }

            // Keep a snapshot: if any request fails for this user, discard the
            // partial result and leave the user out of graphUsers, so that
            // deleteEvents() does not remove his local absences.
            $snapshot = $this->incomingEvents;
            $complete = true;

            foreach ($this->getRanges() as [$from, $to]) {
                $this->logMessage("Getting events from $from to $to for user " . $user->getLogin());
                $response = $this->getCalendarView($user, $from, $to);
                if (!$response || !$this->addToIncomingEvents($user, $response, $from, $to)) {
                    $complete = false;
                    break;
                }
            }

            if ($complete) {
                array_push($this->graphUsers, $user->getId());
            } else {
                $this->incomingEvents = $snapshot;
                $this->logMessage("Unable to get events for user " . $user->getLogin() . ": user skipped, local absences kept");
            }
        }
        $this->logMessage("Amount of incoming events: " . count($this->incomingEvents));
    }

    /**
     * Date ranges to scan: one range per year for a full scan,
     * otherwise the default sliding window.
     */
    private function getRanges(): array {
        if (!$this->full) {
            $range = $this->getDateRange();
            return [[$range['from'], $range['to']]];
        }

        $ranges = [];
        for ($year = (int) $this->start_year; $year <= (int) date("Y"); $year++) {
            $ranges[] = ["$year-01-01", "$year-12-31"];
        }
        return $ranges;
    }

    /**
     * Adds the events of a calendarView response (and its next pages) to incomingEvents.
     *
     * @return bool false if a page could not be retrieved
     */
    private function addToIncomingEvents($user, $response, $from, $to, $nextLink = null): bool {
        if ($nextLink) {
            $response = $this->sendGet($nextLink, true);
            if (!$response || $response->code != 200) {
                $this->logMessage("Unable to get events");
                return false;
            }
        }
        foreach ($response->body->value as $event) {
            if (($event->start->dateTime >= $from . 'T00:00:00.0000000' && $event->end->dateTime <= $to . 'T00:00:00.0000000' ) &&
                ($event->isOrganizer == true || $event->responseStatus->response == "accepted") &&
                !in_array($event->showAs, $this->ignoredStatuses) &&
                 !$this->isEventEmpty($user->getLogin(), $event->id)) {
                $this->incomingEvents[$user->getId() . $event->iCalUId]['plb_id'] = $user->getId();
                $this->incomingEvents[$user->getId() . $event->iCalUId]['plb_login'] = $user->getLogin();
                $this->incomingEvents[$user->getId() . $event->iCalUId]['last_modified'] = $event->lastModifiedDateTime;
                $this->incomingEvents[$user->getId() . $event->iCalUId]['event'] = $event;
            }
        }

        if (property_exists($response->body, '@odata.nextLink')) {
            $this->logMessage("Paginate " . $response->body->{'@odata.nextLink'});
            return $this->addToIncomingEvents($user, $response, $from, $to, $response->body->{'@odata.nextLink'});
        }

        return true;
    }

    private function isEventEmpty($login, $id): bool {
        $query = '/users/' . $this->graphKey($login) . '/events/' . $id;
        $response = $this->sendGet($query);
        return !$response || $response->code != 200;
    }

    private function getLocalEvents(): void {
        $usersSQLIds = implode(',', $this->graphUsers);

        if ($this->full) {
            $from = $this->start_year . "-01-01";
            $to = date("Y") . "-12-31";
        } else {
            $range = $this->getDateRange();
            $from = $range['from'];
            $to = $range['to'];
        }
        $query = "SELECT * FROM " . $this->dbprefix . "absences WHERE motif='" . $this->reason_name . "' AND perso_id IN($usersSQLIds) AND debut >= '" . $from . "' AND fin <= '" . $to . "'";
        $statement = $this->entityManager->getConnection()->prepare($query);
        $results = $statement->execute();
        $this->localEvents = array();
        foreach ($results->fetchAllAssociative() as $localEvent) {
            $this->localEvents[$localEvent['perso_id'] . $localEvent['ical_key']] = $localEvent;
        }
        $this->logMessage("Amount of local events: " . count($this->localEvents));
    }

    private function getCalendarView($user, $from, $to) {
        $login = $user->getLogin();
        $response = $this->sendGet('/users/' . $this->graphKey($login) . '/calendar/calendarView?startDateTime=' . $from . 'T00:00:00.0000000&endDateTime=' . $to . 'T00:00:00.0000000&$top=200');
        if ($response && $response->code == 200) {
            return $response;
        } 
        return false;
    }

    private function isGraphUser($user): bool {
        $login = $user->getLogin();
        $response = $this->sendGet('/users/' . $this->graphKey($login) . '/calendar');
        return $response && $response->code == 200;
    }

    private function deleteEvents(): void {
        // The SQL calls in this function should be replaced by doctrine calls when available
        $query = "DELETE FROM " . $this->dbprefix . "absences WHERE ical_key=:ical_key AND perso_id=:perso_id";
        $statement = $this->entityManager->getConnection()->prepare($query);
        foreach ($this->localEvents as $ical_key => $localEvent) {
            if (!array_key_exists($ical_key, $this->incomingEvents)) {
                $this->logMessage("deleting user " . $localEvent['perso_id'] . " event " . $localEvent['ical_key']);
                $statement->bindParam(':ical_key', $localEvent['ical_key']);
                $statement->bindParam(':perso_id', $localEvent['perso_id']);
                $statement->execute();
            }
        }
    }

    private function insertOrUpdateEvents(): void {
        // The SQL calls in this function should be replaced by doctrine calls when available
        foreach ($this->incomingEvents as $eventArray) {
            $incomingEvent = $eventArray['event'];
            $rrule = null;

            if (!$incomingEvent->iCalUId) {
                $this->logMessage("Cannot process event: ical_key is null");
                continue;
            }

            if ($incomingEvent->sensitivity == 'private') {
                $incomingEvent->subject = '[Private]';
            } elseif(!$incomingEvent->subject) {
                $incomingEvent->subject = '[Empty]';
            }

            if (array_key_exists($eventArray['plb_id'] . $incomingEvent->iCalUId, $this->localEvents)) {
                // Event modification
                $localEvent = $this->localEvents[$eventArray['plb_id'] . $incomingEvent->iCalUId];
                if ($incomingEvent->lastModifiedDateTime != $localEvent['last_modified']) {
                    if ($incomingEvent->type == "occurrence") {
                        $response = $this->sendGet('/users/' . $this->graphKey($eventArray['plb_login']) . '/calendar/events/' . $incomingEvent->seriesMasterId);
                        $rrule = $this->msCalendarUtils->recurrenceToRRule($response->body->recurrence);
                    }
                    $query = "UPDATE " . $this->dbprefix . "absences SET debut=:debut, fin=:fin, motif=:motif, commentaires=:commentaires, last_modified=:last_modified, rrule=:rrule WHERE ical_key=:ical_key AND perso_id=:perso_id LIMIT 1";
                    $statement = $this->entityManager->getConnection()->prepare($query);
                    $statement->execute(array(
                        'debut'             => $this->formatDate($incomingEvent->start),
                        'fin'               => $this->formatDate($incomingEvent->end),
                        'motif'             => $this->reason_name,
                        'commentaires'      => $incomingEvent->subject,
                        'last_modified'     => $incomingEvent->lastModifiedDateTime,
                        'rrule'             => $rrule,
                        'ical_key'          => $incomingEvent->iCalUId,
                        'perso_id'          => $eventArray['plb_id']
                    ));
                    $this->logMessage("updating event '" . $incomingEvent->subject . "' from " . $this->formatDate($incomingEvent->start) . " to " . $this->formatDate($incomingEvent->end) ." for user " . $eventArray['plb_id'] . " (" . $eventArray['plb_login'] . "), ical_key: " . $incomingEvent->iCalUId);
                }
            } else {
                // Event insertion
                if ($incomingEvent->type == "occurrence") {
                    $response = $this->sendGet('/users/' . $this->graphKey($eventArray['plb_login']) . '/calendar/events/' . $incomingEvent->seriesMasterId);
                    $rrule = $this->msCalendarUtils->recurrenceToRRule($response->body->recurrence);
                }
                $query = "INSERT INTO " . $this->dbprefix . "absences ";
                $query .= "( perso_id,  debut,  fin,  motif, motif_autre, commentaires, valide_n1, validation_n1, valide, validation, demande, cal_name,  ical_key, last_modified, rrule) VALUES ";
                $query .= "(:perso_id, :debut, :fin, :motif, '',         :commentaires, 99999, NOW(), 99999, NOW(), NOW(), :cal_name, :ical_key, :last_modified, :rrule)";
                $statement = $this->entityManager->getConnection()->prepare($query);
                $statement->execute(array(
                    'perso_id'      => $eventArray['plb_id'],
                    'debut'         => $this->formatDate($incomingEvent->start),
                    'fin'           => $this->formatDate($incomingEvent->end),
                    'motif'         => $this->reason_name,
                    'commentaires'  => $incomingEvent->subject,
                    'cal_name'      => $this->cal_name . $eventArray['plb_id'] . '-' . md5($incomingEvent->iCalUId),
                    'ical_key'      => $incomingEvent->iCalUId,
                    'last_modified' => $incomingEvent->lastModifiedDateTime,
                    'rrule'         => $rrule
                ));
                $this->logMessage("inserting event '" . $incomingEvent->subject . "' from " . $this->formatDate($incomingEvent->start) . " to " . $this->formatDate($incomingEvent->end) ." for user " . $eventArray['plb_id'] . " (" . $eventArray['plb_login'] . "), ical_key: " . $incomingEvent->iCalUId);
            }
        }
    }

    /**
     * Returns the key used in "/users/{key}" Graph requests for a Planno login.
     *
     * Graph only accepts the object id or the userPrincipalName in this path,
     * not the mail address. When login + MSGraph-LoginSuffix looks like an
     * address, the user is first looked up by mail; the immutable object id is
     * used when exactly one user matches. Otherwise the address is used as the
     * userPrincipalName (previous behaviour).
     *
     * Requires the User.Read.All application permission for the lookup;
     * without it, the lookup is disabled for the rest of the run.
     */
    protected function graphKey(?string $login): string {
        $address = (string) $login . $this->login_suffix;
        if (array_key_exists($address, $this->graphKeys)) {
            return $this->graphKeys[$address];
        }

        $key = rawurlencode($address);

        if ($this->mailLookupEnabled && str_contains($address, '@')) {
            // OData string literal: single quotes are escaped by doubling them
            $filter = rawurlencode("mail eq '" . str_replace("'", "''", $address) . "'");
            $response = $this->sendGet("/users?\$filter=$filter&\$select=id,userPrincipalName");

            if (!$response || $response->code != 200) {
                $code = $response ? $response->code : 'none';
                $this->logMessage("Unable to look up users by mail (HTTP $code), check the User.Read.All application permission. Using logins as userPrincipalName.");
                $this->mailLookupEnabled = false;
            } elseif (count($response->body->value) === 1) {
                $key = $response->body->value[0]->id;
                $this->logMessage("$address resolved to " . $response->body->value[0]->userPrincipalName);
            } elseif (count($response->body->value) > 1) {
                $this->logMessage("$address matches several users, using it as userPrincipalName");
            }
        }

        $this->graphKeys[$address] = $key;
        return $key;
    }

    protected function sendGet($request, $absolute = false, $retry = 0) {
        $token = $this->oauth->getToken();
        $headers['Authorization'] = "Bearer $token";
        $response = null;
        try {
            \Unirest\Request::timeout(10);
            $response = \Unirest\Request::get($absolute ? $request : $this->base_url . $request, $headers);
        } catch (\Exception $e) {
            $this->logMessage("Error in Unirest::get: " . $e->getMessage());
            if ($retry < 5) {
                $retry++;
                $this->logMessage("Retry #$retry...");
                return $this->sendGet($request, $absolute, $retry);
            }
        }
        return $response;
    }

    private function logMessage($message): void {
        $this->log($message, "MSGraphClient", $this->stdout);
    }

    private function formatDate($graphdate, $format = "Y-m-d H:i:s"): string {
        $time = strtotime($graphdate->dateTime . $graphdate->timeZone);
        return date($format, $time);
    }
}
