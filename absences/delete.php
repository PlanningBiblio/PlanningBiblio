<?php
/*
Planning Biblio, Version 1.7.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : absences/delete.php
Création : mai 2011
Dernière modification : 31 mars 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de supprimer une absence : confirmation et suppression.

Page appelée par la page index.php après avoir cliqué sur l'icône supprimer de la page absences/modif.php
*/

require_once "class.absences.php";

// Initialisation des variables
$a=new absences();
$a->fetchById($_GET['id']);
$debut=$a->elements['debut'];
$fin=$a->elements['fin'];
$perso_id=$a->elements['perso_id'];
$nom=$a->elements['nom'];
$prenom=$a->elements['prenom'];
$mail=$a->elements['mail'];
$mailResponsable=$a->elements['mailResponsable'];
$motif=$a->elements['motif'];
$commentaires=$a->elements['commentaires'];

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
if(!isset($_GET['confirm'])){
  echo "<h4>Etes vous sûr de vouloir supprimer cette absence ?</h4>\n";
  echo "<form method='get' action='#' name='form'>\n";
  echo "<input type='hidden' name='page' value='absences/delete.php' />\n";
  echo "<input type='hidden' name='id' value='".$_GET['id']."' />\n";
  echo "<input type='button' value='Non' onclick='annuler(1);' />\n";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='submit' name='confirm' value='Oui' />\n";
  echo "</form>\n";
}
else{
  // Envoi d'un mail à l'agent et aux responsables
  $message="Suppression d'une absence : <br/>$prenom $nom<br/>Début : ".dateFr($debut);
  $hre_debut=substr($debut,-8);
  $hre_fin=substr($fin,-8);
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

  $a=new absences();
  $a->getRecipients($config['Absences-notifications2'],$responsables,$mail,$mailResponsable);
  $destinataires=$a->recipients;

  sendmail("Suppression d'une absence",$message,$destinataires);

  // Mise à jour du champs 'absent' dans 'pl_poste'
  $db=new db();
  $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='0' WHERE
    ((CONCAT(`date`,' ',`debut`) < '$fin' AND CONCAT(`date`,' ',`debut`) >= '$debut')
    OR (CONCAT(`date`,' ',`fin`) > '$debut' AND CONCAT(`date`,' ',`fin`) <= '$fin'))
    AND `perso_id`='$perso_id'";

  // suppression dans la table 'absences'
  $db->query($req);
  $db=new db();
  $db->delete("absences","id='{$_GET['id']}'");
  echo "<h4>L'asbence a été supprimée</h4>\n";
  echo "<a href='javascript:annuler(3);'>Retour</a>\n";
}
?>