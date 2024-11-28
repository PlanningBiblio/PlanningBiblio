<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

@file ics/class.ics.php
Création : 29 mai 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe permettant le traitement des fichiers ICS
*/


/**
 * Utilisation :
 * foreach($tab as $elem){
 *   $ics=new CJICS();
 *   $ics->CSRFToken;		// Jeton XSRF
 *   $ics->src=$elem[1];	// source ICS
 *   $ics->perso_id=$elem[0];	// ID de l'agent
 *   $ics->table="absences";	// Table à mettre à jour
 *   $ics->logs=true            // Loguer les opérations dans la base de données (table logs)
 *   $ics->updateTable();
 * }
 *
 * @note :
 * Clés pour la MAJ de la base de données : UID + + DTSTART + LAST-MODIFIED
 * - Si la clé n'existe que dans la base de données, l'événement correspondant sera supprimé
 * - Si la clé n'existe que dans le fichier ICS, l'évenement sera ajouté
 * les 2 actions précédentes permettent également de gérer les modifications et les récurrences car
 * - la clé est modifiée si l'événement est modifié (la clé contient LAST-MODIFIED)
 * - il existe une clé par date d'un événement récurrent (la clé contient DTSTART qui est le début de chaque occurence)
 *
 * RRULE => FREQ=WEEKLY;COUNT=6;BYDAY=TU,TH
 * RRULE => FREQ=WEEKLY;UNTIL=20150709T073000Z;BYDAY=MO,TU,WE,TH
 * EXDATE : exception dates
 */
 
use ICal\ICal;

require_once(__DIR__.'/../include/config.php');
require_once(__DIR__.'/../personnel/class.personnel.php');

class CJICS
{
    public $CSRFToken = null;
    public $error=null;
    public $icsServer = 0;
    public $logs=null;
    public $number = 0;
    public $pattern=null;
    public $desc = true;
    public $perso_id=0;
    public $status = 'CONFIRMED';
    public $src=null;
    public $table="absences";

    /** 
     * @method createIcsEvent
     * @param $params array
     * @return $event array
     */

    public static function createIcsEvent($params) {
    
        /* 
        For absences, params are $id, $start, $end, $reason, $comment, $status, $createdAt, $lastModified
        For planning, params are $id, $start, $end, $position, $positionId, $site, $siteId, $floor, $organizer, $lastModified
        */
    
        extract($params);
    
        $description = $comment ?? '';
        $createdAt = isset($createdAt) ? gmdate('Ymd\THis\Z', $createdAt) : null;
        $floor = $floor ?? null;
        $organizer = $organizer ?? null;
        $positionId = $positionId ?? null;
        $positionOrReason = $position ?? $reason;
        $site = $site ?? null;
        $siteId = $siteId ?? null;
        $status = $status ?? 'CONFIRMED';
    
        $tz = date_default_timezone_get();
        $url = $_SERVER['SERVER_NAME'];
    
        $start = date('Ymd\THis', $start);
        $end = date('Ymd\THis', $end);
        $lastModified = gmdate('Ymd\THis\Z', $lastModified);
    
        // If the site is not provide, this is an absence
        $location = $siteId ? $site . $floor : null;

        $shortDescription = $description;
        if ($shortDescription) {
            if (strpos($shortDescription, "\r")) {
                $shortDescription = strstr($shortDescription, "\r", true);
            }
            $shortDescription = ' ' . $shortDescription;
        }

        $summary = $positionOrReason . $shortDescription;

        $description = str_replace("\r\n", "\\n", $description);

        $event = [];
    
        $event[] = "BEGIN:VEVENT";
        $event[] = "UID:$id-$siteId-$positionId-$start-$end@$url";
        $event[] = 'DTSTAMP:' . gmdate('Ymd\THis\Z');
        $event[] = "DTSTART;TZID=$tz:$start";
        $event[] = "DTEND;TZID=$tz:$end";
        $event[] = self::splitLine("SUMMARY:$summary");

        if ($description) {
            $event[] = self::splitLine("DESCRIPTION:$description");
        }
    
        if($organizer){
          $event[] = "ORGANIZER;CN=$organizer";
        }
    
        $event[] = "LOCATION:$location";
        $event[] = "STATUS:$status";
        $event[] = 'CLASS:PUBLIC';
        $event[] = 'X-MICROSOFT-CDO-INTENDEDSTATUS:BUSY';
        $event[] = 'TRANSP:OPAQUE';

        if ($createdAt) {
            $event[] = "CREATED:$createdAt";
        }

        $event[] = "LAST-MODIFIED:$lastModified";
        $event[] = "DTSTAMP:$lastModified";
        $event[] = 'BEGIN:VALARM';
        $event[] = 'ACTION:DISPLAY';
        $event[] = 'DESCRIPTION:This is an event reminder';
        $event[] = 'TRIGGER:-P0DT0H10M0S';
        $event[] = 'END:VALARM';
        $event[] = 'END:VEVENT';
    
        return $event;
    }

