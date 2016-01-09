<?php
/*
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/delete.php
Création : mai 2011
Dernière modification : 9 janvier 2016
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Permet de supprimer une absence : confirmation et suppression.

Page appelée par la page index.php après avoir cliqué sur l'icône supprimer de la page absences/modif.php
*/

require_once "class.absences.php";

// Initialisation des variables
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$confirm=filter_input(INPUT_GET,"confirm",FILTER_CALLBACK,array("options"=>"sanitize_on"));

$a=new absences();
$a->fetchById($id);
$debut=$a->elements['debut'];
$fin=$a->elements['fin'];
$perso_id=$a->elements['perso_id'];
$nom=$a->elements['nom'];
$prenom=$a->elements['prenom'];
$mail=$a->elements['mail'];
$mailsResponsables=$a->elements['mailsResponsables'];
$motif=$a->elements['motif'];
$commentaires=$a->elements['commentaires'];
$valideN1=$a->elements['valideN1'];
$valideN2=$a->elements['valideN2'];

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

echo "<h3>Suppression de l'absence</h3>";
if(!$confirm){
  echo "<h4>Etes vous sûr de vouloir supprimer cette absence ?</h4>\n";
  echo "<form method='get' action='#' name='form'>\n";
  echo "<input type='hidden' name='page' value='absences/delete.php' />\n";
  echo "<input type='hidden' name='id' value='$id' />\n";
  echo "<input type='button' value='Non' onclick='annuler(1);' class='ui-button' />\n";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='submit' name='confirm' value='Oui' class='ui-button' />\n";
  echo "</form>\n";
}
else{
  // Envoi d'un mail à l'agent et aux responsables
  $message="<b><u/>Suppression d'une absence</u></b> : <br/><br/><b>$prenom $nom</b><br/><br/>Début : ".dateFr($debut);
  $hre_debut=substr($debut,-8);
  $hre_fin=substr($fin,-8);
  if($hre_debut!="00:00:00")
    $message.=" ".heure3($hre_debut);
  $message.="<br/>Fin : ".dateFr($fin);
  if($hre_fin!="23:59:59")
    $message.=" ".heure3($hre_fin);
  $message.="<br/><br/>Motif : $motif<br/>";

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

    $message.="<br/>Validation pr&eacute;c&eacute;dente : <br/>\n";
    $message.=$validationText;
    $message.="<br/>\n";
  }

  if($commentaires){
    $message.="<br/>Commentaire:<br/>$commentaires<br/>";
  }

  $a=new absences();
  $a->getResponsables($debut,$fin,$perso_id);
  $responsables=$a->responsables;

  $a=new absences();
  $a->getRecipients(2,$responsables,$mail,$mailsResponsables);
  $destinataires=$a->recipients;

  // Envoi du mail
  $m=new sendmail();
  $m->subject="Suppression d'une absence";
  $m->message=$message;
  $m->to=$destinataires;
  $m->send();

  // Si erreur d'envoi de mail, affichage de l'erreur
  if($m->error){
    echo "<script type='text/javascript'>CJInfo(\"{$m->error_CJInfo}\",\"error\");</script>\n";
  }

  // Mise à jour du champs 'absent' dans 'pl_poste'
  $db=new db();
  $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='0' WHERE
    CONCAT(`date`,' ',`debut`) < '$fin' AND CONCAT(`date`,' ',`fin`) > '$debut'
    AND `perso_id`='$perso_id'";

  // suppression dans la table 'absences'
  $db->query($req);
  $db=new db();
  $db->delete2("absences",array("id"=>$id));
  echo "<h4>L'absence a été supprimée</h4>\n";
  echo "<a href='javascript:annuler(3);'>Retour</a>\n";
}
?>
