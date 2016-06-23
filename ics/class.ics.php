<?php
/**
Planning Biblio, Version 2.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : ics/class.ics.php
Création : 29 mai 2016
Dernière modification : 23 juin 2016
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
 *   $i->updateTable();
 * }
 *
 * @note : 
 * Clés pour la MAJ de la base de données : UID + LAST-MODIFIED
 * - Si UID n'existe pas dans la base : INSERT (voir fonctionnement de UPDATE INTO)
 * - Si UID existe et LAST-MODIFIED ICS > LAST-MODIFIED BDD => UPDATE
 * à tester : récurrences : voir EXDATE et RECURRENCE-ID, RRUle
 * RRULE => FREQ=WEEKLY;COUNT=6;BYDAY=TU,TH
 * RRULE => FREQ=WEEKLY;UNTIL=20150709T073000Z;BYDAY=MO,TU,WE,TH
 * EXDATE : exception dates
 */
 

// TODO : loguer les imports / Modifs dans la table logs

// TODO : Modification d'une récurrence : si l'option "les éléments suivants" est choisie lors de la modification d'un événément récurrent, un nouvel élément ICS est créé avec 
// un UID du type uid_origine_rev_date ... PB l'événement initial reste tel quel et ça créé des doublons erronés :  A Vérifier : les noveaux index iCalKey ont peut être réglé ce problème
// TODO : comparrer les éléments ayant la même base (UID avant _R), supprimer de l'élément d'origine les dates traités par la révision :  A Vérifier : les noveaux index iCalKey ont peut être réglé ce problème
// 4l0hmqags1s23hqgomago8vi74_R20160708T073000@google.com
// 4l0hmqags1s23hqgomago8vi74@google.com
// TODO : Modification d'un" récurrence : si l'option "uniquement cet élément" est chosie lors de la modifcation d'un événement récurrent, un nouvel élément ICS est créé avec le même UID est une date de modifcation différente
// PB : l'un des 2 éléments est ignoré :  A Vérifier : les noveaux index iCalKey ont peut être réglé ce problème
/*
BEGIN:VEVENT
DTSTART;TZID=Europe/Paris:20160708T093000
DTEND;TZID=Europe/Paris:20160708T120000
DTSTAMP:20160617T225529Z
UID:6pah8kq546frnqrtce857jf9n4@google.com
RECURRENCE-ID;TZID=Europe/Paris:20160708T093000
CREATED:20160617T225057Z
DESCRIPTION:
LAST-MODIFIED:20160617T225211Z
LOCATION:
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:Mailman ordi petit salon test test
TRANSP:OPAQUE
END:VEVENT

BEGIN:VEVENT
DTSTART;TZID=Europe/Paris:20160701T093000
DTEND;TZID=Europe/Paris:20160701T120000
RRULE:FREQ=WEEKLY;COUNT=3;BYDAY=FR
DTSTAMP:20160617T225529Z
UID:6pah8kq546frnqrtce857jf9n4@google.com
CREATED:20160617T225057Z
DESCRIPTION:
LAST-MODIFIED:20160617T225057Z
LOCATION:
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:Mailman ordi petit salon test
TRANSP:OPAQUE
END:VEVENT
*/

// TEST
$version="test";

require_once "$path/include/config.php";
require_once "$path/vendor/ics-parser/class.iCalReader.php";

class CJICS{

  public $error=null;
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
    $perso_id=$this->perso_id;	// perso_id
    $table=$this->table;	// Table à mettre à jour
    $src=$this->src;		// Fichier ICS
    $iCalKeys=array();  	// Clés des événements confirmés et occupés du fichier ICS
    $tableKeys=array();		// Clés des événements ICS de la table $table
    $calName=null;		// Nom du calendrier
    $deleted=array();		// Evénements supprimés du fichier ICS ou événements modifiés
    $insert=array();		// Evénements à insérer (nouveaux ou événements modifiés (suppression + réinsertion))

    // TEST
    echo "CJICS::UpdateTable Start \nTable :$table \nPerso : $perso_id\nURL : $src\n";

    // TEST
    // Parse le fichier ICS, le tableaux $events contient les événements du fichier ICS
    $ical   = new ICal($src, "MO");
    $events = $ical->events();
    
    if(!is_array($events)){
      exit;
    }
    
    // TEST
    
    // Récupération du nom du calendrier
    $calName=$ical->calendarName();
    $calTimeZone = $ical->calendarTimezone();
    echo "Calendar : $calName \n";
    echo "Timezone : $calTimeZone \n\n";
    
    
    // Ne garde que les événements confirmés et occupés et rempli le tableau $iCalKeys
    $tmp=array();
    