    /**
     * purge
     * @param string $this->table
     * @param int $this->perso_id (optionnel)
     * Supprime de la table $this->table tous les événements du calendrier $this->src pour l'agent défini par $this->perso_id
     */
    public function purge($old = false)
    {
        // Initialisation des variables
        $CSRFToken = $this->CSRFToken;
        $icsServer = $this->icsServer;
        $perso_id = $this->perso_id;    // perso_id
        $table = $this->table;          // Table à mettre à jour
        $src = $this->src;              // Fichier ICS
        $calName = null;                // Nom du calendrier

        // Test if the URL is valid
        if (substr($src, 0, 4) == 'http') {
            $test = @get_headers($src, 1);

            if (empty($test)) {
                logs("Agent #$perso_id, Server #$icsServer : $src is not a valid URL", "ICS", $CSRFToken);
                return false;
            }

            if (!strstr($test[0], '200')) {
                logs("Agent #$perso_id, Server #$icsServer : $src {$test[0]}", "ICS", $CSRFToken);
                return false;
            }
        }

        // Parse le fichier ICS, le tableau $events contient les événements du fichier ICS
        $ical   = new ICal($src);

        // Récupération du nom du calendrier
        $calName=$ical->calendarName();
        $calName = removeAccents($calName);

        if (empty($calName)) {
            $calName = "imported_calendar_{$this->number}_for_agent_$perso_id";
        }

        if ($this->logs) {
            logs("Agent #$perso_id, Server #$icsServer : Purge $calName, Table: $table, src: $src", "ICS", $CSRFToken);
        }

        if ($this->logs) {
            $db = new db();
            $db->select2($table, 'id', array('cal_name' => $calName, 'perso_id' => $perso_id));
            $nb = $db->nb;
            logs("Agent #$perso_id, Server #$icsServer : Purge $calName, Table: $table, $nb éléments à supprimer", "ICS", $CSRFToken);
        }

        if ($icsServer != 0 and $old == false) {
            $db = new db();
            $db->CSRFToken = $CSRFToken;
            $db->delete($table, array('ics_server' => $icsServer, 'perso_id' => $perso_id));
        } elseif ($old == true) {
            $db = new db();
            $db->CSRFToken = $CSRFToken;
            $db->delete($table, array('ics_server' => null, 'cal_name' => $calName, 'perso_id' => $perso_id));
        }
    }

