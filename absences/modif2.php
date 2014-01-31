<?php
/*
Planning Biblio, Version 1.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : absences/modif2.php
Création : mai 2011
Dernière modification : 16 janvier 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr
Fichier personnalisé MLV

Description :
Page validant la modification d'une absence : enregistrement dans la BDD des modifications

Page appelée par la page index.php
Page d'entrée : absences/modif.php
*/

require_once "class.absences.php";

// Initialisation des variables
$id=$_GET['id'];
$debut=$_GET['debut'];
$fin=$_GET['fin']?$_GET['fin']:$_GET['debut'];
$hre_debut=$_GET['hre_debut']?$_GET['hre_debut']:"00:00:00";
$hre_fin=$_GET['hre_fin']?$_GET['hre_fin']:"23:59:59";
$debut_sql=$debut." ".$hre_debut;
$fin_sql=$fin." ".$hre_fin;
$isValidate=true;
$valideN1=0;
$valideN2=0;
$validationN1=null;
$validationN2=null;

if($config['Absences-validation']){
  $valide=$_GET['valide'];
  if($valide==1 or $valide==-1){
    $valideN2=$valide*$_SESSION['login_id'];
    $validationN2=date("Y-m-d H:i:s");
  }
  elseif($valide==2 or $valide==-2){
    $valideN1=$valide*$_SESSION['login_id'];
    $validationN1=date("Y-m-d H:i:s");
  }
  $isValidate=$valideN2>0?true:false;
}

$motif=$_GET['motif'];
$nbjours=isset($_GET['nbjours'])?$_GET['nbjours']:0;
$commentaires=addslashes($_GET['commentaires']);

