<?php
/**
Planning Biblio, Version 2.6.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : ics/absences.php
Création : juillet 2022
Dernière modification : juillet 2022
@author Arthur Suzuki <arthur.suzuki@biblibre.com>

Description :
Ce fichier génère un calendrier ICS par agent.
Utilisation : renseigner l'adresse http(s)://votre_serveur/votre_planning/ics/absences.php&login=login_de_l_agent dans le champ URL de votre client ICS (Thunderbird, Outlook, Google Calendar, etc.)
Vous pouvez également utiliser les paramètres 'mail' et 'id' pour identifier l'agent (http(s)://votre_serveur/votre_planning/ics/calendar.php&mail=e-mail_de_l_agent et http(s)://votre_serveur/votre_planning/ics/calendar.php&id=id_de_l_agent)
Le calendrier contenant les absences validées sera retourné.
Vous devez activier le paramètre ICS-Export dans le menu Administration / Configuration / ICS
*
* @param int id : ID de l'agent
* @param string login : login de l'agent
* @param string mail : e-mail de l'agent
* @param code : codeICS (option) : Code permettant de rendre privé le fichier ICS
* au moins l'un des paramètres id, login et mail est requis.
*/

// TODO : Protection par code, générer des codes pour les agents existants, générer des codes à chaque ajout/importation, afficher ces codes des les fichiers agents.
// TODO : config : activer/désactiver la génération des fichiers ICS: désactivé par défaut

$version="ics";
require_once "../include/config.php";
require_once "../include/function.php";
require_once "../absences/class.absences.php";
require_once "../personnel/class.personnel.php";
require_once "../postes/class.postes.php";
require_once __DIR__ . '/../init_entitymanager.php';

$CSRFToken = CSRFToken();

if(!$config['ICS-Export']){
  logs("L'exportation ICS est désactivée","ICS Export Absences", $CSRFToken);
  exit;
}

$url=$_SERVER['SERVER_NAME'];
$code=filter_input(INPUT_GET,"code",FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$login=filter_input(INPUT_GET,"login",FILTER_SANITIZE_STRING);
$mail=filter_input(INPUT_GET,"mail",FILTER_SANITIZE_EMAIL);

// Définission de l'id de l'agent si l'argument login est donné
if(!$id and $login){
  $db = new db();
  $db->select2('personnel','id',array('login'=>$login));
  if($db->result){
    $id = $db->result[0]['id'];
  }
  else{
    logs("Impossible de trouver l'id associé au login $login","ICS Export Absences", $CSRFToken);
    exit;
  }
}

// Définission de l'id de l'agent si l'argument mail est donné
if(!$id and $mail){
  $db = new db();
  $db->select2('personnel','id',array('mail'=>$mail));
  if($db->result){
    $id = $db->result[0]['id'];
  }
  else{
    logs("Impossible de trouver l'id associé au mail $mail","ICS Export Absences", $CSRFToken);
    exit;
  }
}

if(!$id){
  logs("L'id de l'agent n'est pas fourni","ICS Export Absences", $CSRFToken);
  exit;
}

logs("Exportation des absences de l'agent #$id","ICS Export Absences", $CSRFToken);


// N'affiche pas les calendriers des agents supprimés
$requete_personnel = array('supprime'=>0);

// Recherche des plages de service public de l'agent
// Si les exports ICS sont protégés par des codes
if($config['ICS-Code']){
  $requete_personnel["code_ics"] = $code;
}

$db=new db();

// Recherche des absences
$a=new absences();
$a->valide=true;
$a->fetch("`debut`,`fin`",null,$id,'0000-00-00 00:00:00', date('Y-m-d', strtotime(date('Y-m-d').' + 2 years')));
$absences=$a->elements;

// Recherche des congés (si le module est activé)
if ($config['Conges-Enable']) {
  require_once "../conges/class.conges.php";
  $c = new conges();
  $c->perso_id = $id;
  $c->debut = '0000-00-00 00:00:00';
  $c->fin = date('Y-m-d', strtotime(date('Y-m-d').' + 2 years'));
  $c->valide = true;
  $c->fetch();
  $absences = array_merge($absences, $c->elements);
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
$ical[]="X-WR-CALNAME:Absences $agent";
$ical[]="PRODID:Planning-Biblio-Absences";
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

if(isset($absences)){
  // Complète le tableau $ical
  foreach($absences as $elem){
    $debut = date("Ymd\THis", strtotime($elem['debut']));
    $fin = date("Ymd\THis", strtotime($elem['fin']));
    // Nom du poste pour SUMMARY
    $motif = html_entity_decode($elem['motif'],ENT_QUOTES|ENT_IGNORE,'UTF-8');
    $commentaires = html_entity_decode($elem['commentaires'],ENT_QUOTES|ENT_IGNORE,'UTF-8');
    // Validation pour LAST-MODIFIED et DSTAMP
    $validation = date("Ymd\THis", strtotime($elem['validation']));
    // Demande pour CREATED
    $demande = date("Ymd\THis", strtotime($elem['demande']));
    // ORGANIZER
    $organizer = null;
    /*
    if(isset($agents[$verrou[$elem['date'].'_'.$elem['site']]['agent']])){
      $tmp = $agents[$verrou[$elem['date'].'_'.$elem['site']]['agent']];
      $organizer = html_entity_decode($tmp['prenom'].' '.$tmp['nom'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
      $organizer .= ':mailto:'.$tmp['mail'];
    }
    //*/
    
    $ical[]="BEGIN:VEVENT";
    $ical[]="UID: $id-{$elem['site']}-{$elem['poste']}-$debut-$fin@$url";
    $ical[]="DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z";
    $ical[]="DTSTART;TZID=Europe/Paris:$debut";
    $ical[]="DTEND;TZID=Europe/Paris:$fin";
    $ical[]="SUMMARY:$motif".($commentaires?" - $commentaires":"");
    if($organizer){
      $ical[]="ORGANIZER;CN=$organizer";
    }
    $ical[]="LOCATION:INDISPO";
    $ical[]="STATUS:".($elem['valide']?"CONFIRMED":"TENTATIVE");
    $ical[]="CLASS:PUBLIC";
    $ical[]="X-MICROSOFT-CDO-INTENDEDSTATUS:BUSY";
    $ical[]="TRANSP:OPAQUE";
    $ical[]="CREATED:$demande";
    $ical[]="LAST-MODIFIED:$validation";
    $ical[]="DTSTAMP:$validation";
    $ical[]="BEGIN:VALARM";
    $ical[]="ACTION:DISPLAY";
    $ical[]="DESCRIPTION:This is an event reminder";
    $ical[]="TRIGGER:-P0DT0H10M0S";
    $ical[]="END:VALARM";
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
