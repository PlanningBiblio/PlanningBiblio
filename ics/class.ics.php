<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : ics/class.ics.php
Création : 29 mai 2016
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe permettant le traitement des fichiers ICS 
*/


/**
 * Utilisation : 
 * foreach($tab as $elem){
 *   $i=new CJICS();
 *   $i->src=$elem[1];		// source ICS
 *   $i->perso_id=$elem[0];	// ID de l'agent
 *   $i->table="absences";	// Table à mettre à jour
 *   $ics->logs=true            // Loguer les opérations dans la base de données (table logs)
 *   $i->updateTable();
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
 
require_once __DIR__."/../include/config.php";
require_once __DIR__."/../vendor/ics-parser/class.iCalReader.php";

class CJICS{

  public $CSRFToken = null;
  public $error=null;
  public $logs=null;
  public $pattern=null;
  public $perso_id=0;
  public $src=null;
  public $table="absences";

  /**
   * updateTable
   * @param string $this->table
   * @param int $this->perso_id (optionnel)
   * Met à jour la table définie par $this->table pour l'agent défini par $this->perso_id depuis le fichier ICS $this->src
   */
  public function updateTable(){

    // Initialisation des variables
    $CSRFToken = $this->CSRFToken;
    $perso_id=$this->perso_id;	// perso_id
    $table=$this->table;	// Table à mettre à jour
    $src=$this->src;		// Fichier ICS
    $iCalKeys=array();  	// Clés des événements confirmés et occupés du fichier ICS
    $tableKeys=array();		// Clés des événements ICS de la table $table
    $calName=null;		// Nom du calendrier
    $deleted=array();		// Evénements supprimés du fichier ICS ou événements modifiés
    $insert=array();		// Evénements à insérer (nouveaux ou événements modifiés (suppression + réinsertion))

    if($this->logs){
      logs("Table: $table, Perso: $perso_id, src: $src", "ICS", $CSRFToken);
    }

    // Parse le fichier ICS, le tableau $events contient les événements du fichier ICS
    $ical   = new ICal($src, "MO");
    $events = $ical->events();
    
    // Récupération du nom du calendrier
    $calName=$ical->calendarName();
    $calName = removeAccents($calName);
    $calTimeZone = $ical->calendarTimezone();
    if($this->logs){
      logs("Calendrier: $calName, Fuseau horaire: $calTimeZone", "ICS", $CSRFToken);
    }
    
    if(!is_array($events) or empty($events)){
      if($this->logs){
        logs("Aucun élément trouvé dans le fichier $src", "ICS", $CSRFToken);
        $events = array();
      }
    }
    
    // Ne garde que les événements confirmés et occupés et rempli le tableau $iCalKeys
    $tmp=array();
    
    foreach($events as $elem){
      $key=$elem['UID']."_".$elem['DTSTART']."_".$elem['LAST-MODIFIED'];
      $tmp[]=array_merge($elem,array("key"=>$key));
    }
    
    $events=array();
    foreach($tmp as $elem){
	  // Ne traite pas les événéments ayant le status X-MICROSOFT-CDO-INTENDEDSTATUS différent de BUSY (si le paramètre X-MICROSOFT-CDO-INTENDEDSTATUS existe)
	  if(isset($elem['X-MICROSOFT-CDO-INTENDEDSTATUS']) and $elem['X-MICROSOFT-CDO-INTENDEDSTATUS'] != "BUSY"){
		continue;
	  }
	  
	  // Exclusion des dates EXDATE (ics-parser ne le gère pas correctement)
	  if(isset($elem['EXDATE'])){
		$exdate_array = explode(",", $elem['EXDATE']);
		if($exdate_array and !empty($exdate_array)){
		  foreach ($exdate_array as $exdate){
			$exdate = date("Ymd\THis", strtotime($exdate));
			if($exdate == $elem['DTSTART_tz']){
			  continue 2;
			}
		  }
		}
	  }

	  // Traite seulement les événéments ayant le STATUS CONFIRMED et TRANSP OPAQUE (TRANSP OPAQUE défini un status BUSY)
      if($elem['STATUS']=="CONFIRMED" and $elem['TRANSP']=="OPAQUE"){
		$events[]=$elem;
		$iCalKeys[]=$elem['key'];
      }
    }

    // Recherche les événements correspondant au calendrier $calName et à l'agent $perso_id dans la table $table
    $db=new db();
    $db->select2($table,null,array("cal_name"=> "$calName","perso_id"=>$perso_id));
    if($db->result){
      // Pour chaque événement
      foreach($db->result as $elem){
		// Si l'évenement n'est plus dans le fichier ICS ou s'il a été modifié dans le fichier ICS, on le supprime : complète le tableau $delete
		if(!in_array($elem['ical_key'],$iCalKeys)){
		  $deleted[]=array(":id"=>$elem['id']);
		}else{
		  // Sinon, on complète le table $tableKeys avec la clé de l'évenement pour ne pas le réinsérer dans la table
		  $tableKeys[]=$elem['ical_key'];
		}
      }
    }
    
    // Suppression des événements supprimés ou modifiés de la base de données
	$nb = count($deleted);
    if(!empty($deleted)){
      $db=new dbh();
      $db->prepare("DELETE FROM `{$GLOBALS['dbprefix']}$table` WHERE `id`=:id;");
      foreach($deleted as $elem){
		$db->execute($elem);
      }
    }

    if($this->logs){
      logs("$nb événement(s) supprimé(s)", "ICS", $CSRFToken);
    }

    // Insertion des nouveux éléments ou des éléments modifiés dans la table $table : complète le tableau $insert
    foreach($events as $elem){
      if(!in_array($elem['key'],$tableKeys)){
		$insert[]=$elem;
      }
    }
      
    // Insertion des nouveux éléments ou des éléments modifiés dans la table $table : insertion dans la base de données
	$nb=0;
    if(!empty($insert)){
      $db=new dbh();
      $req="INSERT INTO `{$GLOBALS['dbprefix']}$table` (`perso_id`, `debut`, `fin`, `demande`, `valide`, `validation`, `valide_n1`, `validation_n1`, `motif`, `motif_autre`, `commentaires`, `cal_name`, `ical_key`) 
		VALUES (:perso_id, :debut, :fin, :demande, :valide, :validation, :valide_n1, :validation_n1, :motif, :motif_autre, :commentaires, :cal_name, :ical_key);";
      $db->prepare($req);

      foreach($insert as $elem){
		// Adaptation des valeurs pour la base de données
		$lastmodified = date("Y-m-d H:i:s",strtotime($elem['LAST-MODIFIED']));
		$demande= array_key_exists("CREATED",$elem) ? date("Y-m-d H:i:s",strtotime($elem['CREATED'])) : $lastmodified;

		$debut = date("Y-m-d H:i:s", strtotime($elem["DTSTART_tz"]));

		// Les événements ICS sur des journées complètes ont comme date de fin J+1 à 0h00
		// Donc si la date de fin est à 0h00, on retire une seconde pour la rammener à J
		$offset = date("H:i:s", strtotime($elem["DTEND_tz"])) == "00:00:00" ? "-1 second" : null;
		$fin = date("Y-m-d H:i:s", strtotime($elem["DTEND_tz"]." $offset"));

		$commentaires = isset($elem['SUMMARY']) ? $elem['SUMMARY'] : null;
		if(array_key_exists("DESCRIPTION",$elem)){
		  $commentaires.="<br/>\n".$elem['DESCRIPTION'];
		}
		
		// Insertion dans la base de données
		$tab=array(":perso_id" => $perso_id, ":debut" => $debut, ":fin" => $fin, ":demande" => $demande, ":valide"=> "99999", ":validation" => $lastmodified, ":valide_n1"=> "99999", 
		  ":validation_n1" => $lastmodified, ":motif" => $this->pattern, ":motif_autre" => $this->pattern, ":commentaires" => $commentaires, ":cal_name" => $calName, ":ical_key" => $elem['key']);
		  
		$db->execute($tab);
		$nb++;
      }
    }

    if($this->logs){
      logs("$nb événement(s) importé(s)", "ICS", $CSRFToken);
    }

  }

}