$db=new db();
$db->query("select {$dbprefix}personnel.id as perso_id, {$dbprefix}personnel.nom as nom, {$dbprefix}personnel.prenom as prenom, 
  {$dbprefix}personnel.mail as mail, {$dbprefix}personnel.mailResponsable as mailResponsable, {$dbprefix}personnel.site as site 
  FROM {$dbprefix}absences INNER JOIN {$dbprefix}personnel ON {$dbprefix}absences.perso_id={$dbprefix}personnel.id 
  WHERE {$dbprefix}absences.id='$id'");
$perso_id=$db->result[0]['perso_id'];
$nom=$db->result[0]['nom'];
$prenom=$db->result[0]['prenom'];
$mail=$db->result[0]['mail'];
$site=$db->result[0]['site'];
$mailResponsable=$db->result[0]['mailResponsable'];

// Sécurité
// Droit 1 = modification de toutes les absences
// Droit 6 = modification de ses propres absences
$acces=in_array(1,$droits)?true:false;
if(!$acces){
  $acces=(in_array(6,$droits) and $perso_id==$_SESSION['login_id'])?true:false;
}
if(!$acces){
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
  include "include/footer.php";
  exit;
}

// Multisites, ne pas modifier les absences des agents d'un site non géré
if($config['Multisites-nombre']>1 and !$config['Multisites-agentsMultisites']){
  $sites=array();
  if(in_array(201,$droits)){
    $sites[]=1;
  }
  if(in_array(202,$droits)){
    $sites[]=2;
  }

  if(!in_array($site,$sites)){
    echo "<h3>Modification de l'absence</h3>\n";
    echo "Vous n'êtes pas autorisé(e) à modifier cette absence.<br/><br/>\n";
    echo "<a href='index.php?page=absences/voir.php'>Retour à la liste des absences</a><br/><br/>\n";
    include "include/footer.php";
    exit;
  }
}

// Pour mise à jour du champs 'absent' dans 'pl_poste'
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}absences` WHERE `id`='$id';");
$debut1=$db->result[0]['debut'];
$fin1=$db->result[0]['fin'];
$valide1N1=$db->result[0]['valideN1'];
$valide1N2=$db->result[0]['valide'];
$perso_id=$db->result[0]['perso_id'];

if(($debut!=$debut1 or $fin!=$fin1) and $isValidate){			// mise à jour du champs 'absent' dans 'pl_poste'
  $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='0' WHERE
    ((CONCAT(`date`,' ',`debut`) < '$fin1' AND CONCAT(`date`,' ',`debut`) >= '$debut1')
    OR (CONCAT(`date`,' ',`fin`) > '$debut1' AND CONCAT(`date`,' ',`fin`) <= '$fin1'))
    AND `perso_id`='$perso_id'";
  $db=new db();
  $db->query($req);
  $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='1' WHERE
    ((CONCAT(`date`,' ',`debut`) < '$fin_sql' AND CONCAT(`date`,' ',`debut`) >= '$debut_sql')
    OR (CONCAT(`date`,' ',`fin`) > '$debut_sql' AND CONCAT(`date`,' ',`fin`) <= '$fin_sql'))
    AND `perso_id`='$perso_id'";
  $db=new db();
  $db->query($req);
}

// Mise à jour de la table 'absences'
$db=new db();
$update=array("motif"=>$motif, "nbjours"=>$nbjours, "commentaires"=>$commentaires, "debut"=>$debut_sql, "fin"=>$fin_sql);
if($config['Absences-validation']){
  if($valideN1){
    $update["valideN1"]=$valideN1;
    $update["validationN1"]=$validationN1;
  }
  if($valideN2){
    $update["valide"]=$valideN2;
    $update["validation"]=$validationN2;
  }
}
$where=array("id"=>$id);
$db->update2("absences",$update,$where);

// Envoi d'un mail de notification
// MLV
// Pas d'envoi en cas de modif
/*
$sujet="Modification d'une absence";

// Liste des responsables
$a=new absences();
$a->getResponsables($debut,$fin,$perso_id);
$responsables=$a->responsables;

// Choix des destinataires des notifications selon le degré de validation
// Si l'agent lui même modifie son absence ou si pas de validation, la notification est envoyée au 1er groupe
if($_SESSION['login_id']==$perso_id or $config['Absences-validation']=='0'){
  $notifications=$config['Absences-notifications'];
}
else{
  if($valide1N2<=0 and $valideN2>0){
    $sujet="Validation d'une absence";
    $validationText="Valid&eacute;e";
    $notifications=$config['Absences-notifications3'];
  }
  elseif($valide1N2>=0 and $valideN2<0){
    $sujet="Refus d'une absence";
    $validationText="Refus&eacute;e";
    $notifications=$config['Absences-notifications3'];
  }
  elseif($valide1N1<=0 and $valideN1>0){
    $sujet="Acceptation d'une absence (en attente de validation hiérarchique)";
    $validationText="Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
    $notifications=$config['Absences-notifications2'];
  }
  elseif($valide1N1>=0 and $valideN1<0){
    $sujet="Refus d'une absence (en attente de validation hiérarchique)";
    $validationText="Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
    $notifications=$config['Absences-notifications2'];
  }
  else{
    $sujet="Modification d'une absence";
    $validationText=null;
    $notifications=$config['Absences-notifications'];
  }
}

// Choix des destinataires en fonction de la configuration
$destinataires=array();
switch($notifications){
  case "Aux agents ayant le droit de g&eacute;rer les absences" :
    foreach($responsables as $elem){
      $destinataires[]=$elem['mail'];
    }
    break;
  case "Au responsable direct" :
    $destinataires[]=$mailResponsable;
    break;
  case "A la cellule planning" :
    $destinataires=explode(";",$config['Mail-Planning']);
    break;
  case "A l&apos;agent concern&eacute;" :
    $destinataires[]=$mail;
    break;
  case "A l&apos;agent concerné" :
    $destinataires[]=$mail;
    break;
  case "A tous" :
    $destinataires=explode(";",$config['Mail-Planning']);
    $destinataires[]=$mail;
    $destinataires[]=$mailResponsable;
    foreach($responsables as $elem){
      $destinataires[]=$elem['mail'];
    }
    break;
}

// Message
$message="$sujet : <br/>$prenom $nom<br/>Début : ".dateFr($debut);
if($hre_debut!="00:00:00")
  $message.=" ".heure3($hre_debut);
$message.="<br/>Fin : ".dateFr($fin);
if($hre_fin!="23:59:59")
  $message.=" ".heure3($hre_fin);
$message.="<br/>Motif : $motif<br/>";
if($commentaires)
  $message.="Commentaire:<br/>$commentaires<br/>";
if($config['Absences-validation']){
  $message.="<br/>Validation : <br/>\n";
  $message.=$validationText;
  $message.="<br/>\n";
}

sendmail($sujet,$message,$destinataires);
*/

echo "<h3>Modification de l'absence</h3>\n";
echo "<h4>Votre demande &agrave; &eacute;t&eacute; enregistr&eacute;e</h4>";
echo "<a href='javascript:annuler(2);'>Retour</a>\n";
?>