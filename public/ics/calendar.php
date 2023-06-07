<?php
/**
Planning Biblio
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : ics/calendar.php
Création : juillet 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Ce fichier génère un calendrier ICS par agent.
Utilisation : renseigner l'adresse http(s)://votre_serveur/votre_planning/ics/calendar.php&login=login_de_l_agent dans le champ URL de votre client ICS (Thunderbird, Outlook, Google Calendar, etc.)
Vous pouvez également utiliser les paramètres 'mail' et 'id' pour identifier l'agent (http(s)://votre_serveur/votre_planning/ics/calendar.php&mail=e-mail_de_l_agent et http(s)://votre_serveur/votre_planning/ics/calendar.php&id=id_de_l_agent)
Le calendrier contenant les plages de service public validées sera retourné.
Vous devez activier le paramètre ICS-Export dans le menu Administration / Configuration / ICS
*
* @param int id : ID de l'agent
* @param string login : login de l'agent
* @param string mail : e-mail de l'agent
* @param code : codeICS (option) : Code permettant de rendre privé le fichier ICS
* au moins l'un des paramètres id, login et mail est requis.
*/

/*
* TODO
* Faire une autre requete pour afficher par poste, dans ce cas les noms et prénoms seront affichés dans summary
*  cette autre requete sera utilisée si les @param id, login et mail n'existent pas et si @param poste à la place
* TODO
* @file maj.php, @file setup/db_structure : config/codeICS pour postes, un code par site
*/

// TODO : Protection par code, générer des codes pour les agents existants, générer des codes à chaque ajout/importation, afficher ces codes des les fichiers agents.
// TODO : config : activer/désactiver la génération des fichiers ICS: désactivé par défaut

session_start();

$version="ics";

require_once "../include/config.php";
require_once "../include/function.php";
require_once "../absences/class.absences.php";
require_once "../personnel/class.personnel.php";
require_once "../postes/class.postes.php";
require_once(__DIR__ . '/../ics/class.ics.php');
require_once __DIR__ . '/../init_entitymanager.php';

$CSRFToken = CSRFToken();

if (!$config['ICS-Export']) {
    logs("L'exportation ICS est désactivée", "ICS Export", $CSRFToken);
    exit;
}

