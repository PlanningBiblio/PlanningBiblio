<?php
/*
Planning Biblio, Version 1.8.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : infos/index.php
Création : février 2012
Dernière modification : 24 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affcihe le pied de page
Permet d'ajouter, de modifier et de supprimer un message d'information.
Le message apparaître pendant la période souhaitée en haut du planning.
L'affichage des messages est géré par la page planning/postes/index.php

Cette page est appelée par le fichier index.php
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

echo "<h3>Messages d'informations</h3>\n";

//	Initialisation des variables
$id=isset($_GET['id'])?$_GET['id']:null;

//			----------------	Suppression			-------------------------------//
if(isset($_GET['suppression']) and isset($_GET['validation'])){
  $db=new db();
  $db->query("delete from {$dbprefix}infos where id=".$_GET['id'].";");
  echo "<b>L'information a été supprimée</b>";
  echo "<br/><br/><a href='index.php?page=infos/index.php'>Retour</a>\n";
}
elseif(isset($_GET['suppression'])){
  echo "<h4>Etes vous sûr de vouloir supprimer cette information ?</h4>\n";
  echo "<form method='get' action='#' name='form'>\n";
  echo "<input type='hidden' name='page' value='infos/index.php'/>\n";
  echo "<input type='hidden' name='suppression' value='oui'/>\n";
  echo "<input type='hidden' name='validation' value='oui'/>\n";
  echo "<input type='hidden' name='id' value='".$_GET['id']."'/>\n";
  echo "<input type='button' value='Non' onclick='history.back();'  class='ui-button'/>\n";
  echo "&nbsp;&nbsp;&nbsp;";
  echo "<input type='submit' value='Oui' class='ui-button'/>\n";
  echo "</form>\n";
}
//			----------------	FIN Suppression			-------------------------------//
//			----------------	Validation du formulaire	-------------------------------//
elseif(isset($_GET['validation'])){		//		Validation
  echo "<b>Votre demande a été enregistrée</b>\n";
  echo "<br/><br/><a href='index.php?page=infos/index.php'>Retour</a>\n";
  $db=new db();
  if(isset($_GET['id']) and $_GET['id']!=null)
    $db->update2("infos",array("debut"=>dateSQL($_GET['debut']),"fin"=>dateSQL($_GET['fin']),"texte"=>$_GET['texte']),array("id"=>$_GET['id']));
  else
    $db->insert2("infos",array("debut"=>dateSQL($_GET['debut']),"fin"=>dateSQL($_GET['fin']),"texte"=>$_GET['texte']));
}
//			---------------		Vérification			------------------------------//
elseif(isset($_GET['debut'])){
  $texte=htmlentities($_GET['texte'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
  $_GET['fin']=$_GET['fin']?$_GET['fin']:$_GET['debut'];
  echo "<h4>Confirmation</h4>";
  echo "Du {$_GET['debut']} au {$_GET['fin']}";
  echo "<br/>";
  echo $texte;
  echo "<br/><br/>";
  echo "<form method='get' action='index.php' name='form'>";
  echo "<input type='hidden' name='page' value='infos/index.php'/>\n";
  echo "<input type='hidden' name='debut' value='".$_GET['debut']."'/>\n";
  echo "<input type='hidden' name='fin' value='".$_GET['fin']."'/>\n";
  echo "<input type='hidden' name='texte' value='$texte'/>\n";
  echo "<input type='hidden' name='id' value='".$_GET['id']."'/>\n";
  echo "<input type='hidden' name='validation' value='validation'/>\n";
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

  if(isset($_GET['id'])){
    $db=new db();
    $db->query("select * from {$dbprefix}infos where id=".$_GET['id'].";");
    $debut=dateFr($db->result[0]['debut']);
    $fin=dateFr($db->result[0]['fin']);
    $texte=$db->result[0]['texte'];
    $titre="Modifications d'une informations\n";
  }
  else{
    $debut=null;
    $fin=null;
    $texte=null;
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
  <td><input type='text' name='debut' value='".$debut."' class='datepicker' /></td></tr>
  <tr><td><label class='intitule'>Date de fin</label></td>
  <td><input type='text' name='fin' value='".$fin."' class='datepicker'/></td></tr>
  <tr><td><label class='intitule'>Texte</label></td>
  <td><textarea name='texte' rows='3' cols='16' class='ui-widget-content ui-corner-all'>".$texte."</textarea>
  </td></tr><tr><td>&nbsp;
  </td></tr>
  <tr><td colspan='2'>\n";
  if(isset($_GET['id'])){
    echo "<input type='button' value='Supprimer' onclick='document.location.href=\"index.php?page=infos/index.php&amp;id=".$_GET['id']."&amp;suppression=oui\";' class='ui-button'/>\n";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
  }
  echo "<input type='button' value='Annuler' onclick='document.location.href=\"index.php?page=infos/index.php\";' class='ui-button' />
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  <input type='submit' value='Valider' class='ui-button' />
  </td></tr></table>";
  if(!empty($infos) and !isset($_GET['id'])){
    echo "</td><td style='padding-left:100px;'>\n";
    echo "<table>\n";
    echo "<tr><td style='padding-bottom:30px;' colspan='4'><b>Informations en cours</b></td></tr>\n";
    foreach($infos as $elem){
      echo "<tr><td><a href='index.php?page=infos/index.php&amp;id={$elem['id']}'>\n";
      echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
      echo "<a href='index.php?page=infos/index.php&amp;id={$elem['id']}&amp;suppression'>\n";
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