<?php
/**
Planning Biblio, Version 2.8.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : personnel/valid.php
Création : mai 2011
Dernière modification : 24 mai 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Valide l'ajout ou la modification des agents : enregistrement des infos dans la base de données

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

$arrivee=filter_input(INPUT_POST, "arrivee", FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$CSRFToken = filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING);
$depart=filter_input(INPUT_POST, "depart", FILTER_CALLBACK, array("options"=>"sanitize_dateFr"));
$heuresHebdo=filter_input(INPUT_POST, "heuresHebdo", FILTER_SANITIZE_STRING);
$heuresTravail=filter_input(INPUT_POST, "heuresTravail", FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
$mail=trim(filter_input(INPUT_POST, "mail", FILTER_SANITIZE_EMAIL));

$actif = htmlentities($post['actif'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
$action=$post['action'];
$check_hamac = !empty($post['check_hamac']) ? 1 : 0;
$check_ics1 = !empty($post['check_ics1']) ? 1 : 0;
$check_ics2 = !empty($post['check_ics2']) ? 1 : 0;
$check_ics3 = !empty($post['check_ics3']) ? 1 : 0;
$check_ics = "[$check_ics1,$check_ics2,$check_ics3]";
$droits=array_key_exists("droits", $post)?$post['droits']:null;
$categorie=trim($post['categorie']);
$informations=trim($post['informations']);
$mailsResponsables=trim(str_replace(array("\n"," "), null, $post['mailsResponsables']));
$matricule=trim($post['matricule']);
$url_ics=isset($post['url_ics']) ? trim($post['url_ics']) : null;
$nom=trim($post['nom']);
$postes=$post['postes'];
$prenom=trim($post['prenom']);
$recup = isset($post['recup']) ? trim($post['recup']) : null;
$service = htmlentities($post['service'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
$sites=array_key_exists("sites", $post)?$post['sites']:null;
$statut = htmlentities($post['statut'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
$temps=array_key_exists("temps", $post)?$post['temps']:null;

// Modification du choix des emplois du temps avec l'option EDTSamedi == 1 (EDT différent les semaines avec samedi travaillé)
$eDTSamedi=array_key_exists("EDTSamedi", $post)?$post['EDTSamedi']:null;

// Modification du choix des emplois du temps avec l'option EDTSamedi == 2 (EDT différent les semaines avec samedi travaillé et les semaines à ouverture restreinte)
if ($config['EDTSamedi'] == 2) {
    $eDTSamedi = array();
    foreach ($post as $k => $v) {
        if (substr($k, 0, 10) == 'EDTSamedi_' and $v > 1) {
            $eDTSamedi[] = array(substr($k, -10), $v);
        }
    }
}

$premierLundi=array_key_exists("premierLundi", $post)?$post['premierLundi']:null;
$dernierLundi=array_key_exists("dernierLundi", $post)?$post['dernierLundi']:null;

$droits=$droits?$droits:array();
$postes = $postes ? json_encode(explode(",", $postes)) : null;
$sites=$sites?json_encode($sites):null;
$temps = $temps ? json_encode($temps) : null;

$arrivee=dateSQL($arrivee);
$depart=dateSQL($depart);

for ($i=1;$i<=$config['Multisites-nombre'];$i++) {
    // Modification des plannings Niveau 2 donne les droits Modification des plannings Niveau 1
    if (in_array((300+$i), $droits) and !in_array((1000+$i), $droits)) {
        $droits[]=1000+$i;
    }
}

// Le droit de gestion des absences (20x) donne le droit modifier ses propres absences (6) et le droit d'ajouter des absences pour plusieurs personnes (9)
for ($i = 1; $i <= $config['Multisites-nombre']; $i++) {
    if (in_array((200+$i), $droits) or in_array((500+$i), $droits)) {
        $droits[]=6;
        $droits[]=9;
        break;
    }
}

$droits[]=99;
$droits[]=100;
if ($id==1) {		// Ajoute config. avancée à l'utilisateur admin.
    $droits[]=20;
}
$droits=json_encode($droits);

switch ($action) {
  case "ajout":
    $db=new db();
    $db->select2("personnel", array(array("name"=>"MAX(`id`)", "as"=>"id")));
    $id=$db->result[0]['id']+1;

    $login=login($nom, $prenom);
    $mdp=gen_trivial_password();
    $mdp_crypt=md5($mdp);

    // Envoi du mail
    $message="Votre compte Planning Biblio a &eacute;t&eacute; cr&eacute;&eacute; :";
    $message.="<ul><li>Login : $login</li><li>Mot de passe : $mdp</li></ul>";
    
    $m=new CJMail();
    $m->subject="Création de compte";
    $m->message=$message;
    $m->to=$mail;
    $m->send();

    // Si erreur d'envoi de mail, affichage de l'erreur
    $msg=null;
    $msgType=null;
    if ($m->error) {
        $msg=urlencode($m->error_CJInfo);
        $msgType="error";
    }

    // Enregistrement des infos dans la base de données
    $insert=array("nom"=>$nom,"prenom"=>$prenom,"mail"=>$mail,"statut"=>$statut,"categorie"=>$categorie,"service"=>$service,"heures_hebdo"=>$heuresHebdo,
      "heures_travail"=>$heuresTravail,"arrivee"=>$arrivee,"depart"=>$depart,"login"=>$login,"password"=>$mdp_crypt,"actif"=>$actif,
      "droits"=>$droits,"postes"=>$postes,"temps"=>$temps,"informations"=>$informations,"recup"=>$recup,"sites"=>$sites,
      "mails_responsables"=>$mailsResponsables,"matricule"=>$matricule,"url_ics"=>$url_ics, "check_ics"=>$check_ics, "check_hamac"=>$check_hamac);
    if ($config['Conges-Enable']) {
        include "conges/ficheAgentValid.php";
    }
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("personnel", $insert);

    // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
    $p=new personnel();
    $p->CSRFToken = $CSRFToken;
    $p->updateEDTSamedi($eDTSamedi, $premierLundi, $dernierLundi, $id);
    
        
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php&msg=$msg&msgType=$msgType';</script>";
    break;
  
  case "mdp":
    $mdp=gen_trivial_password();
    $mdp_crypt=md5($mdp);
    $db=new db();
    $db->select2("personnel", "login", array("id"=>$id));
    $login=$db->result[0]['login'];

    // Envoi du mail
    $message="Votre mot de passe Planning Biblio a &eacute;t&eacute; modifi&eacute;";
    $message.="<ul><li>Login : $login</li><li>Mot de passe : $mdp</li></ul>";
    
    $m=new CJMail();
    $m->subject="Modification du mot de passe";
    $m->message=$message;
    $m->to=$mail;
    $m->send();

    // Si erreur d'envoi de mail, affichage de l'erreur
    $msg=null;
    $msgType=null;
    if ($m->error) {
        $msg=urlencode($m->error_CJInfo);
        $msgType="error";
    } else {
        $msg=urlencode("Le mot de passe a été modifié et envoyé par e-mail à l'agent");
        $msgType="success";
    }

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("personnel", array("password"=>$mdp_crypt), array("id"=>$id));
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php&msg=$msg&msgType=$msgType';</script>";
    break;

  case "modif":
    $update=array("nom"=>$nom, "prenom"=>$prenom, "mail"=>$mail, "statut"=>$statut, "categorie"=>$categorie, "service"=>$service,
      "heures_hebdo"=>$heuresHebdo, "heures_travail"=>$heuresTravail, "actif"=>$actif, "droits"=>$droits, "arrivee"=>$arrivee,
      "depart"=>$depart, "postes"=>$postes, "informations"=>$informations, "recup"=>$recup, "sites"=>$sites,
      "mails_responsables"=>$mailsResponsables, "matricule"=>$matricule, "url_ics"=>$url_ics, "check_ics"=>$check_ics, "check_hamac"=>$check_hamac);
    // Si le champ "actif" passe de "supprimé" à "service public" ou "administratif", on réinitialise les champs "supprime" et départ
    if (!strstr($actif, "Supprim")) {
        $update["supprime"]="0";
        // Si l'agent était supprimé et qu'on le réintégre, on change sa date de départ
        // pour qu'il ne soit pas supprimé de la liste des agents actifs
        $db=new db();
        $db->select2("personnel", "*", array("id"=>$id));
        if (strstr($db->result[0]['actif'], "Supprim") and $db->result[0]['depart']<=date("Y-m-d")) {
            $update["depart"]="0000-00-00";
        }
    } else {
        $update["actif"]="Supprim&eacute;";
    }

    // Mise à jour de l'emploi du temps si modifié à partir de la fiche de l'agent
    if ($temps) {
        $update["temps"]=$temps;
    }

    if ($config['Conges-Enable']) {
        include "conges/ficheAgentValid.php";
    }

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("personnel", $update, array("id"=>$id));

        //	Mise à jour de la table pl_poste en cas de modification de la date de départ
    $db=new db();		// On met supprime=0 partout pour cet agent
    $db->CSRFToken = $CSRFToken;
    $db->update("pl_poste", array("supprime"=>"0"), array("perso_id"=>$id));
    if ($depart!="0000-00-00" and $depart!="") {
        // Si une date de départ est précisée, on met supprime=1 au dela de cette date
        $db=new db();
        $id=$db->escapeString($id);
        $depart=$db->escapeString($depart);
        $db->query("UPDATE `{$GLOBALS['config']['dbprefix']}pl_poste` SET `supprime`='1' WHERE `perso_id`='$id' AND `date`>'$depart';");
    }

    // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
    $p=new personnel();
    $p->CSRFToken = $CSRFToken;
    $p->updateEDTSamedi($eDTSamedi, $premierLundi, $dernierLundi, $id);

    echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php';</script>";
    break;
}

function date2($date)
{
    if ($date=="") {
        $date="0000-00-00";
    } else {
        $date2=explode("/", $date);
    
        if (strlen($date2[2])==2) {
            $date2[2]="20".$date2[2];
        }
        if (strlen($date2[1])==1) {
            $date2[1]="0".$date2[1];
        }
        if (strlen($date2[0])==1) {
            $date2[0]="0".$date2[0];
        }
        
        $date=$date2[2]."-".$date2[1]."-".$date2[0];
    }
    return $date;
}

function login($nom, $prenom)
{
    $prenom=trim($prenom);
    $nom=trim($nom);
    if ($prenom) {
        $tmp[]=$prenom;
    }
    if ($nom) {
        $tmp[]=$nom;
    }
  
    $tmp=join($tmp, ".");
    $login=removeAccents(strtolower($tmp));
    $login=str_replace(" ", "-", $login);
    $login=substr($login, 0, 95);
  
    $i=1;
    $db=new db();
    $db->select2("personnel", "*", array("login"=>$login));
    while ($db->result) {
        $i++;
        if ($i==2) {
            $login.="2";
        } else {
            $login=substr($login, 0, strlen($login)-1).$i;
        }
        $db=new db();
        $db->select("personnel", null, "login='$login'");
    }
    return $login;
}