$code=filter_input(INPUT_GET, "code", FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
$login=filter_input(INPUT_GET, "login", FILTER_SANITIZE_STRING);
$mail=filter_input(INPUT_GET, "mail", FILTER_SANITIZE_EMAIL);
$get_absences = filter_input(INPUT_GET, 'absences', FILTER_SANITIZE_NUMBER_INT);

// Définission de l'id de l'agent si l'argument login est donné
if (!$id and $login) {
    $db = new db();
    $db->select2('personnel', 'id', array('login'=>$login));
    if ($db->result) {
        $id = $db->result[0]['id'];
    } else {
        logs("Impossible de trouver l'id associé au login $login", "ICS Export", $CSRFToken);
        exit;
    }
}

// Définission de l'id de l'agent si l'argument mail est donné
if (!$id and $mail) {
    $db = new db();
    $db->select2('personnel', 'id', array('mail'=>$mail));
    if ($db->result) {
        $id = $db->result[0]['id'];
    } else {
        logs("Impossible de trouver l'id associé au mail $mail", "ICS Export", $CSRFToken);
        exit;
    }
}

if (!$id) {
    logs("L'id de l'agent n'est pas fourni", "ICS Export", $CSRFToken);
    exit;
}

logs("Exportation des plages de SP pour l'agent #$id", "ICS Export", $CSRFToken);

// N'affiche pas les calendriers des agents supprimés
$requete_personnel = array('supprime'=>0);

// Recherche des plages de service public de l'agent
// Si les exports ICS sont protégés par des codes
if ($config['ICS-Code']) {
    $requete_personnel["code_ics"] = $code;
}

$icsInterval = null;
if ($config['ICS-Interval'] != '' && intval($config['ICS-Interval'])) {
    $icsInterval = $config['ICS-Interval'];
}

$db=new db();
$db->selectInnerJoin(
    array("pl_poste","perso_id"),
    array("personnel","id"),
    array("date", "debut", "fin", "poste", 'site', 'absent', 'supprime'),
    array(),
  array("perso_id"=>$id),
    $requete_personnel,
    ($icsInterval ? "AND `date` > DATE_SUB(curdate(), INTERVAL $icsInterval DAY) " : '') . "ORDER BY `date` DESC, `debut` DESC, `fin` DESC"
);
if ($db->result) {
    $planning = $db->result;
}

// Recherche des postes pour affichage du nom des postes
$p=new postes();
$p->fetch();
$postes=$p->elements;

// Liste des sites
if ($config['Multisites-nombre'] > 1) {
    $sites = array();
    for ($i=1; $i<=$config['Multisites-nombre']; $i++) {
        $sites[$i] = html_entity_decode($config["Multisites-site$i"], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
    }
}

// Recherche des plannings verrouillés pour exclure les plages concernant des plannings en attente
$verrou = array();
$db = new db();
$db->select2("pl_poste_verrou", null, array('verrou2'=>'1'), ($icsInterval ? "AND `date` > DATE_SUB(curdate(), INTERVAL $icsInterval DAY) " : ''));
if ($db->result) {
    foreach ($db->result as $elem) {
        $verrou[$elem['date'].'_'.$elem['site']] = array('date'=>$elem['validation2'], 'agent'=>$elem['perso2']);
    }
}

// Recherche des absences
$a=new absences();
$a->valide = true;
$a->documents = false;
$a->fetch("`debut`,`fin`", $id, ($icsInterval ? date('Y-m-d',strtotime(date('Y-m-d') . " - $icsInterval days")) : '0000-00-00 00:00:00'), date('Y-m-d', strtotime(date('Y-m-d').' + 2 years')));
$absences=$a->elements;

// Recherche des congés (si le module est activé)
if ($config['Conges-Enable']) {
    require_once "../conges/class.conges.php";
    $c = new conges();
    $c->perso_id = $id;
    $c->debut = ($icsInterval ? date('Y-m-d',strtotime(date('Y-m-d') . " - $icsInterval days")) : '0000-00-00 00:00:00');
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

$tz = date_default_timezone_get();
// Tableau $ical
$ical=array();
$ical[]="BEGIN:VCALENDAR";
$ical[]="X-WR-CALNAME:Service Public $agent";
$ical[]="PRODID:Planning-Biblio-Calendar";
$ical[]="VERSION:2.0";
$ical[]="METHOD:PUBLISH";
$ical[]="BEGIN:VTIMEZONE";
$ical[]="TZID:$tz";
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
if (isset($planning)) {
    // Exclusion des planning non validés
    foreach ($planning as $elem) {
        if (!array_key_exists($elem['date'].'_'.$elem['site'], $verrou)) {
            continue;
        }
    
        // Exclusion des absences
        foreach ($absences as $a) {
            if ($a['debut']< $elem['date'].' '.$elem['fin'] and $a['fin']> $elem['date'].' '.$elem['debut']) {
                continue 2;
            }
        }
    
        if ($elem['absent'] == 1) {
            continue;
        }

        // Regroupe les plages de SP qui se suivent sur le même poste
        if (isset($tab[$i-1])
            and $tab[$i-1]['date'] == $elem['date']
            and $tab[$i-1]['debut'] == $elem['fin']
            and $tab[$i-1]['poste'] == $elem['poste']
            and $tab[$i-1]['site'] == $elem['site']
            and $tab[$i-1]['supprime'] == $elem['supprime']
            and $tab[$i-1]['absent'] == $elem['absent']) {
            $tab[$i-1]['debut'] = $elem['debut'];
        } else {
            $tab[$i++] = $elem;
        }
    }

    // Complète le tableau $ical
    foreach ($tab as $elem) {

        // Organizer
        $organizer = null;
        if (isset($agents[$verrou[$elem['date'].'_'.$elem['site']]['agent']])) {
            $tmp = $agents[$verrou[$elem['date'].'_'.$elem['site']]['agent']];
            $organizer = $tmp['prenom'] . ' ' . $tmp['nom'];
            $organizer .= ':mailto:'.$tmp['mail'];
        }
    
        $params = [
            'id' => $id,
            'start' => strtotime($elem['date']." ".$elem['debut']),
            'end' => strtotime($elem['date']." ".$elem['fin']),
            'site' => !empty($sites[$elem['site']]) ? $sites[$elem['site']] : null,
            'siteId' => $elem['site'],
            'floor' => !empty($postes[$elem['poste']]['etage']) ? ' ' . $postes[$elem['poste']]['etage'] : null,
            'position' => $postes[$elem['poste']]['nom'],
            'positionId' => $elem['poste'],
            'organizer' => $organizer,
            'lastModified' => strtotime($verrou[$elem['date'].'_'.$elem['site']]['date']),
        ];

        $event = CJICS::createIcsEvent($params);
        $ical = array_merge($ical, $event);
    }
}

if (isset($absences) and $get_absences) {

  // Complète le tableau $ical

  foreach ($absences as $elem) {

    $params = [
        'id' => $id,
        'start' => strtotime($elem['debut']),
        'end' => strtotime($elem['fin']),
        'reason' => $elem['motif'],
        'comment' => $elem['commentaires'],
        'status' => $elem['valide'] ? 'CONFIRMED' : 'TENTATIVE',
        'createdAt' => strtotime($elem['demande']),
        'lastModified' => strtotime($elem['validation']),
    ];
 
    $event = CJICS::createIcsEvent($params);
    $ical = array_merge($ical, $event);
  }
}

$ical[]="END:VCALENDAR";

$ical=implode("\n", $ical);

//set correct content-type-header
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=calendar.ics');
echo $ical;
exit;
