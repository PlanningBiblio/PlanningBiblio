<?php
/*
Planning Biblio, Version 1.6.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : absences/modif2.php
Création : mai 2011
Dernière modification : 29 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

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
if($config['Absences-validation']){
  $valide=$_GET['valide']*$_SESSION['login_id'];
  $validation=date("Y-m-d H:i:s");
  $isValidate=$valide>0?true:false;
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

				// pour mise à jour du champs 'absent' dans 'pl_poste'
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}absences` WHERE `id`='$id';");
$debut1=$db->result[0]['debut'];
$fin1=$db->result[0]['fin'];
$valide1=$db->result[0]['valide'];
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
  $update["valide"]=$valide;
  $update["validation"]=$validation;
}
$where=array("id"=>$id);
$db->update2("absences",$update,$where);

echo "<h3>Modification de l'absence</h3>\n";

// Envoi d'un mail à l'agent et aux responsables
// MLV
// Pas d'envoi en cas de modif
/*
$sujet="Modification d'une absence";
if($valide1<=0 and $valide>0){
  $sujet="Validation d'une absence";
}
elseif($valide1>=0 and $valide<0){
  $sujet="Refus d'une absence";
}
$message="$sujet : <br/>$prenom $nom<br/>Début : ".dateFr($debut);
if($hre_debut!="00:00:00")
  $message.=" ".heure3($hre_debut);
$message.="<br/>Fin : ".dateFr($fin);
if($hre_fin!="23:59:59")
  $message.=" ".heure3($hre_fin);
$message.="<br/>Motif : $motif<br/>";
if($commentaires)
  $message.="Commentaire:<br/>$commentaires<br/>";

$a=new absences();
$a->getResponsables($debut,$fin,$perso_id);
$responsables=$a->responsables;

$destinataires=array();
if(verifmail($mail)){
  $destinataires[]=$mail;
}
else{
  echo "<font style='color:red;'>L'adresse e-mail enregistrée pour $nom $prenom n'est pas valide.\n";
  echo "<br/>La notification ne lui sera pas envoyée.</font>\n";
}

if($config['Absences-notifications']=="A tous" or $config['Absences-notifications']=="Au responsable direct"){
  $destinataires[]=$mailResponsable;
}
if($config['Absences-notifications']=="A tous" or substr($config['Absences-notifications'],0,25)=="Aux agents ayant le droit"){
  foreach($responsables as $elem){
    $destinataires[]=$elem['mail'];
  }
}
sendmail($sujet,$message,$destinataires);
*/
echo "<h4>Votre demande à été enregistrée</h4>";
echo "<a href='javascript:annuler(2);'>Retour</a>\n";
?>