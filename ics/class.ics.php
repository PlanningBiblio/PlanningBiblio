<?php
/**
Planning Biblio, Version 2.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : ics/class.ics.php
Création : 29 mai 2016
Dernière modification : 29 mai 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe permettant le traitement des fichiers ICS 
*/


/**
 * @note : 
 * Clés pour la MAJ de la base de données : UID + LAST-MODIFIED
 * - Si UID n'existe pas dans la base : INSERT (voir fonctionnement de UPDATE INTO)
 * - Si UID existe et LAST-MODIFIED ICS > LAST-MODIFIED BDD => UPDATE
 * à tester : récurrences : voir EXDATE et RECURRENCE-ID, RRUle
 * RRULE => FREQ=WEEKLY;COUNT=6;BYDAY=TU,TH
 * RRULE => FREQ=WEEKLY;UNTIL=20150709T073000Z;BYDAY=MO,TU,WE,TH
 * EXDATE : exception dates
 */
 
/*
 if(!isset($version)){
  include_once "../include/accessDenied.php";
}
*/
// TEST
$version="test";

require_once "include/config.php";

class CJICS{

  public $calendar=null;
  public $events=null;
  public $error=null;
  public $src=null;

  
  /**
   * @function parse
   * Parse les événements d'un fichier ICS : créé un tableaux PHP contenant les événements
   * @param string $this->src : fichier ICS : chemin relatif ou absolu ou URL
   * @result array $this->calendar : informations sur le calendrier ICS parsé
   * @result array $this->events : tableaux des événements
   */
  public function parse(){
    if(!$this->src){
      $this->error="Fichier ICS absent";
      return false;
    }

    // Ouverture du fichier ICS
    $Fnm=$this->src;
    ini_set("auto_detect_line_endings", "1");

    $inF=fopen($Fnm,"r");

    if(!$inF){
      $this->error="Impossible d'ouvrir le fichier";
      return false;
    }
    
    // Construction du tableau $events contenant les événements
    $events=array();
    $id=0;
    // Lecture du fichier ICS, Pour chaque ligne ...
    while($line=fgets($inF)){

      // $done : indique si la ligne a été traitée, de façon la laisser de coté et passer à la ligne suivante (équivalent du "continue" d'une boucle for)
      $done=false;;
      
      // Si BEGIN;VEVENT : création d'un nouvel événement : nouvelle entrée dans le tableau $events
      if(substr($line,0,12)=="BEGIN:VEVENT"){
	// Incrémentation de l'id, clé du tableau events
	$id++;
	// Initialisation de la nouvelle entrée
	$events[$id]=array();
      }
      
      // Récupération des éléments qui ont débordés sur plusieurs lignes
      // Ces éléments sont réperés en fonction de la casses des premières lettres de la ligne.
      // Si elles ne sont pas en majuscules, la ligne ne commence pas par un index, donc c'est un débordement.
      if(!ctype_upper(substr($line,0,3)) and !in_array(substr($line,0,3),array("X-W","X-L")) and isset($key)){
	if(!is_array($events[$id][$key])){
	  $events[$id][$key].=$line;
	}else{
	  $tmp=array_keys($events[$id][$key]);
	  $tmp=$tmp[count($tmp)-1];
	  $events[$id][$key][$tmp].=$line;
	}
	$done=true;
      }
      
      
      // On n'enregistre pas les lignes BEGIN/END:VEVENT, BEGIN/END:VALARM, END:VCALENDAR, etc.
      // Donc, si la ligne ne correspond pas à ces critères ...
      // if(substr($line,0,6) != "BEGIN:" and substr($line,0,4) != "END:" and substr($line,0,5) != "RRULE" and !$done){
      if(substr($line,0,6) != "BEGIN:" and substr($line,0,4) != "END:" and !$done){
	// ... on créé on nouvelle entrée dans le tableau "événement" en définissant une clé (SUMMARY, UID, etc.) et une valeur (string ou array selon le cas)
	if(substr($line,0,7)=="DTSTART"){
	  $key="DTSTART";
	  $value=substr($line,strlen($key)+1);
	  $value=ICSDateConversion($value);
	}elseif(substr($line,0,10)=="TZOFFSETFR"){
	  $key="TZOFFSETFROM";
	  $value=substr($line,strlen($key)+1);
	}elseif(substr($line,0,10)=="TZOFFSETTO"){
	  $key="TZOFFSETTO";
	  $value=substr($line,strlen($key)+1);
	}elseif(substr($line,0,10)=="X-LIC-LOCA"){
	  $key="X-LIC-LOCATION";
	  $value=substr($line,strlen($key)+1);
	}elseif(substr($line,0,10)=="X-WR-CALID"){
	  $key="X-WR-CALID";
	  $value=substr($line,strlen($key)+1);
	}elseif (substr($line,0,10)=="X-WR-CALNA"){
	  $key="X-WR-CALNAME";
	  $value=substr($line,strlen($key)+1);
	}elseif(substr($line,0,10)=="X-WR-TIMEZ"){
	  $key="X-WR-TIMEZONE";
	  $value=substr($line,strlen($key)+1);
	}

	else{
	  switch(substr($line,0,3)){
	    // Valeurs simples
	    case "ATT" : $key="ATTENDEE";	$value=substr($line,strlen($key)+1);	break;
	    case "CAL" : $key="CALSCALE";	$value=substr($line,strlen($key)+1); 	break;
	    case "CLA" : $key="CLASS";		$value=substr($line,strlen($key)+1); 	break;
	    case "DES" : $key="DESCRIPTION";	$value=substr($line,strlen($key)+1); 	break;
	    case "LOC" : $key="LOCATION";	$value=substr($line,strlen($key)+1); 	break;
	    case "MET" : $key="METHOD";		$value=substr($line,strlen($key)+1); 	break;
	    case "PRO" : $key="PRODID";		$value=substr($line,strlen($key)+1); 	break;
	    case "SEQ" : $key="SEQUENCE";	$value=substr($line,strlen($key)+1); 	break;
	    case "STA" : $key="STATUS";		$value=substr($line,strlen($key)+1); 	break;
	    case "SUM" : $key="SUMMARY";	$value=substr($line,strlen($key)+1); 	break;
	    case "TRA" : $key="TRANSP";		$value=substr($line,strlen($key)+1); 	break;
	    case "TZI" : $key="TZID";		$value=substr($line,strlen($key)+1); 	break;
	    case "TZN" : $key="TZNAME";		$value=substr($line,strlen($key)+1); 	break;
	    case "UID" : $key="UID";		$value=substr($line,strlen($key)+1); 	break;
	    case "VER" : $key="VERSION";	$value=substr($line,strlen($key)+1); 	break;
	    case "X-M" : $key="X-MICROSOFT-CDO-INTENDEDSTATUS";	$value=substr($line,strlen($key)+1); 	break;

	    // Dates
	    case "CRE" : $key="CREATED";	$value=substr($line,strlen($key)+1); 	$value=ICSDateConversion($value);	break;
	    case "DTE" : $key="DTEND";		$value=substr($line,strlen($key)+1);	$value=ICSDateConversion($value);	break;
	    case "DTS" : $key="DTSTAMP";	$value=substr($line,strlen($key)+1);	$value=ICSDateConversion($value);	break;
	    case "EXD" : $key="EXDATE";		$value=substr($line,strlen($key)+1);    $value=ICSDateConversion($value);    	break;
	    case "LAS" : $key="LAST-MODIFIED";	$value=substr($line,strlen($key)+1); 	$value=ICSDateConversion($value);	break;
	    case "REC" : $key="RECURRENCE-ID";	$value=substr($line,strlen($key)+1);	$value=ICSDateConversion($value);	break;


	    // ORGANIZER : Nom et e-mail de l'organisateur
	    case "ORG" : $key="ORGANIZER";
			$value=substr($line,strlen($key)+1);
			$tmp=explode(":",$value);
			$cn=str_replace("CN=",null,$tmp[0]);
			$value=array("CN"=>trim($cn), "mail"=>trim($tmp[2]));
			break;
	    // Recurrency rules
	    case "RRU" : $key="RRULE";
			$value=substr($line,strlen($key)+1);
			$tmp=explode(";",$value);
			$tmp3=array();
			foreach($tmp as $elem){
			  $tmp2=explode("=",$elem);
			  $tmp3[$tmp2[0]]=trim($tmp2[1]);
			}
			if(array_key_exists("UNTIL",$tmp3)){
			  $tmp3["UNTIL"]=ICSDateConversion($tmp3["UNTIL"]);
			}
			$value=$tmp3;
			break;

	    default : $key="Undefined";	$value=$line;	break;
	  }
	}
	
	if(!is_array($value)){
	  $value=trim($value);
	}
	
	// Les informations sont ajoutés dans le tableau $events et liés à l'événement auquel elles appartiennent grace à la clé $id
	// Il peut y avoir plusieurs valeurs pour les champs ATTENDEE, EXDATE, RECURRENCE-ID, donc insertion sous forme de tableau
	if(in_array($key,array("ATTENDEE","EXDATE","RECURRENCE-ID","Undefined"))){
	  $events[$id][$key][]=$value;
	// Pour les autres, insertion sous forme d'une chaine de caractère
	}else{
	  $events[$id][$key]=$value;
	}
	
      }
      
      // Les informations VALARM ont été ignorées plus haut, on les traite ici
      // Si la ligne est BEGIN:VALARM ...
      elseif(substr($line,0,12) == "BEGIN:VALARM"){
	// ... On créé un tableau $alarm
	$alarm=array();
	// Et tant que END:VALARM n'est pas trouvé ...
	while($line=fgets($inF) and substr($line,0,10) != "END:VALARM"){
	  // ... On ajoute les éléments dans ce tableau, en définissant les clés de ce dernier      
	  if(substr($line,0,3)=="UID"){
	    $aKey="UID";
	    $value=substr($line,strlen($aKey)+1);
	  }else{
	    switch(substr($line,0,5)){
	      case "ACTIO" : $aKey="ACTION";			$value=substr($line,strlen($aKey)+1); 	break;
	      case "ATTAC" : $aKey="ATTACH";			$value=substr($line,strlen($aKey)+1); 	break;
	      case "ATTEN" : $aKey="ATTENDEE";			$value=substr($line,strlen($aKey)+1); 	break;
	      case "ACKNO" : $aKey="ACKNOWLEDGED";		$value=substr($line,strlen($aKey)+1); 	break;
	      case "DESCR" : $aKey="DESCRIPTION";		$value=substr($line,strlen($aKey)+1); 	break;
	      case "SUMMA" : $aKey="SUMMARY";			$value=substr($line,strlen($aKey)+1); 	break;
	      case "X-APP" : $aKey="X-APPLE-DEFAULT-ALARM";	$value=substr($line,strlen($aKey)+1); 	break;
	      case "X-WR-" : $aKey="X-WR-ALARMUID";		$value=substr($line,strlen($aKey)+1); 	break;
	      
	      case "TRIGG" : $aKey="TRIGGER";
			  $value=substr($line,strlen($aKey)+1);
			  $tmp=explode(":",$value);
			  $rel=str_replace('RELATED=',null,$tmp[0]);
			  $value=array("RELATED"=>$rel, "time"=>$tmp[1]);
			  break;

	      default : $aKey="Undefined";	$value=$line;	break;
	    }
	  }

	  if(in_array($aKey,array("Undefined"))){
	    $alarm[$aKey][]=$value;
	  }else{
	    $alarm[$aKey]=$value;
	  }

	}

	// On ajoute le tableau $alarm dans le tableau $events en le liant à l'événement auquel il appartient grace à la clé $id
	$events[$id]["VALARM"]=$alarm;
      }
      
      // Si la ligne est END:VEVENT : On termnie l'événement : on remplace son id temporaire ($id) par son UID
      elseif(substr($line,0,10) == "END:VEVENT"){
	// Création de tableaux pour le champ "ATTENDEE"
	// On le fait une fois que l'événement est bien constitué pour éviter les problèmes avec les débordements sur plusieurs lignes
	if(array_key_exists("ATTENDEE",$events[$id])){
	  foreach($events[$id]["ATTENDEE"] as $k => $v){
	    $value=array();
	    
	    // Récupération du mail (se trouve après :mailto: avec google)
	    $tmp=explode(":mailto:",$v);
	    $value["mail"]=$tmp[1];
	    
	    // Récupération des autres informations. Elles sont séparées par des ; sous cette forme : KEY:VALUE
	    $tmp=explode(";",$tmp[0]);
	    foreach($tmp as $elem){
	      $tmp2=explode("=",$elem);
	      $value[$tmp2[0]]=$tmp2[1];
	    }
	    $events[$id]["ATTENDEE"][$k]=$value;
	  }
	}
      
      
	// On remplace l'id temporairement ($id) de l'événement par son UID
	if(array_key_exists($id,$events) and array_key_exists("UID",$events[$id]) and !empty($events[$id]["UID"])){
	  $uid=$events[$id]["UID"];
	  $events[$uid]=$events[$id];
	  unset($events[$id]);
	}
      }
      
    }
    // On ferme le fichier
    fclose($inF);


    // Le premier élément du tableau contient les premières lignes du fichier ICS.
    // Ce sont des informations générales relatives à l'ensemble des événements (timezone, etc.) et non un événement en particulier.
    // On transfert ces éléments dans un tableau $calendar
    $calendar=$events[0];
    unset($events[0]);


    // Tri des événements par dates
    uasort($events,"cmp_DTStart_Desc");
    
    $this->calendar=$calendar;
    $this->events=$events;
    
  }
  
