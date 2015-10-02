<?php
/*
Planning Biblio, Version 2.0.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/modif2.php
Création : mai 2011
Dernière modification : 29 septembre 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Page validant la modification d'une absence : enregistrement dans la BDD des modifications

Page appelée par la page index.php
Page d'entrée : absences/modif.php
*/

require_once "class.absences.php";

// Initialisation des variables
$commentaires=trim(filter_input(INPUT_GET,"commentaires",FILTER_SANITIZE_STRING));
$debut=filter_input(INPUT_GET,"debut",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$fin=filter_input(INPUT_GET,"fin",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$hre_debut=filter_input(INPUT_GET,"hre_debut",FILTER_CALLBACK,array("options"=>"sanitize_time"));
$hre_fin=filter_input(INPUT_GET,"hre_fin",FILTER_CALLBACK,array("options"=>"sanitize_time_end"));
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$motif=filter_input(INPUT_GET,"motif",FILTER_SANITIZE_STRING);
$motif_autre=trim(filter_input(INPUT_GET,"motif_autre",FILTER_SANITIZE_STRING));
$nbjours=filter_input(INPUT_GET,"nbjours",FILTER_SANITIZE_NUMBER_INT);
$valide=filter_input(INPUT_GET,"valide",FILTER_SANITIZE_NUMBER_INT);

// Pièces justificatives
$pj1=filter_input(INPUT_GET,"pj1",FILTER_CALLBACK,array("options"=>"sanitize_on01"));
$pj2=filter_input(INPUT_GET,"pj2",FILTER_CALLBACK,array("options"=>"sanitize_on01"));
$so=filter_input(INPUT_GET,"so",FILTER_CALLBACK,array("options"=>"sanitize_on01"));

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);
$debut_sql=$debutSQL." ".$hre_debut;
$fin_sql=$finSQL." ".$hre_fin;

$isValidate=true;
$valideN1=0;
$valideN2=0;
$validationN1=null;
$validationN2=null;

$nbjours=$nbjours?$nbjours:0;
$valide=$valide?$valide:0;

if($config['Absences-validation']){
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

$db=new db();
$db->selectInnerJoin(array("absences","perso_id"),array("personnel","id"),
  array(),
  array(array("name"=>"id","as"=>"perso_id"),"nom","prenom","mail","mailsResponsables","sites"),
  array("id"=>$id));

$perso_id=$db->result[0]['perso_id'];
$nom=$db->result[0]['nom'];
$prenom=$db->result[0]['prenom'];
$mail=$db->result[0]['mail'];
$sites_agent=unserialize($db->result[0]['sites']);
$mailsResponsables=explode(";",html_entity_decode($db->result[0]['mailsResponsables'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));

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
}else{
  $admin=in_array(1,$droits)?true:false;
}

// Pour mise à jour du champs 'absent' dans 'pl_poste'
$db=new db();
$db->select2("absences","*",array("id"=>$id));
$debut1=$db->result[0]['debut'];
$fin1=$db->result[0]['fin'];
$valide1N1=$db->result[0]['valideN1'];
$valide1N2=$db->result[0]['valide'];
$perso_id=$db->result[0]['perso_id'];

// Mise à jour du champs 'absent' dans 'pl_poste'
if(($debutSQL!=$debut1 or $finSQL!=$fin1) and $isValidate){
  $db=new db();
  $debut1=$db->escapeString($debut1);
  $fin1=$db->escapeString($fin1);
  $perso_id=$db->escapeString($perso_id);
  $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='0' WHERE
    CONCAT(`date`,' ',`debut`) < '$fin1' AND CONCAT(`date`,' ',`fin`) > '$debut1'
    AND `perso_id`='$perso_id'";
  $db->query($req);

  $db=new db();
  $debut1=$db->escapeString($debut1);
  $fin1=$db->escapeString($fin1);
  $perso_id=$db->escapeString($perso_id);
  $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='1' WHERE
    CONCAT(`date`,' ',`debut`) < '$fin_sql' AND CONCAT(`date`,' ',`fin`) > '$debut_sql'
    AND `perso_id`='$perso_id'";
  $db->query($req);
}

// Mise à jour de la table 'absences'
$update=array("motif"=>$motif, "motif_autre"=>$motif_autre, "nbjours"=>$nbjours, "commentaires"=>$commentaires, 
  "debut"=>$debut_sql, "fin"=>$fin_sql);

if($admin){
  $update=array_merge($update,array("pj1"=>$pj1, "pj2"=>$pj2, "so"=>$so));
}

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
$db=new db();
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
  $notifications=2;
}
else{
  if($valide1N2<=0 and $valideN2>0){
    $sujet="Validation d'une absence";
    $notifications=4;
  }
  elseif($valide1N2>=0 and $valideN2<0){
    $sujet="Refus d'une absence";
    $notifications=4;
  }
  elseif($valide1N1<=0 and $valideN1>0){
    $sujet="Acceptation d'une absence (en attente de validation hiérarchique)";
    $notifications=3;
  }
  elseif($valide1N1>=0 and $valideN1<0){
    $sujet="Refus d'une absence (en attente de validation hiérarchique)";
    $notifications=3;
  }
  else{
    $sujet="Modification d'une absence";
    $notifications=2;
  }
}

// Choix des destinataires en fonction de la configuration
$a=new absences();
$a->getRecipients($notifications,$responsables,$mail,$mailsResponsables);
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
$msg=urlencode("L'absence a été modifiée avec succés");
echo "<script type='text/JavaScript'>document.location.href='index.php?page=absences/voir.php&msg=$msg&msgType=success';</script>\n";
?>