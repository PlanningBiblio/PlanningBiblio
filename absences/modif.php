<?php
/*
Planning Biblio, Version 1.5.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : absences/modif.php
Création : mai 2011
Dernière modification : 20 août 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Formulaire permettant de modifier

Page appelée par la page index.php
*/

require_once "class.absences.php";

//	Initialisation des variables
$display=null;
$checked=null;

$id=$_GET['id'];
$db=new db();
$req="SELECT `{$dbprefix}personnel`.`id` AS `perso_id`, `{$dbprefix}personnel`.`nom` AS `nom`, 	
  `{$dbprefix}personnel`.`prenom` AS `prenom`, `{$dbprefix}absences`.`id` AS `id`, `{$dbprefix}personnel`.`site` AS `site`,  
  `{$dbprefix}absences`.`debut` AS `debut`, `{$dbprefix}absences`.`fin` AS `fin`, 
  `{$dbprefix}absences`.`nbjours` AS `nbjours`, `{$dbprefix}absences`.`motif` AS `motif`, 
  `{$dbprefix}absences`.`commentaires` AS `commentaires` 
  FROM `{$dbprefix}absences` INNER JOIN `{$dbprefix}personnel` 
  ON `{$dbprefix}absences`.`perso_id`=`{$dbprefix}personnel`.`id` WHERE `{$dbprefix}absences`.`id`='$id';";
$db->query($req);
$perso_id=$db->result[0]['perso_id'];
$motif=$db->result[0]['motif'];
$commentaires=$db->result[0]['commentaires'];
$debut=$db->result[0]['debut'];
$fin=$db->result[0]['fin'];
$site=$db->result[0]['site'];
$hre_debut=substr($debut,-8);
$hre_fin=substr($fin,-8);
$debut=substr($debut,0,10);
$fin=substr($fin,0,10);
if($hre_debut=="00:00:00" and $hre_fin=="23:59:59"){
  $checked="checked='checked'";
  $display="style='display:none;'";
}

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


// Multisites, ne pas afficher les absences des agents d'un site non géré
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

echo "<h3>Modification de l'absence</h3>\n";
echo "<form name='form' method='get' action='index.php' onsubmit='return verif_absences(\"debut=date1;fin=date2;motif\");'>\n";
echo "<input type='hidden' name='page' value='absences/modif2.php' />\n";
echo "<input type='hidden' name='perso_id' value='$perso_id' />\n";		// nécessaire pour verif_absences
echo "<table>\n";
echo "<tr><td>Nom, Prénom : </td><td>";
echo $db->result[0]['nom'];
echo "&nbsp;";
echo $db->result[0]['prenom'];
echo "</td></tr>\n";
echo "<tr><td>\n";
echo "Journée(s) entière(s) : \n";
echo "</td><td>\n";
echo "<input type='checkbox' name='allday' $checked onclick='all_day();'/>\n";
echo "</td></tr>\n";
echo "<tr><td>";
echo "Date de début : </td><td>";
echo "<input type='text' name='debut' value='{$debut}' />\n";
echo "<img src='img/calendrier.gif' border='0' onclick='calendrier(\"debut\");' alt='calendrier' />";
echo "</td></tr>\n";
echo "<tr id='hre_debut' $display ><td>\n";
echo "Heure de début : \n";
echo "</td><td>\n";
echo "<select name='hre_debut'>\n";
selectHeure(8,23,true);
echo "</select>\n";
echo "</td></tr>\n";

echo "<tr><td>";
echo "Date de fin : </td><td>";
echo "<input type='text' name='fin' value='{$fin}' />\n";
echo "<img src='img/calendrier.gif' border='0' onclick='calendrier(\"fin\");' alt='calendrier' />";
echo "</td></tr>\n";
echo "<tr id='hre_fin' $display ><td>\n";
echo "Heure de début : \n";
echo "</td><td>\n";
echo "<select name='hre_fin'>\n";
selectHeure(8,23,true);
echo "</select>\n";
echo "</td></tr>\n";

echo "<tr><td>";
echo "Motif : </td><td>";

echo "<select name='motif'>\n";
echo "<option value=''>-----------------------</option>\n";
$db_select=new db();
$db_select->query("select valeur from {$dbprefix}select_abs order by rang;");
foreach($db_select->result as $elem){
  echo "<option value='".$elem['valeur']."'>".$elem['valeur']."</option>\n";
}
echo "</select>\n";
echo "<script type='text/JavaScript'>ajoutSelect('select_abs','motif');</script>";
echo "</td></tr><tr><td>";
echo "Commentaires : </td><td>";
echo "<textarea name='commentaires' cols='25' rows='5'>$commentaires</textarea>";
echo "</td></tr>";
echo "<tr><td colspan='2'><br/>\n";
echo "<input type='button' value='Supprimer' onclick='document.location.href=\"index.php?page=absences/delete.php&amp;id=$id\";'/>";
echo "&nbsp;&nbsp;\n";
echo "<input type='button' value='Annuler' onclick='annuler(1);'/>\n";
echo "&nbsp;&nbsp;\n";
echo "<input type='submit' value='Valider'/>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "<input type='hidden' name='id' value='$id'/>";
echo "</form>\n";
echo "<script type='text/JavaScript'>initform(\"motif=$motif\");</script>";
echo "<script type='text/JavaScript'>initform(\"hre_debut=$hre_debut\");</script>";
echo "<script type='text/JavaScript'>initform(\"hre_fin=$hre_fin\");</script>";
?>