  /**
   * @function updateDB
   * Enregistre les nouveaux événements d'un fichier ICS dans la base de données
   * Met à jour les événements modifiés
   * Marque les événements supprimés
   * @param string @this->src
   * @note : utilise la method $this->parse pour la lecture des fichiers ICS
   */
  public function updateDB(){
    if(!$this->src){
      $this->error="Fichier ICS absent";
      return false;
    }
    
    // Lit le fichier ICS et le parse
    $this->parse();
    $calendar=$this->calendar;
    $events=$this->events;
    
    $calName=$calendar['X-WR-CALNAME'];

    // Pour chaque événement
    // Si l'événement n'existe pas dans la base de données, on l'insère
    // Si l'événement existe et qu'il a été modifié (comparaison des champs LAST-MODIFIED), on le met à jour.
    $insert=array();
    $update=array();
    $uidsDB=array();
    
    
    // TODO : A continuer : Ajouter les autres champs dans la base de données
    // TODO : Ajouter des index sur les champs CALNAME et UID
    $keys=array("UID","DESCRIPTION","LOCATION","SUMMARY","SEQUENCES","STATUS","DTSTART","DTEND","DTSTAMP","CREATED","LAST-MODIFIED","RRULE");
    
    // Recherche des événements enregistrés dans la base de données
    $calDB=array();
    $db=new db();
    $db->select2("ics",null,array("CALNAME"=>$calName));
    if($db->result){
      foreach($db->result as $elem){
	// Evénéments de la base de données
	$calDB[$elem['UID']]=$elem;
	// Listes des UIDs enregistrés dans la base de données
	$uidsDB[]=$elem['UID'];
      }
    }

    // Pour chaque événment du fichier ICS
    foreach($this->events as $event){
      // Si le status n'est pas confimé, on ignore l'événement
      if(!in_array($event["STATUS"],array("CONFIRMED"))){
	continue;
      }

      // Si l'événement n'est pas dans la base de données ou s'il a été modifié
      // On copie les données dans les tableaux $insert ou $update
      if(!in_array($event["UID"],$uidsDB)
	or $event['LAST-MODIFIED']>$calDB[$event['UID']]['LASTMODIFIED']){

	$tmp=array(":CALNAME"=>$calName);
	foreach($keys as $k){
	  // Symbole - problématique avec PDO-SQL, on le supprime dans les champs MySQL
	  $k1=str_replace("-",null,$k);
	  if(is_array($event[$k]) and array_key_exists("YMDTime",$event[$k])){
	    $tmp[":$k1"]=$event[$k]["YMDTime"];
	  }elseif(is_array($event[$k])){
	    $tmp[":$k1"]=json_encode($event[$k]);
	  }elseif($event[$k]){
	    $tmp[":$k1"]=$event[$k];
	  }else{
	    $tmp[":$k1"]="";
	  }
	}

	// Si l'événement n'est pas dans la base de données, on l'insère
	if(!in_array($event["UID"],$uidsDB)){
	  $insert[]=$tmp;

	// Si l'événement est dans la base de données et qu'il a été modifié, on le met à jour
	}else{
	  $update[]=$tmp;
	}
      }
    }
    
    
    // Insertion des nouveaux événments
    if(!empty($insert)){
    
      // TODO : A TESTER
      // TODO : A TESTER
      // TODO : A TESTER

      $k=array_keys($insert[0]);
      $fields="`".implode("`, `",$k)."`";
      $fields=str_replace(":",null,$fields);
      $values=implode(", ",$k);
      
      $req="INSERT INTO `{$GLOBALS['dbprefix']}ics` ($fields) VALUES ($values);";
      $db=new dbh();
      $db->prepare($req);
      foreach($insert as $elem){
	$db->execute($elem);
      }
    }
    
    // Mise à jour des événements modifiés
    if(!empty($update)){

      // TODO : A TESTER
      // TODO : A TESTER
      // TODO : A TESTER

      $set=array();
      $k=array_keys($update[0]);
      foreach($k as $value){
	if(in_array($value,array(":CALNAME",":UID"))){
	  continue;
	}
	$field=str_replace(":",null,$value);
	$set[]="`$field`=$value";
      }
      $set=implode(", ",$set);
      
      $req="UPDATE `{$GLOBALS['dbprefix']}ics` set $set WHERE `CALNAME`=:CALNAME AND `UID`=:UID ;";
      $db=new dbh();
      $db->prepare($req);
      foreach($update as $elem){
	$db->execute($elem);
      }
    }
    
    
    
    // Lecture de la base de données à la recherche d'événements supprimés (qui ne sont plus dans le fichier ICS)

  
  
  
  }
}

