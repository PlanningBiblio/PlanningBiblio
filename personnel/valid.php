<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : personnel/valid.php
Création : mai 2011
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Valide l'ajout ou la modification des agents : enregistrement des infos dans la base de données

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

$post=filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);

$arrivee=filter_input(INPUT_POST,"arrivee",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$CSRFToken = filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING);
$depart=filter_input(INPUT_POST,"depart",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$heuresHebdo=filter_input(INPUT_POST,"heuresHebdo",FILTER_SANITIZE_STRING);
$heuresTravail=filter_input(INPUT_POST,"heuresTravail",FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_POST,"id",FILTER_SANITIZE_NUMBER_INT);
$mail=trim(filter_input(INPUT_POST,"mail",FILTER_SANITIZE_EMAIL));

$actif = htmlentities( $post['actif'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
$action=$post['action'];
$droits=array_key_exists("droits",$post)?$post['droits']:null;
$categorie=trim($post['categorie']);
$informations=trim($post['informations']);
$mailsResponsables=trim(str_replace(array("\n"," "),null,$post['mailsResponsables']));
$matricule=trim($post['matricule']);
$url_ics=isset($post['url_ics']) ? trim($post['url_ics']) : null;
$nom=trim($post['nom']);
$postes=$post['postes'];
$prenom=trim($post['prenom']);
$recup=trim($post['recup']);
$service = htmlentities($post['service'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
$sites=array_key_exists("sites",$post)?$post['sites']:null;
$statut = htmlentities($post['statut'], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
$temps=array_key_exists("temps",$post)?$post['temps']:null;

// Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
$eDTSamedi=array_key_exists("EDTSamedi",$post)?$post['EDTSamedi']:null;
$premierLundi=array_key_exists("premierLundi",$post)?$post['premierLundi']:null;
$dernierLundi=array_key_exists("dernierLundi",$post)?$post['dernierLundi']:null;

$droits=$droits?$droits:array();
$postes = $postes ? json_encode(explode(",",$postes)) : null;
$sites=$sites?json_encode($sites):null;
$temps = $temps ? json_encode($temps) : null;

$arrivee=dateSQL($arrivee);
$depart=dateSQL($depart);

for($i=1;$i<=$config['Multisites-nombre'];$i++){
  // Multisites, Gestion des absences : si droits de gérer les absences de l'un des sites (201,202, ...), ajoute le droit 1 pour débloquer les champs administrateur
  if(in_array((200+$i),$droits) and !in_array(1,$droits)){
    $droits[]=1;
  }
  // Multisites, Gestion des absences validation N2 : si droits de gérer les absences N2 de l'un des sites (501,502, ...), ajoute le droit 8 pour débloquer les champs administrateur N2
  if(in_array((500+$i),$droits) and !in_array(8,$droits)){
    $droits[]=8;
  }
  // Multisites, Modification des plannings : si droits de modifier les plannings de l'un dessites (301,302, ...), ajoute le droit 12 pour débloquer les champs administrateur
  if(in_array((300+$i),$droits) and !in_array(12,$droits)){
    $droits[]=12;
  }
  // Multisites, Gestion des congés : si droits de gérer les congés de l'un des sites (401,402, ...), ajoute le droit 2 pour débloquer les champs administrateur
  if(in_array((400+$i),$droits) and !in_array(2,$droits)){
    $droits[]=2;
  }
}
$droits[]=99;
$droits[]=100;
if($id==1)		// Ajoute config. avancée à l'utilisateur admin.
  $droits[]=20;
$droits=json_encode($droits);

switch($action){
  case "ajout" :
    $db=new db();
    $db->select2("personnel",array(array("name"=>"MAX(`id`)", "as"=>"id")));
    $id=$db->result[0]['id']+1;

    $login=login($nom,$prenom);
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
    if($m->error){
      $msg=urlencode($m->error_CJInfo);
      $msgType="error";
    }

    // Enregistrement des infos dans la base de données
    $insert=array("nom"=>$nom,"prenom"=>$prenom,"mail"=>$mail,"statut"=>$statut,"categorie"=>$categorie,"service"=>$service,"heures_hebdo"=>$heuresHebdo,
      "heures_travail"=>$heuresTravail,"arrivee"=>$arrivee,"depart"=>$depart,"login"=>$login,"password"=>$mdp_crypt,"actif"=>$actif,
      "droits"=>$droits,"postes"=>$postes,"temps"=>$temps,"informations"=>$informations,"recup"=>$recup,"sites"=>$sites,
      "mails_responsables"=>$mailsResponsables,"matricule"=>$matricule,"url_ics"=>$url_ics);
    if(in_array("conges",$plugins)){
      include "plugins/conges/ficheAgentValid.php";
    }
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->insert("personnel",$insert);

    // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
    $p=new personnel();
    $p->CSRFToken = $CSRFToken;
    $p->updateEDTSamedi($eDTSamedi,$premierLundi,$dernierLundi,$id);
    
	    
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php&msg=$msg&msgType=$msgType';</script>";
    break;
  
  case "mdp" :
    $mdp=gen_trivial_password();
    $mdp_crypt=md5($mdp);
    $db=new db();
    $db->select2("personnel","login",array("id"=>$id));
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
    if($m->error){
      $msg=urlencode($m->error_CJInfo);
      $msgType="error";
    }else{
      $msg=urlencode("Le mot de passe a été modifié et envoyé par e-mail à l'agent");
      $msgType="success";
    }

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("personnel",array("password"=>$mdp_crypt),array("id"=>$id));
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php&msg=$msg&msgType=$msgType';</script>";
    break;

  case "modif" :
    $update=array("nom"=>$nom, "prenom"=>$prenom, "mail"=>$mail, "statut"=>$statut, "categorie"=>$categorie, "service"=>$service, 
      "heures_hebdo"=>$heuresHebdo, "heures_travail"=>$heuresTravail, "actif"=>$actif, "droits"=>$droits, "arrivee"=>$arrivee, 
      "depart"=>$depart, "postes"=>$postes, "informations"=>$informations, "recup"=>$recup, "sites"=>$sites, 
      "mails_responsables"=>$mailsResponsables, "matricule"=>$matricule, "url_ics"=>$url_ics);
    // Si le champ "actif" passe de "supprimé" à "service public" ou "administratif", on réinitialise les champs "supprime" et départ
    if(!strstr($actif,"Supprim")){
      $update["supprime"]="0";
      // Si l'agent était supprimé et qu'on le réintégre, on change sa date de départ 
      // pour qu'il ne soit pas supprimé de la liste des agents actifs
      $db=new db();
      $db->select2("personnel","*",array("id"=>$id));
      if(strstr($db->result[0]['actif'],"Supprim") and $db->result[0]['depart']<=date("Y-m-d")){
       $update["depart"]="0000-00-00";
      }
    }
    // Mise à jour de l'emploi du temps si modifié à partir de la fiche de l'agent
    if($temps){
      $update["temps"]=$temps;
    }

    if(in_array("conges",$plugins)){
      include "plugins/conges/ficheAgentValid.php";
    }

    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("personnel",$update,array("id"=>$id));

	    //	Mise à jour de la table pl_poste en cas de modification de la date de départ
    $db=new db();		// On met supprime=0 partout pour cet agent
    $db->CSRFToken = $CSRFToken;
    $db->update("pl_poste",array("supprime"=>"0"),array("perso_id"=>$id));
    if($depart!="0000-00-00" and $depart!=""){
	    // Si une date de départ est précisée, on met supprime=1 au dela de cette date
      $db=new db();
      $id=$db->escapeString($id);
      $depart=$db->escapeString($depart);
      $db->query("UPDATE `{$GLOBALS['dbprefix']}pl_poste` SET `supprime`='1' WHERE `perso_id`='$id' AND `date`>'$depart';");
    }

    // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
    $p=new personnel();
    $p->CSRFToken = $CSRFToken;
    $p->updateEDTSamedi($eDTSamedi,$premierLundi,$dernierLundi,$id);

    echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php';</script>";
    break;
}

function date2($date){
  if($date=="")
    $date="0000-00-00";
  else{
    $date2=explode("/",$date);
    
    if(strlen($date2[2])==2)
      $date2[2]="20".$date2[2];
    if(strlen($date2[1])==1)
      $date2[1]="0".$date2[1];
    if(strlen($date2[0])==1)
      $date2[0]="0".$date2[0];
	    
    $date=$date2[2]."-".$date2[1]."-".$date2[0];
  }
  return $date;
}

function login($nom,$prenom){
  $prenom=trim($prenom);
  $nom=trim($nom);
  if($prenom)
    $tmp[]=$prenom;
  if($nom)
    $tmp[]=$nom;
  
  $tmp=join($tmp,".");
  $login=removeAccents(strtolower($tmp));
  $login=str_replace(" ","-",$login);
  $login=substr($login,0,95);
  
  $i=1;
  $db=new db();
  $db->select2("personnel","*",array("login"=>$login));
  while($db->result){
    $i++;
    if($i==2)
      $login.="2";
    else
      $login=substr($login,0,strlen($login)-1).$i;
    $db=new db();
    $db->select("personnel",null,"login='$login'");
  }
  return $login;
}
?>