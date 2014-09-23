<?php
/*
Planning Biblio, Version 1.8.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : absences/modif2.php
Création : mai 2011
Dernière modification : 22 septembre 2014
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
$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);
$hre_debut=$_GET['hre_debut']?$_GET['hre_debut']:"00:00:00";
$hre_fin=$_GET['hre_fin']?$_GET['hre_fin']:"23:59:59";
$debut_sql=$debutSQL." ".$hre_debut;
$fin_sql=$finSQL." ".$hre_fin;
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
    $valideN1=($valide/2)*$_SESSION['login_id'];
    $validationN1=date("Y-m-d H:i:s");
  }
  $isValidate=$valideN2>0?true:false;
}

$motif=$_GET['motif'];
$motif_autre=htmlentities($_GET['motif_autre'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
$commentaires=htmlentities($_GET['commentaires'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
$nbjours=isset($_GET['nbjours'])?$_GET['nbjours']:0;

$db=new db();
$db->query("select {$dbprefix}personnel.id as perso_id, {$dbprefix}personnel.nom as nom, {$dbprefix}personnel.prenom as prenom, 
  {$dbprefix}personnel.mail as mail, {$dbprefix}personnel.mailResponsable as mailResponsable, {$dbprefix}personnel.sites as sites 
  FROM {$dbprefix}absences INNER JOIN {$dbprefix}personnel ON {$dbprefix}absences.perso_id={$dbprefix}personnel.id 
  WHERE {$dbprefix}absences.id='$id'");
$perso_id=$db->result[0]['perso_id'];
$nom=$db->result[0]['nom'];
$prenom=$db->result[0]['prenom'];
$mail=$db->result[0]['mail'];
$sites_agent=unserialize($db->result[0]['sites']);
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
if($config['Multisites-nombre']>1){
  $sites=array();
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    if(in_array((200+$i),$droits)){
      $sites[]=$i;
    }
  }

  $admin=false;
  foreach($sites_agent as $site){
    if(in_array($site,$sites)){
      $admin=true;
    }
  }
  if(!$admin){
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

if(($debutSQL!=$debut1 or $finSQL!=$fin1) and $isValidate){			// mise à jour du champs 'absent' dans 'pl_poste'
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
$update=array("motif"=>$motif, "motif_autre"=>$motif_autre, "nbjours"=>$nbjours, "commentaires"=>$commentaires, "debut"=>$debut_sql, "fin"=>$fin_sql);
if($config['Absences-validation']){
  // Validation N1
  if($valideN1){
    $update["valideN1"]=$valideN1;
    $update["validationN1"]=$validationN1;
  }
  // Validation N2
  if($valideN2){
    $update["valide"]=$valideN2;
    $update["validation"]=$validationN2;
  }
  // Retour à l'état demandé
  if($valide==0){
    $update["valide"]=0;
    $update["valideN1"]=0;
    $update["validation"]="0000-00-00 00:00:00";
    $update["validationN1"]="0000-00-00 00:00:00";
  }
}
$where=array("id"=>$id);
$db->update2("absences",$update,$where);

// Envoi d'un mail de notification
$sujet="Modification d'une absence";

// Liste des responsables
$a=new absences();
$a->getResponsables($debutSQL,$finSQL,$perso_id);
$responsables=$a->responsables;

// Choix des destinataires des notifications selon le degré de validation
// Si l'agent lui même modifie son absence ou si pas de validation, la notification est envoyée au 1er groupe
if($_SESSION['login_id']==$perso_id or $config['Absences-validation']=='0'){
  $notifications=$config['Absences-notifications2'];
}
else{
  if($valide1N2<=0 and $valideN2>0){
    $sujet="Validation d'une absence";
    $notifications=$config['Absences-notifications4'];
  }
  elseif($valide1N2>=0 and $valideN2<0){
    $sujet="Refus d'une absence";
    $notifications=$config['Absences-notifications4'];
  }
  elseif($valide1N1<=0 and $valideN1>0){
    $sujet="Acceptation d'une absence (en attente de validation hiérarchique)";
    $notifications=$config['Absences-notifications3'];
  }
  elseif($valide1N1>=0 and $valideN1<0){
    $sujet="Refus d'une absence (en attente de validation hiérarchique)";
    $notifications=$config['Absences-notifications3'];
  }
  else{
    $sujet="Modification d'une absence";
    $notifications=$config['Absences-notifications2'];
  }
}

// Choix des destinataires en fonction de la configuration
$a=new absences();
$a->getRecipients($notifications,$responsables,$mail,$mailResponsable);
$destinataires=$a->recipients;

// Message
$message="<b><u>$sujet</u></b> : <br/><br/><b>$prenom $nom</b><br/><br/>Début : $debut";
if($hre_debut!="00:00:00")
  $message.=" ".heure3($hre_debut);
$message.="<br/>Fin : $fin";
if($hre_fin!="23:59:59")
  $message.=" ".heure3($hre_fin);
$message.="<br/><br/>Motif : $motif";
if($motif_autre){
  $message.=" / $motif_autre";
}
$message.="<br/>";

if($config['Absences-validation']){
  $validationText="Demand&eacute;e";
  if($valideN2>0){
    $validationText="Valid&eacute;e";
  }
  elseif($valideN2<0){
    $validationText="Refus&eacute;e";
  }
  elseif($valideN1>0){
    $validationText="Accept&eacute;e (en attente de validation hi&eacute;rarchique)";
  }
  elseif($valideN1<0){
    $validationText="Refus&eacute;e (en attente de validation hi&eacute;rarchique)";
  }

  $message.="<br/>Validation : <br/>\n";
  $message.=$validationText;
  $message.="<br/>\n";
}

if($commentaires){
  $message.="<br/>Commentaire:<br/>$commentaires<br/>";
}

// Ajout du lien permettant de rebondir sur l'absence
$url=createURL("absences/modif.php&id=$id");
$message.="<br/><br/>Lien vers la demande d&apos;absence :<br/><a href='$url'>$url</a><br/><br/>";

// Envoi du mail
if(!empty($destinataires)){
  sendmail($sujet,$message,$destinataires);
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&messageOK="
  .urlencode("L'absence a été modifiée avec succés")."';</script>\n";
?>