<?php
/**
* @param id : perso_id required
* @param code : codeICS optional
*
* TODO
* tester avec google, zimbra
*
* TODO
* Faire une autre requete pour afficher par poste, dans ce cas les noms et prénoms seront affichés dans summary
*  cette autre requete sera utilisée si le @param id n'existe pas et si @param poste à la place
* TODO
* @file class.ics.php : @class ics, @function icsdate, @function $begin, @function end, @function event
* TODO
* @file maj.php, @file setup/db_structure : personnel/codeICS
* @file maj.php, @file setup/db_structure : config/codeICS pour postes, un code par site
*/

// TODO :Ne pas importer les absents (voir requete $absencesDB des stats)
// TODO :Ne pas importer ceux en congés (plugin) (faire comme pour les absences)
// TODO : recherche par login et par email, en plus de perso_id (accepte paramètres en entrée id, login, email)


function icsdate($date){
  $date = date("Ymd\THis", strtotime($date));
  return $date;
}

$version="ics";
require_once "../include/config.php";
require_once "../include/function.php";
require_once "../personnel/class.personnel.php";
require_once "../postes/class.postes.php";

$url=$_SERVER['SERVER_NAME'];
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$code=filter_input(INPUT_GET,"code",FILTER_SANITIZE_STRING);

// Recherche des plages de service public de l'agent
$db=new db();
$db->selectInnerJoin(array("pl_poste","perso_id"), array("personnel","id"), array("date", "debut", "fin", "poste", 'site', 'absent', 'supprime'), array(), 
  array("perso_id"=>$id), array("codeICS"=>$code), "ORDER BY `date` DESC, `debut` DESC, `fin` DESC");
if($db->result){
  $planning = $db->result;
}

// Recherche des postes pour affichage du nom des postes
$p=new postes();
$p->fetch();
$postes=$p->elements;

// Liste des sites
if($config['Multisites-nombre'] > 1){
  $sites = array();
  for($i=1; $i<=$config['Multisites-nombre']; $i++){
    $sites[$i] = html_entity_decode($config["Multisites-site$i"],ENT_QUOTES|ENT_IGNORE,'UTF-8');
  }
}

// Recherche des plannings verrouillés pour exclure les plages concernant des plannings en attente
$verrou = array();
$db = new db();
$db->select2("pl_poste_verrou",null,array('verrou2'=>'1'));
if($db->result){
  foreach($db->result as $elem){
    $verrou[$elem['date'].'_'.$elem['site']] = array('date'=>$elem['validation2'], 'agent'=>$elem['perso2']);
  }
}

// Nom de l'agent pour X-WR-CALNAME
$agent = nom($id);

// Tableaux contenant les noms et emails de tous les agents, permet de renseigner le champ ORGANIZER avec le nom de l'agent ayant vérrouillé le planning
$p = new personnel();
$p->supprime = array(0,1,2);
$p->fetch();
$agents=$p->elements;

// Tableau $ical
$ical=array();
$ical[]="BEGIN:VCALENDAR";
$ical[]="X-WR-CALNAME:Service Public $agent";
$ical[]="PRODID:Planning-Biblio-Calendar";
$ical[]="VERSION:2.0";
$ical[]="METHOD:PUBLISH";
$ical[]="BEGIN:VTIMEZONE";
$ical[]="TZID:Europe/Paris";
$ical[]="BEGIN:STANDARD";
$ical[]="DTSTART:16010101T030000";
$ical[]="TZOFFSETTO:+0100";
$ical[]="TZOFFSETFROM:+0200";
$ical[]="RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=10;BYDAY=-1SU";
$ical[]="TZNAME:CET";
$ical[]="END:STANDARD";
$ical[]="BEGIN:DAYLIGHT";
$ical[]="DTSTART:16010101T020000";
$ical[]="TZOFFSETTO:+0200";
$ical[]="TZOFFSETFROM:+0100";
$ical[]="RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=3;BYDAY=-1SU";
$ical[]="TZNAME:CEST";
$ical[]="END:DAYLIGHT";
$ical[]="END:VTIMEZONE";

$tab = array();
$i=0;
if(isset($planning)){
  // Exclusion des planning non validés : A testet
  foreach($planning as $elem){
    if(!array_key_exists($elem['date'].'_'.$elem['site'], $verrou)){
      continue;
    }
    
    if(isset($tab[$i-1])
      and $tab[$i-1]['debut'] == $elem['fin'] 
      and $tab[$i-1]['poste'] == $elem['poste'] 
      and $tab[$i-1]['site'] == $elem['site'] 
      and $tab[$i-1]['supprime'] == $elem['supprime'] 
      and $tab[$i-1]['absent'] == $elem['absent']){
      $tab[$i-1]['debut'] = $elem['debut'];
    } else {
      $tab[$i++] = $elem;
    }
  }

  foreach($tab as $elem){
    $debut = icsdate($elem['date']." ".$elem['debut']);
    $fin = icsdate($elem['date']." ".$elem['fin']);
    // Nom du poste pour SUMMARY
    $poste = html_entity_decode($postes[$elem['poste']]['nom'],ENT_QUOTES|ENT_IGNORE,'UTF-8');
    // Site et étage pour LOCATION
    $site = isset($sites) ? $sites[$elem['site']] : null;
    $etage = $postes[$elem['poste']]['etage'] ? ' '.html_entity_decode($postes[$elem['poste']]['etage'],ENT_QUOTES|ENT_IGNORE,'UTF-8') : null;
    // Validation pour LAST-MODIFIED et DSTAMP
    $validation = gmdate("Ymd\THis\Z", strtotime($verrou[$elem['date'].'_'.$elem['site']]['date']));
    // ORGANIZER
    $organizer = null;
    if(isset($agents[$verrou[$elem['date'].'_'.$elem['site']]['agent']])){
      $tmp = $agents[$verrou[$elem['date'].'_'.$elem['site']]['agent']];
      $organizer = $tmp['prenom'].' '.$tmp['nom'];
      $organizer .= ':mailto:'.$tmp['mail'];
    }
    
    $ical[]="BEGIN:VEVENT";
    $ical[]="UID:" . md5(uniqid(mt_rand(), true)) . "@$url";
    $ical[]="DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z";
    $ical[]="DTSTART;TZID=Europe/Paris:$debut";
    $ical[]="DTEND;TZID=Europe/Paris:$fin";
    $ical[]="SUMMARY:$poste";
    if($organizer){
      $ical[]="ORGANIZER;CN=$organizer";
    }
    $ical[]="LOCATION:{$site}{$etage}";
    $ical[]="STATUS:CONFIRMED";
    $ical[]="CLASS:PUBLIC";
    $ical[]="X-MICROSOFT-CDO-INTENDEDSTATUS:BUSY";
    $ical[]="TRANSP:OPAQUE";
    $ical[]="LAST-MODIFIED:$validation";
    $ical[]="DTSTAMP:$validation";
    $ical[]="END:VEVENT";
  }
}

$ical[]="END:VCALENDAR";

$ical=join("\n",$ical);

//set correct content-type-header
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=calendar.ics');
echo $ical;
exit;