function cmp_DTStart_Desc($a,$b){
  if(!array_key_exists("DTSTART",$a) or !array_key_exists("DTSTART",$b)){
    return 0;
  }
  if(strcmp($a["DTSTART"]["Time"],$b["DTSTART"]["Time"])==0 and array_key_exists("DTEND",$a) and array_key_exists("DTEND",$b)){
    return (int) strcmp($b["DTEND"]["Time"],$a["DTEND"]["Time"]);
  }
  return (int) strcmp($b["DTSTART"]["Time"],$a["DTSTART"]["Time"]);
}

function ICSDateConversion($value){
  // Avec Zimbra, le TimeZone est renseigné dans ce champ. On créé donc un tableau array(TZID, time)
  // Avec Google, le timeZone n'est pas spécifié
  $value=str_replace(array('VALUE=DATE:','"'),null,$value);
  
  if(substr($value,0,5)=="TZID="){
    $tmp=explode(":",$value);
    $tz=str_replace(array('TZID=','"'),null,$tmp[0]);
    $time=strtotime($tmp[1]);
    $value=array("TZID"=>$tz, "DTime"=>$tmp[1], "Time"=>$time, "YMDTime"=> date("Y-m-d H:i:s",$time));
  }else{
    $time=strtotime($value);
    $value=array("TZID"=>null, "DTime"=>$value, "Time"=>$time, "YMDTime"=> date("Y-m-d H:i:s",$time));
  }
  return $value;
}
