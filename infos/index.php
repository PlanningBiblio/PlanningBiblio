<?php
/**
Planning Biblio, Version 2.4.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : infos/index.php
Création : février 2012
Dernière modification : 1er octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affcihe le pied de page
Permet d'ajouter, de modifier et de supprimer un message d'information.
Le message apparaître pendant la période souhaitée en haut du planning.
L'affichage des messages est géré par la page planning/postes/index.php

Cette page est appelée par le fichier index.php
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if(__FILE__ == $_SERVER['SCRIPT_FILENAME']){
  include_once "../include/accessDenied.php";
  exit;
}

echo "<h3>Messages d'informations</h3>\n";

//	Initialisation des variables
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$debut=filter_input(INPUT_GET,"debut",FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_GET,"fin",FILTER_SANITIZE_STRING);
$texte=trim(filter_input(INPUT_GET,"texte",FILTER_SANITIZE_STRING));
$suppression=filter_input(INPUT_GET,"suppression",FILTER_SANITIZE_STRING);
$validation=filter_input(INPUT_GET,"validation",FILTER_SANITIZE_NUMBER_INT);

// Contrôle sanitize_dateFr en 2 temps pour éviter les erreurs CheckMarx
$debut=filter_var($debut,FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin,FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);
if($texte){
  $texte=htmlentities($texte,ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
}

//			----------------	Suppression			-------------------------------//
if($suppression and $validation){
  $db=new db();
  $db->delete2("infos",array("id"=>$id));
  echo "<b>L'information a été supprimée</b>";
  echo "<br/><br/><a href='index.php?page=infos/index.php'>Retour</a>\n";
}
elseif($suppression){
  echo "<h4>Etes vous sûr de vouloir supprimer cette information ?</h4>\n";
  echo "<form method='get' action='#' name='form'>\n";
  echo "<input type='hidden' name='page' value='infos/index.php'/>\n";
  echo "<input type='hidden' name='suppression' value='1'/>\n";
  echo "<input type='hidden' name='validation' value='1'/>\n";
  echo "<input type='hidden' name='id' value='$id'/>\n";
  echo "<input type='button' value='Non' onclick='history.back();'  class='ui-button'/>\n";
  echo "&nbsp;&nbsp;&nbsp;";
  echo "<input type='submit' value='Oui' class='ui-button'/>\n";
  echo "</form>\n";
}
//			----------------	FIN Suppression			-------------------------------//
//			----------------	Validation du formulaire	-------------------------------//
elseif($validation){		//		Validation
  echo "<b>Votre demande a été enregistrée</b>\n";
  echo "<br/><br/><a href='index.php?page=infos/index.php'>Retour</a>\n";
  if($id){
    $db=new db();
    $db->update2("infos",array("debut"=>$debutSQL,"fin"=>$finSQL,"texte"=>$texte),array("id"=>$id));
  }else{
    $db=new db();
    $db->insert2("infos",array("debut"=>$debutSQL,"fin"=>$finSQL,"texte"=>$texte));
  }
}
//			---------------		Vérification			------------------------------//
elseif($debut){
  if(!$fin){
    $fin=$debut;
  }
  echo "<h4>Confirmation</h4>";
  echo "Du $debut au $fin";
  echo "<br/>";
  echo $texte;
  echo "<br/><br/>";
  echo "<form method='get' action='index.php' name='form'>";
  echo "<input type='hidden' name='page' value='infos/index.php'/>\n";
  echo "<input type='hidden' name='debut' value='$debut'/>\n";
  echo "<input type='hidden' name='fin' value='$fin'/>\n";
  echo "<input type='hidden' name='texte' value='$texte'/>\n";
  echo "<input type='hidden' name='id' value='$id'/>\n";
  echo "<input type='hidden' name='validation' value='1'/>\n";
  echo "<input type='button' value='Annuler' onclick='history.back();' class='ui-button' />";
  echo "&nbsp;&nbsp;&nbsp;\n";
  echo "<input type='submit' value='Valider' class='ui-button' />\n";
  echo "</form>";
}
//			----------------	FIN Validation du formulaire		-------------------------------//
else{
  //	selection des infos pour en afficher la liste
  $date=date("Y-m-d");
  $db=new db();
  $db->query("SELECT * FROM `{$dbprefix}infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
  $infos=$db->result;

  if($id){
    $db=new db();
    $db->select2("infos","*",array("id"=>$id));
    $debut=dateFr($db->result[0]['debut']);
    $fin=dateFr($db->result[0]['fin']);
    $texte=$db->result[0]['texte'];
    $titre="Modifications d'une informations\n";
  }
  else{
    $debut=null;
    $fin=null;
    $texte=null;
    $titre="Ajout d'une information\n";
  }

  echo "
  <form method='get' action='index.php' name='form' onsubmit='return verif_form(\"debut=date1;fin=date2;texte\");'>\n
  <input type='hidden' name='page' value='infos/index.php'/>\n
  <input type='hidden' name='id' value='$id'/>\n
  <table><tr style='vertical-align:top;'><td>
  <table class='tableauFiches'>
  <tr><td style='padding-bottom:30px;' colspan='2'><b>$titre</b></td></tr>
  <tr><td><label class='intitule'>Date de d&eacute;but</label></td>
  <td><input type='text' name='debut' value='$debut' class='datepicker' /></td></tr>
  <tr><td><label class='intitule'>Date de fin</label></td>
  <td><input type='text' name='fin' value='$fin' class='datepicker'/></td></tr>
  <tr><td><label class='intitule'>Texte</label></td>
  <td><textarea name='texte' rows='3' cols='16' class='ui-widget-content ui-corner-all'>$texte</textarea>
  </td></tr><tr><td>&nbsp;
  </td></tr>
  <tr><td colspan='2'>\n";
  if($id){
    echo "<input type='button' value='Supprimer' onclick='document.location.href=\"index.php?page=infos/index.php&amp;id={$id}&amp;suppression=1\";' class='ui-button'/>\n";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
  }
  echo "<input type='button' value='Annuler' onclick='document.location.href=\"index.php?page=infos/index.php\";' class='ui-button' />
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <input type='submit' value='Valider' class='ui-button' />
  </td></tr></table>";
  if(!empty($infos) and !$id){
    echo "</td><td style='padding-left:100px;'>\n";
    echo "<table>\n";
    echo "<tr><td style='padding-bottom:30px;' colspan='4'><b>Informations en cours</b></td></tr>\n";
    foreach($infos as $elem){
      echo "<tr><td><a href='index.php?page=infos/index.php&amp;id={$elem['id']}'>\n";
      echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
      echo "<a href='index.php?page=infos/index.php&amp;id={$elem['id']}&amp;suppression=1'>\n";
      echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a></td>\n";
      echo "<td>".dateFr($elem['debut'])."</td>\n";
      echo "<td>".dateFr($elem['fin'])."</td>\n";
      echo "<td>{$elem['texte']}</td></tr>\n";
    }
    echo "</td></tr></table>\n";
  }
  echo "</td></tr></table>\n";
  echo "</form>\n";
}
?>