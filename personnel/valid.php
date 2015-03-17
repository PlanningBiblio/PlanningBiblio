<?php
/*
Planning Biblio, Version 1.9.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : personnel/valid.php
Création : mai 2011
Dernière modification : 2 février 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Valide l'ajout ou la modification des agents : enregistrement des infos dans la base de données

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

$action=$_POST['action'];
if(isset($_POST['id'])){
  $id=$_POST['id'];
  $nom=trim(htmlentities($_POST['nom'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  $prenom=trim(htmlentities($_POST['prenom'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  $mail=trim(htmlentities($_POST['mail'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  $statut=trim(htmlentities($_POST['statut'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  $categorie=trim(htmlentities($_POST['categorie'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  $service=trim(htmlentities($_POST['service'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  $heuresHebdo=$_POST['heuresHebdo'];
  $heuresTravail=$_POST['heuresTravail'];
  $postes=serialize(explode(",",$_POST['postes']));
  $temps=isset($_POST['temps'])?serialize($_POST['temps']):null;
  $actif=$_POST['actif'];
  $arrivee=dateSQL($_POST['arrivee']);
  $depart=dateSQL($_POST['depart']);
  $informations=trim(htmlentities($_POST['informations'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  $recup=trim(htmlentities($_POST['recup'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
  $sites=isset($_POST['sites'])?serialize($_POST['sites']):null;
  $droits=isset($_POST['droits'])?$_POST['droits']:array();
  
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
  $droits=serialize($droits);


  // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
  $eDTSamedi=isset($_POST['EDTSamedi'])?$_POST['EDTSamedi']:null;
  $premierLundi=isset($_POST['premierLundi'])?$_POST['premierLundi']:null;
  $dernierLundi=isset($_POST['dernierLundi'])?$_POST['dernierLundi']:null;
  $mailsResponsables=trim(str_replace(array("\n"," "),null,$_POST['mailsResponsables']));
  $matricule=trim(htmlentities($_POST['matricule'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
}
else{
  $id=null;
  $nom=null;
  $prenom=null;
  $mail=null;
  $statut=null;
  $categorie=null;
  $service=null;
  $heuresHebdo=null;
  $heuresTravail=null;
  $postes=null;
  $temps=null;
  $actif='Actif';
  $arrivee=date("Y-m-d");
  $depart=null;
  $informations=null;
  $recup=null;
  $sites=serialize(array());
  $droits=array();
  $mailsResponsables=null;
  $matricule=null;
}

switch($action){
  case "ajout" :
    $db=new db();
    $db->select("personnel","MAX(`id`) AS `id`");
    $id=$db->result[0]['id']+1;

    $login=login($nom,$prenom);
    $mdp=gen_trivial_password();
    $mdp_crypt=md5($mdp);

    sendmail("Création de compte","Login : $login <br>Mot de passe : $mdp","$mail");
    $insert=array("nom"=>$nom,"prenom"=>$prenom,"mail"=>$mail,"statut"=>$statut,"categorie"=>$categorie,"service"=>$service,"heuresHebdo"=>$heuresHebdo,
      "heuresTravail"=>$heuresTravail,"arrivee"=>$arrivee,"depart"=>$depart,"login"=>$login,"password"=>$mdp_crypt,"actif"=>$actif,
      "droits"=>$droits,"postes"=>$postes,"temps"=>$temps,"informations"=>$informations,"recup"=>$recup,"sites"=>$sites,
      "mailsResponsables"=>$mailsResponsables,"matricule"=>$matricule);
    if(in_array("conges",$plugins)){
      include "plugins/conges/ficheAgentValid.php";
    }
    $db=new db();
    $db->insert2("personnel",$insert);

    // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
    $p=new personnel();
    $p->updateEDTSamedi($eDTSamedi,$premierLundi,$dernierLundi,$id);
    
	    
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php';</script>";
    break;
  
  case "mdp" :
    $mdp=gen_trivial_password();
    $mdp_crypt=md5($mdp);
    $db=new db();
    $db->query("select login from {$dbprefix}personnel where id='$id';");
    $login=$db->result[0]['login'];
    sendmail("Modification du mot de passe","Login : $login <br>Mot de passe : $mdp","$mail");

    $req="update {$dbprefix}personnel set password='$mdp_crypt' where id=$id;";
    $db=new db();
    $db->query($req);
    echo "<script type='text/JavaScript'>alert('Le mot de passe a été changé');</script>";
    echo "<script type='text/JavaScript'>document.location.href='index.php?page=personnel/index.php';</script>";
    break;

  case "modif" :
    $update=array("nom"=>$nom, "prenom"=>$prenom, "mail"=>$mail, "statut"=>$statut, "categorie"=>$categorie, "service"=>$service, 
      "heuresHebdo"=>$heuresHebdo, "heuresTravail"=>$heuresTravail, "actif"=>$actif, "droits"=>$droits, "arrivee"=>$arrivee, 
      "depart"=>$depart, "postes"=>$postes, "informations"=>$informations, "recup"=>$recup, "sites"=>$sites, 
      "mailsResponsables"=>$mailsResponsables, "matricule"=>$matricule);
    // Si le champ "actif" passe de "supprimé" à "service public" ou "administratif", on réinitialise les champs "supprime" et départ
    if(!strstr($actif,"Supprim")){
      $update["supprime"]="0";
      // Si l'agent était supprimé et qu'on le réintégre, on change sa date de départ 
      // pour qu'il ne soit pas supprimé de la liste des agents actifs
      $db=new db();
      $db->select("personnel","*","id='$id'");
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
    $db->update2("personnel",$update,array("id"=>$id));

	    //	Mise à jour de la table pl_poste en cas de modification de la date de départ
    $db=new db();		// On met supprime=0 partout pour cet agent
    $db->query("UPDATE `{$GLOBALS['dbprefix']}pl_poste` SET `supprime`='0' WHERE `perso_id`='$id';");
    if($depart!="0000-00-00" and $depart!=""){
	    // Si une date de départ est précisée, on met supprime=1 au dela de cette date
      $db=new db();
      $db->query("UPDATE `{$GLOBALS['dbprefix']}pl_poste` SET `supprime`='1' WHERE `perso_id`='$id' AND `date`>'$depart';");
    }

    // Modification du choix des emplois du temps avec l'option EDTSamedi (EDT différent les semaines avec samedi travaillé)
    $p=new personnel();
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
  $db->select("personnel",null,"login='$login'");
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