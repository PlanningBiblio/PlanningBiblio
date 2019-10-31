<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ics/class.ics.php
Création : 29 mai 2016
Dernière modification : 9 avril 2019
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
 
require_once(__DIR__.'/../include/config.php');
require_once(__DIR__.'/../vendor/ics-parser/class.iCalReader.php');
require_once(__DIR__.'/../personnel/class.personnel.php');

class CJICS
{
    public $CSRFToken = null;
    public $error=null;
    public $logs=null;
    public $pattern=null;
    public $perso_id=0;
    public $status = 'CONFIRMED';
    public $src=null;
    public $table="absences";

    /**
     * purge
     * @param string $this->table
     * @param int $this->perso_id (optionnel)
     * Supprime de la table $this->table tous les événements du calendrier $this->src pour l'agent défini par $this->perso_id
     */
    public function purge()
    {
        // Initialisation des variables
        $CSRFToken = $this->CSRFToken;
        $perso_id = $this->perso_id;    // perso_id
        $table = $this->table;          // Table à mettre à jour
        $src = $this->src;              // Fichier ICS
        $calName = null;                // Nom du calendrier

        // Parse le fichier ICS, le tableau $events contient les événements du fichier ICS
        $ical   = new ICal($src, "MO");

        // Récupération du nom du calendrier
        $calName=$ical->calendarName();
        $calName = removeAccents($calName);
    
        if (empty($calName)) {
            return false;
        }

        if ($this->logs) {
            logs("Agent #$perso_id : Purge $calName, Table: $table, src: $src", "ICS", $CSRFToken);
        }
    
        if ($this->logs) {
            $db = new db();
            $db->select2($table, 'id', array('cal_name' => $calName, 'perso_id' => $perso_id));
            $nb = $db->nb;
            logs("Agent #$perso_id : Purge $calName, Table: $table, $nb éléments à supprimer", "ICS", $CSRFToken);
        }

        $db = new db();
        $db->CSRFToken = $CSRFToken;
        $db->delete($table, array('cal_name' => $calName, 'perso_id' => $perso_id));
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

        if ($this->logs) {
            logs("Agent #$perso_id : Table: $table, src: $src", "ICS", $CSRFToken);
        }

        // Parse le fichier ICS, le tableau $events contient les événements du fichier ICS
        $ical   = new ICal($src, "MO");
        $events = $ical->events();

        // Récupération du nom du calendrier
        $calName=$ical->calendarName();
        $calName = removeAccents($calName);
        $calTimeZone = $ical->calendarTimezone();
        if ($this->logs) {
            logs("Agent #$perso_id : Calendrier: $calName, Fuseau horaire: $calTimeZone", "ICS", $CSRFToken);
        }

        if (!is_array($events) or empty($events)) {
            if ($this->logs) {
                logs("Agent #$perso_id : Aucun élément trouvé dans le fichier $src", "ICS", $CSRFToken);
                $events = array();
            }
        }

        // Récupération de l'email de l'agent
        $p = new personnel();
        $p->fetchById($perso_id);
        $email = $p->elements[0]['mail'];

        // Ne garde que les événements confirmés et occupés et rempli le tableau $iCalKeys
        $tmp=array();

        foreach ($events as $elem) {
            // Add LAST-MODIFIED = Now, if this attribute doesn't exist (missing in Hamac)
            if (empty($elem['LAST-MODIFIED'])) {
                $elem['LAST-MODIFIED'] = $now;
            }
            // Add STATUS = "CONFIRMED", if this attribute doesn't exist (missing in Hamac)
            if (empty($elem['STATUS'])) {
                $elem['STATUS'] = "CONFIRMED";
            }

            $key=$elem['UID']."_".$elem['DTSTART']."_".$elem['LAST-MODIFIED'];
            $tmp[]=array_merge($elem, array("key"=>$key));
        }

        $events=array();
        foreach ($tmp as $elem) {
            // Ne traite pas les événéments ayant le status X-MICROSOFT-CDO-INTENDEDSTATUS différent de BUSY (si le paramètre X-MICROSOFT-CDO-INTENDEDSTATUS existe)
            if (isset($elem['X-MICROSOFT-CDO-INTENDEDSTATUS']) and $elem['X-MICROSOFT-CDO-INTENDEDSTATUS'] != "BUSY") {
                continue;
            }

            // Exclusion des dates EXDATE (ics-parser ne le gère pas correctement)
            if (isset($elem['EXDATE'])) {
                $exdate_array = explode(",", $elem['EXDATE']);
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

            // Traite seulement les événéments ayant le STATUS CONFIRMED si la configuration demande seulement les status CONFIRMED
            $add = false;

            // If unconfirmed events are accepted
            if ($this->status != 'CONFIRMED') {
                $add = true;

            // If only confirmed events are accepted
            } elseif ($elem['STATUS']=="CONFIRMED") {

                // Check if it is an invitation from someone else (or including attendees)
                // And check if the owner of this calendar accepted it
                if (!empty($elem['ATTENDEE'])) {
                    $attendees = explode('CUTYPE=', $elem['ATTENDEE']);
                    foreach ($attendees as $attendee) {
                        if (!empty($attendee) and strpos($attendee, $email)) {
                            if (strpos($attendee, 'PARTSTAT=ACCEPTED')) {
                                $add = true;
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

        // Recherche les événements correspondant au calendrier $calName et à l'agent $perso_id dans la table $table
        $db=new db();
        $db->select2($table, null, array("cal_name"=> "$calName","perso_id"=>$perso_id));
        if ($db->result) {
            // Pour chaque événement
            foreach ($db->result as $elem) {
                // Si l'évenement n'est plus dans le fichier ICS ou s'il a été modifié dans le fichier ICS, on le supprime : complète le tableau $delete
                if (!in_array($elem['ical_key'], $iCalKeys)) {
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
            logs("Agent #$perso_id : $nb événement(s) supprimé(s)", "ICS", $CSRFToken);
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
                (`perso_id`, `debut`, `fin`, `demande`, `valide`, `validation`, `valide_n1`, `validation_n1`, `motif`, `motif_autre`, `commentaires`, `groupe`, `cal_name`, `ical_key`, `uid`, `rrule`, `id_origin`)
                VALUES (:perso_id, :debut, :fin, :demande, :valide, :validation, :valide_n1, :validation_n1, :motif, :motif_autre, :commentaires, :groupe, :cal_name, :ical_key, :uid, :rrule, :id_origin);";
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
                $motif_autre = $this->pattern == '[SUMMARY]' ? $elem['SUMMARY'] : $this->pattern;

                // Si SUMMARY est enregistré dans le champ motif, on ne le met pas dans le champ description
                if ($this->pattern == '[SUMMARY]') {
                    $commentaires = !empty($elem['DESCRIPTION']) ? $elem['DESCRIPTION'] : '';
                } else {
                    $commentaires = !empty($elem['SUMMARY']) ? $elem['SUMMARY'] : '';
                    if ($commentaires and !empty($elem['DESCRIPTION'])) {
                        $commentaires .= "<br/>\n";
                    }
                    if (!empty($elem["DESCRIPTION"])) {
                        $commentaires .= $elem['DESCRIPTION'];
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

                // Préparation de l'insertion dans la base de données
                $tab[] = array(":perso_id" => $perso_id, ":debut" => $debut, ":fin" => $fin, ":demande" => $demande, ":valide"=> $valide_n2, ":validation" => $validation_n2, ":valide_n1"=> $valide_n1, ":validation_n1" => $validation_n1,
                    ":motif" => $motif, ":motif_autre" => $motif_autre, ":commentaires" => $commentaires, ":groupe" => $groupe, ":cal_name" => $calName, ":ical_key" => $elem['key'], ":uid" => $elem['UID'], ":rrule" => $rrule, 
                    ":id_origin" => $id_origin);

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
            logs("Agent #$perso_id : $nb événement(s) importé(s)", "ICS", $CSRFToken);
        }
    }
}