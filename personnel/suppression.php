<?php
/**
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : personnel/suppression.php
Création : mai 2011
Dernière modification : 10 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime un agent à partir de la liste des agents en cliquant sur l'icône corbeille (fichier personnel/index.php).
L'agent n'est pas supprimé définitivement, il est marqué comme supprimé dans la table personnel (champ supprime=1)

Cette page est appelée par le fichier index.php
*/

require_once "class.personnel.php";

// Initialisation des variables
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$etape=filter_input(INPUT_GET,"etape",FILTER_SANITIZE_STRING);

echo "<h3>Suppression</h3>\n";

switch($etape){
  case "etape2"	: etape2();	break;
  case "etape3"	: etape3();	break;
  case "etape4"	: etape4();	break;
  default 	: etape1();	break;
}

function etape1(){
  global $id;
  global $nom;
  $db=new db();
  $db->select2("personnel",array("nom","prenom","actif","supprime"),array("id"=>$id));
  $nom=$db->result[0]['prenom']." ".$db->result[0]['nom'];
  
  if($db->result[0]['supprime']==1)
    echo "Etes-vous sûr de vouloir définitivement supprimer \"$nom\" ?\n";
  else
    echo "Etes-vous sûr de vouloir supprimer \"$nom\" ?\n";
  echo "<br/><br/>\n";
  echo "<a href='javascript:popup_closed();'>Non</a>\n";
  echo "&nbsp;&nbsp;\n";
  if($db->result[0]['supprime']==1)		// Suppression définitive
    echo "<a href='index.php?page=personnel/suppression.php&amp;menu=off&amp;id=$id&amp;etape=etape4'>Oui</a>\n";
  else								// Marqué comme supprimé
    echo "<a href='index.php?page=personnel/suppression.php&amp;menu=off&amp;id=$id&amp;etape=etape2'>Oui</a>\n";
}

function etape2(){
  global $id;
  echo "<form method='get' action='index.php' name='form' onsubmit='verif_form(\"date=date\");'>\n";
  echo "<input type='hidden' name='page' value='personnel/suppression.php' />\n";
  echo "<input type='hidden' name='menu' value='off' />\n";
  echo "Sélectionner la date de départ : \n";
  echo "<input type='text' name='date' size='10' value='".date("d/m/Y")."' class='datepicker'>";
  echo "<br/><br/>\n";
  echo "<input type='button' value='Annuler' onclick='popup_closed();'>\n";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='submit' value='Supprimer' />\n";
  echo "<input type='hidden' name='id' value='$id'>\n";
  echo "<input type='hidden' name='etape' value='etape3'>\n";
  echo "</form>\n";
}

function etape3(){
  global $id;
  $date=filter_input(INPUT_GET,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
  $date=dateSQL($date);

      //	Mise à jour de la table personnel
  $db=new db();
  $db->update2("personnel",array("supprime"=>"1","actif"=>"Supprim&eacute;","depart"=>$date),array("id"=>$id));
      //	Mise à jour de la table pl_poste
  $db=new db();
  $id=$db->escapeString($id);
  $date=$db->escapeString($date);
  $db->query("UPDATE `{$GLOBALS['dbprefix']}pl_poste` SET `supprime`='1' WHERE `perso_id`='$id' AND `date`>'$date';");
  echo "<script type='text/JavaScript'>parent.window.location.reload(false);</script>";
  echo "<script type='text/JavaScript'>popup_closed();</script>";
}

function etape4(){
  global $id;
      //	Mise à jour de la table personnel
  $p=new personnel();
  $p->delete($id);
  echo "<script type='text/JavaScript'>parent.window.location.reload(false);</script>";
  echo "<script type='text/JavaScript'>popup_closed();</script>";
}
?>