    // $uid_dtstart et $uid_dtstart2 : permet de supprimer les exceptions ajoutées sur les récurrences
    $uid_dtstart=array();
    $uid_dtstart2=array();
    
    foreach($events as $elem){
      $key=$elem['UID']."_".$elem['DTSTART']."_".$elem['LAST-MODIFIED'];
      $tmp[]=array_merge($elem,array("key"=>$key));
      
      if(in_array($elem['UID']."_".$elem['DTSTART'],$uid_dtstart)){
	$uid_dtstart2[]=$elem['UID']."_".$elem['DTSTART'];
      }
      $uid_dtstart[]=$elem['UID']."_".$elem['DTSTART'];
    }
    
    $events=array();
    foreach($tmp as $elem){
      // permet de supprimer les exceptions ajoutées sur les récurrences
      if(in_array($elem['UID']."_".$elem['DTSTART'],$uid_dtstart2) and array_key_exists("RRULE",$elem)){
	continue;
      }
      if($elem['STATUS']=="CONFIRMED" and $elem['TRANSP']=="OPAQUE"){
	$events[]=$elem;
   	$iCalKeys[]=$elem['key'];
      }
    }

    // Recherche les événements correspondant au calendrier $calName et à l'agent $perso_id dans la table $table
    $db=new db();
    $db->select2($table,null,array("CALNAME"=> "$calName","perso_id"=>$perso_id));
    if($db->result){
      // Pour chaque événement
      foreach($db->result as $elem){
	// Si l'évenement n'est plus dans le fichier ICS ou s'il a été modifié dans le fichier ICS, on le supprime : complète le tableau $delete
	if(!in_array($elem['iCalKey'],$iCalKeys)){
	  $deleted[]=array(":id"=>$elem['id']);
	}else{
	  // Sinon, on complète le table $tableKeys avec la clé de l'évenement pour ne pas le réinsérer dans la table
	  $tableKeys[]=$elem['iCalKey'];
	}
      }
    }
    
    // Suppression des événements supprimés ou modifiés de la base de données
    if(!empty($deleted)){
      $db=new dbh();
      $db->prepare("DELETE FROM `{$GLOBALS['dbprefix']}$table` WHERE `id`=:id;");
      foreach($deleted as $elem){
	$db->execute($elem);
      }
    }
    
    // Insertion des nouveux éléments ou des éléments modifiés dans la table $table : complète le tableau $insert
    foreach($events as $elem){
      if(!in_array($elem['key'],$tableKeys)){
	$insert[]=$elem;
      }
    }
      
    // Insertion des nouveux éléments ou des éléments modifiés dans la table $table : insertion dans la base de données
    if(!empty($insert)){
      $db=new dbh();
      $req="INSERT INTO `{$GLOBALS['dbprefix']}$table` (`perso_id`, `debut`, `fin`, `demande`, `valide`, `validation`, `valideN1`, `validationN1`, `motif`, `motif_autre`, `commentaires`, `CALNAME`, `iCalKey`) 
	VALUES (:perso_id, :debut, :fin, :demande, :valide, :validation, :valideN1, :validationN1, :motif, :motif_autre, :commentaires, :CALNAME, :iCalKey);";
      $db->prepare($req);

      $i=0;
      foreach($insert as $elem){
	// Adaptation des valeurs pour la base de données
	$lastmodified = date("Y-m-d H:i:s",strtotime($elem['LAST-MODIFIED']));
	$demande= array_key_exists("CREATED",$elem) ? date("Y-m-d H:i:s",strtotime($elem['CREATED'])) : $lastmodified;

	$debut = date("Y-m-d H:i:s", strtotime($elem["DTSTART_tz"]));
	$fin = date("Y-m-d H:i:s", strtotime($elem["DTEND_tz"]));

	$commentaires = $elem['SUMMARY'];
	if(array_key_exists("DESCRIPTION",$elem)){
	  $commentaires.="<br/>\n".$elem['DESCRIPTION'];
	}
	
	// Insertion dans la base de données
	$tab=array(":perso_id" => $perso_id, ":debut" => $debut, ":fin" => $fin, ":demande" => $demande, ":valide"=> "99999", ":validation" => $lastmodified, ":valideN1"=> "99999", 
	  ":validationN1" => $lastmodified, ":motif" => "Import ICS", ":motif_autre" => "Import ICS", ":commentaires" => $commentaires, ":CALNAME" => $calName, ":iCalKey" => $elem['key']);
	  
	$db->execute($tab);
	$i++;
      }
      
      // TEST
      echo "Events inserted : $i\n";
    }

  }

}