    /**
     * @function updateTable
     * @param string $this->table : table à mettre à jour (ex: absences)
     * @param int $this->perso_id : ID de l'agent
     * @param string $this->src : chemin vers le fichier ICS
     * @param string $this->CSRFToken : Jeton XSRF
     * Met à jour la table définie par $this->table pour l'agent défini par $this->perso_id depuis le fichier ICS $this->src
     */
    public function updateTable()
    {
        // Initialisation des variables
        $CSRFToken = $this->CSRFToken;
        $icsServer = $this->icsServer;
        $perso_id=$this->perso_id;  // perso_id
        $table=$this->table;        // Table à mettre à jour
        $src=$this->src;            // Fichier ICS
        $iCalKeys=array();          // Clés des événements confirmés et occupés du fichier ICS
        $tableKeys=array();         // Clés des événements ICS de la table $table
        $calName=null;              // Nom du calendrier
        $deleted=array();           // Evénements supprimés du fichier ICS ou événements modifiés
        $insert=array();            // Evénements à insérer (nouveaux ou événements modifiés (suppression + réinsertion))
        $email=null;                // Email de l'agent
        $now = date('Ymd\THis\Z');  // Current time
        $config = $GLOBALS['config'];

        if ($this->logs) {
            logs("Agent #$perso_id, Server #$icsServer : Table: $table, src: $src", "ICS", $CSRFToken);
        }

        // Get available absences reasons
        $reasons = array();
        $db = new db();
        $db->select('select_abs');
        if ($db->result) {
            foreach ($db->result as $elem) {
                $reasons[] = $elem['valeur'];
            }
        }

        // Parse le fichier ICS, le tableau $events contient les événements du fichier ICS
        try {
            $ical   = new ICal($src);
            $events = !empty($ical->cal['VEVENT']) ? $ical->cal['VEVENT'] : array();
        } catch(Exception $e) {
            if ($this->logs) {
                $error = $e->getMessage();
                logs("Agent #$perso_id, Server #$icsServer : Impossible de lire le fichier $src", "ICS", $CSRFToken);
                logs("Agent #$perso_id, Server #$icsServer : Error : $error", "ICS", $CSRFToken);
                return false;
            }
        }

        // Récupération du nom du calendrier
        $calName = $ical->calendarName();
        $calName = removeAccents($calName);

        if (empty($calName)) {
            $calName = "imported_calendar_{$this->number}_for_agent_$perso_id";
        }

        // Product ID / Product name
        $prodID = $ical->cal['VCALENDAR']['PRODID'] ?? null;

        $calTimeZone = $ical->calendarTimezone();
        if ($this->logs) {
            logs("Agent #$perso_id, Server #$icsServer : Calendrier: $calName, Fuseau horaire: $calTimeZone", "ICS", $CSRFToken);
        }

        if (!is_array($events) or empty($events)) {
            if ($this->logs) {
                logs("Agent #$perso_id, Server #$icsServer : Aucun élément trouvé dans le fichier $src", "ICS", $CSRFToken);
                $events = array();
            }
        }

        // Récupération de l'email de l'agent
        $p = new personnel();
        $p->fetchById($perso_id);
        $email = $p->elements[0]['mail'];

        // Ne garde que les événements confirmés et occupés et rempli le tableau $iCalKeys
        $tmp=array();

        // Isolate events that contain RECURRENCE-ID
        // These events are exceptions to recurring events that have the same UID
        $events_with_recurrence_id = array();

        foreach ($events as $elem) {
            // Add LAST-MODIFIED = Now, if this attribute doesn't exist (missing in Hamac)
            if (empty($elem['LAST-MODIFIED'])) {
                $elem['LAST-MODIFIED'] = $now;
            }
            // Add STATUS = "CONFIRMED", if this attribute doesn't exist (missing in Hamac)
            if (empty($elem['STATUS'])) {
                $elem['STATUS'] = "CONFIRMED";
            }

            // Isolate events that contain RECURRENCE-ID
            if (!empty($elem['RECURRENCE-ID'])) {

                // Store the dates for which we will create exceptions
                $events_with_recurrence_id[$elem['UID']]['DATES'][] = $elem['RECURRENCE-ID'];

                // Change the UID of the current event to differentiate it from the original event. If not, it will be ignored.
                $elem['UID'] = $elem['UID'] . '_' . $elem['DTSTART'];
            }

            $key=$elem['UID']."_".$elem['DTSTART']."_".$elem['LAST-MODIFIED'];
            $tmp[]=array_merge($elem, array("key"=>$key));
        }

        $events=array();
        foreach ($tmp as $elem) {

            // Run custom exclusions
            if (!empty($config['ICS-custom-exclusion'])) {
                if ($config['ICS-custom-exclusion']($elem)) {
                    continue;
                }
            }

            // Ne traite pas les événéments ayant le status X-MICROSOFT-CDO-INTENDEDSTATUS différent de BUSY (si le paramètre X-MICROSOFT-CDO-INTENDEDSTATUS existe)
            if (isset($elem['X-MICROSOFT-CDO-INTENDEDSTATUS']) and $elem['X-MICROSOFT-CDO-INTENDEDSTATUS'] != "BUSY") {
                continue;
            }

            // Ignore dates referenced in the RECURRENCE-ID attribute of other events that have the same UID
            if (!isset($elem['RECURRENCE-ID']) and array_key_exists($elem['UID'], $events_with_recurrence_id)) {
	        foreach ($events_with_recurrence_id[$elem['UID']]['DATES'] as $date) {
                    $d = date("Ymd\THis", strtotime($date));
                    if ($d == $elem['DTSTART_tz']) {
                        continue 2;
                    }
                }
            }

            // Exclusion des dates EXDATE (ics-parser ne le gère pas correctement)
            if (isset($elem['EXDATE'])) {
                $exdate1 = preg_replace('/.*:(.[^:]*)$/', "$1", $elem['EXDATE']);
                $exdate_array = explode(",", $exdate1);
                if ($exdate_array and !empty($exdate_array)) {
                    foreach ($exdate_array as $exdate) {
                        $exdate = date("Ymd\THis", strtotime($exdate));
                        if ($exdate == $elem['DTSTART_tz']) {
                            continue 2;
                        }
                    }
                }
            }

            // Traite seulement les événéments ayant un status occupé TRANSP OPAQUE (TRANSP OPAQUE défini un status BUSY)
            if (isset($elem['TRANSP']) && $elem['TRANSP'] != "OPAQUE") {
                continue;
            }

            // Ignore events with STATUS = CANCELLED
            if ($elem['STATUS'] == 'CANCELLED') {
                continue;
            }

            // Add "[PRE]" exclusion for Hamac import
            if (!empty($prodID) and stristr($prodID, 'Serveur de planning Cocktail')) {
              if (isset($config['ics_exclude_summary']) and is_array($config['ics_exclude_summary'])) {
                $config['ics_exclude_summary'][] = '[PRE]';
              } else {
                $config['ics_exclude_summary'] = array('[PRE]');
              }
            }

            // Ignore events with SUMMARY defined in $config['ics_exclude_summary']
            if (isset($config['ics_exclude_summary']) and is_array($config['ics_exclude_summary'])) {
              if ( in_array($elem['SUMMARY'], $config['ics_exclude_summary'])) {
                  continue;
              }
            }

            // Traite seulement les événéments ayant le STATUS CONFIRMED si la configuration demande seulement les status CONFIRMED
            $add = false;
            // If unconfirmed events are accepted
            if ($this->status != 'CONFIRMED') {
                $add = true;

            // If only confirmed events are accepted
            } elseif ($elem['STATUS']=="CONFIRMED") {
                // Check if it is an invitation from someone else (or including attendees)
                // And check if the owner of this calendar accepted it

                if (!empty($elem['ATTENDEE_array'])) {

                    foreach ($elem['ATTENDEE_array'] as $key => $value) {

                        if (!empty($value)
                            and is_string($value)
                            and strpos($value, $email)) {

                            $attendee_agent = $elem['ATTENDEE_array'][$key - 1] ?? array();

                            if (!empty($attendee_agent['PARTSTAT'])
                                and $attendee_agent['PARTSTAT'] == 'ACCEPTED') {

                                $add = true;
                                break;
                            }
                        }
                    }

                // If event created by calendar's owner and STATUS=CONFIRMED
                } else {
                    $add = true;
                }
            }

            if ($add) {
                $events[]=$elem;
                $iCalKeys[]=$elem['key'];
            }
        }

        // Recherche les événements correspondant au calendrier $icsServer et à l'agent $perso_id dans la table $table
        $db=new db();
        $db->select2($table, null, array('ics_server' => $icsServer, 'perso_id' => $perso_id));
        if ($db->result) {
            // Pour chaque événement
            foreach ($db->result as $elem) {
                // Si l'évenement n'est plus dans le fichier ICS ou s'il a été modifié dans le fichier ICS, on le supprime : complète le tableau $delete
                if (!in_array($elem['ical_key'], $iCalKeys) && $elem['ical_key'] != null) {
                    $deleted[]=array(":id"=>$elem['id']);
                } else {
                    // Sinon, on complète le table $tableKeys avec la clé de l'évenement pour ne pas le réinsérer dans la table
                    $tableKeys[]=$elem['ical_key'];
                }
            }
        }

        // Suppression des événements supprimés ou modifiés de la base de données
        $nb = count($deleted);
        if (!empty($deleted)) {
            $db=new dbh();
            $db->CSRFToken = $CSRFToken;
            $db->prepare("DELETE FROM `{$GLOBALS['config']['dbprefix']}$table` WHERE `id`=:id;");
            foreach ($deleted as $elem) {
                $db->execute($elem);
            }
        }

        if ($this->logs) {
            logs("Agent #$perso_id, Server #$icsServer : $nb événement(s) supprimé(s)", "ICS", $CSRFToken);
        }

        // Insertion des nouveux éléments ou des éléments modifiés dans la table $table : complète le tableau $insert
        foreach ($events as $elem) {
            if (!in_array($elem['key'], $tableKeys)) {
                $insert[]=$elem;
            }
        }

        // Insertion des nouveux éléments ou des éléments modifiés dans la table $table : insertion dans la base de données
        $nb=0;
        if (!empty($insert)) {
            $db=new dbh();
            $req = "INSERT INTO `{$GLOBALS['config']['dbprefix']}$table`
                (`perso_id`, `debut`, `fin`, `demande`, `valide`, `validation`, `valide_n1`, `validation_n1`, `motif`, `motif_autre`, `commentaires`, `groupe`, `ics_server`, `cal_name`, `ical_key`, `imported_at`, `uid`, `rrule`, `id_origin`, `last_modified`)
                VALUES (:perso_id, :debut, :fin, :demande, :valide, :validation, :valide_n1, :validation_n1, :motif, :motif_autre, :commentaires, :groupe, :ics_server, :cal_name, :ical_key, :imported_at, :uid, :rrule, :id_origin, :last_modified);";
            $db->CSRFToken = $CSRFToken;
            $db->prepare($req);

            $tab = array();

            foreach ($insert as $elem) {
                // Adaptation des valeurs pour la base de données
                $lastmodified = date("Y-m-d H:i:s", strtotime($elem['LAST-MODIFIED']));
                $demande= array_key_exists("CREATED", $elem) ? date("Y-m-d H:i:s", strtotime($elem['CREATED'])) : $lastmodified;

                $debut = date("Y-m-d H:i:s", strtotime($elem["DTSTART_tz"]));
                $id_origin = 0;

                // Si pas de date de fin, la fin est égale au début
                if (empty($elem["DTEND_tz"])) {
                    $elem["DTEND_tz"] = $elem["DTSTART_tz"];
                }

                // Les événements ICS sur des journées complètes ont comme date de fin J+1 à 0h00
                // Donc si la date de fin est à 0h00, on retire une seconde pour la rammener à J
                $offset = date("H:i:s", strtotime($elem["DTEND_tz"])) == "00:00:00" ? "-1 second" : null;
                $fin = date("Y-m-d H:i:s", strtotime($elem["DTEND_tz"]." $offset"));

                // Par défaut, nous mettons dans le champ motif l'information enregistrée dans la config, paramètre ICS-PatternX (ex: Agenda personnel)
                // Mais nous pouvons mettre l'information présente dans le champ SUMMARY de l'événements. Dans ce cas, il faut préciser $this->pattern = "[SUMMARY]"; (exemple d'utilisation : enregistrement d'absences récurrentes dans Planning Biblio)

                $motif = $this->pattern == '[SUMMARY]' ? $elem['SUMMARY'] : $this->pattern;
                $motif_autre = '';

                if (!in_array($motif, $reasons)) {
                    $motif = 'Autre';
                    $motif_autre = $elem['SUMMARY'];
                }

                // The DESCRIPTION is added to the comment field depending on the configuration
                $description = null;
                if ($this->desc) {
                    $description = !empty($elem['DESCRIPTION']) ? str_replace("\\n", "\n", $elem['DESCRIPTION']) : '';
                }

                // If SUMMARY is stored in the "Absence Reason" field, we do not add it to the comment field
                if ($this->pattern == '[SUMMARY]') {
                    $commentaires = !empty($description) ? $description : '';
                // Else, SUMMARY and DESCRIPTION are stored in the comment field
                } else {
                    $commentaires = !empty($elem['SUMMARY']) ? $elem['SUMMARY'] : '';
                    if ($commentaires and !empty($description)) {
                        $commentaires .= "<br/>\n";
                    }
                    if (!empty($description)) {
                        $commentaires .= $description;
                    }
                }

                // Utilisation du champ CATEGORIES pour la gestion des absences groupées (plusieurs agents), et des validations
                $groupe = '';

                // Initialization of validation parameters for Planning Biblio's event (recurrent absences)
                if (stripos($calName, 'PlanningBiblio')) {
                    $valide_n1 = 0;
                    $validation_n1 = '0000-00-00 00:00:00';
                    $valide_n2 = 0;
                    $validation_n2 = '0000-00-00 00:00:00';
                // Initialization of validation parameters for imported events
                } else {
                    $valide_n1 = $elem['STATUS'] == 'CONFIRMED' ? 99999 : 0;
                    $validation_n1 = $elem['STATUS'] == 'CONFIRMED' ? $lastmodified : '0000-00-00 00:00:00';
                    $valide_n2 = $elem['STATUS'] == 'CONFIRMED' ? 99999 : 0;
                    $validation_n2 = $elem['STATUS'] == 'CONFIRMED' ? $lastmodified : '0000-00-00 00:00:00';
                }

                if (!empty($elem['CATEGORIES'])) {
                    $categories = $elem['CATEGORIES'];

                    // Groupe
                    if (strstr($categories, 'PBGroup=')) {
                        $groupe = preg_replace('/.*PBGroup=(\d+-\d+).*/', "$1", $categories);
                    }

                    // Validation N1
                    if (strstr($categories, 'PBValideN1=')) {
                        $valide_n1 = preg_replace('/.*PBValideN1=(-{0,1}\d+).*/', "$1", $categories);
                    }

                    if (strstr($categories, 'PBValidationN1=')) {
                        $validation_n1 = preg_replace('/.*PBValidationN1=(\d+-\d+-\d+ \d+:\d+:\d+).*/', "$1", $categories);
                    }

                    // Validation N2
                    if (strstr($categories, 'PBValideN2=')) {
                        $valide_n2 = preg_replace('/.*PBValideN2=(-{0,1}\d+).*/', "$1", $categories);
                    }

                    if (strstr($categories, 'PBValidationN2=')) {
                        $validation_n2 = preg_replace('/.*PBValidationN2=(\d+-\d+-\d+ \d+:\d+:\d+).*/', "$1", $categories);
                    }

                    // ID Origin
                    if (strstr($categories, 'PBIDOrigin=')) {
                        $id_origin = preg_replace('/.*PBIDOrigin=(\d+).*/', "$1", $categories);
                    }
                }

                $rrule = !empty($elem['RRULE']) ? $elem['RRULE'] : '';
                $last_modified = !empty($elem['X-LAST-MODIFIED-STRING']) ? $elem['X-LAST-MODIFIED-STRING'] : null;
                // Préparation de l'insertion dans la base de données
                $tab[] = array(
                  ":perso_id" => $perso_id,
                  ":debut" => $debut,
                  ":fin" => $fin,
                  ":demande" => $demande,
                  ":valide"=> $valide_n2,
                  ":validation" => $validation_n2,
                  ":valide_n1"=> $valide_n1,
                  ":validation_n1" => $validation_n1,
                  ":motif" => $motif,
                  ":motif_autre" => $motif_autre,
                  ":commentaires" => $commentaires,
                  ":groupe" => $groupe,
                  ':ics_server' => $icsServer,
                  ":cal_name" => $calName,
                  ":ical_key" => $elem['key'],
                  ':imported_at' => date('Y-m-d H:i:s'),
                  ":uid" => $elem['UID'],
                  ":rrule" => $rrule, 
                  ":id_origin" => $id_origin,
                  ':last_modified' => $last_modified
                );

                $nb++;
            }

            // Enregistrement des infos dans la base de données
            foreach ($tab as $elem) {
                // Si l'événement ne contient qu'une seule occurrence, on supprime la règle de récurrence. Ce qui aura pour effet de ne pas afficher l'icône et le popup de modification de récurrence
                if ($nb < 2) {
                    $elem[':rrule'] = '';
                }

                $db->execute($elem);
            }
        }

        if ($this->logs) {
            logs("Agent #$perso_id, Server #$icsServer : $nb événement(s) importé(s)", "ICS", $CSRFToken);
        }
    }

    private static function splitLine($line) {

        if (strlen($line) > 75) {
            $tab = mb_str_split($line, 75);
            $line = implode("\n ", $tab);
        }

        return $line;
    }
}
