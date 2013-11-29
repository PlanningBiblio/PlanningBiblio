<?php
/*
Planning Biblio, Version 1.6.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : absences/modif.php
Création : mai 2011
Dernière modification : 26 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Formulaire permettant de modifier

Page appelée par la page index.php
*/

require_once "class.absences.php";

//	Initialisation des variables
$display=null;
$checked=null;
$admin=in_array(1,$droits)?true:false;

$id=$_GET['id'];
$db=new db();
$req="SELECT `{$dbprefix}personnel`.`id` AS `perso_id`, `{$dbprefix}personnel`.`nom` AS `nom`, 	
  `{$dbprefix}personnel`.`prenom` AS `prenom`, `{$dbprefix}absences`.`id` AS `id`, `{$dbprefix}personnel`.`site` AS `site`,  
  `{$dbprefix}absences`.`debut` AS `debut`, `{$dbprefix}absences`.`fin` AS `fin`, 
  `{$dbprefix}absences`.`nbjours` AS `nbjours`, `{$dbprefix}absences`.`motif` AS `motif`, 
  `{$dbprefix}absences`.`valide` AS `valide`, `{$dbprefix}absences`.`validation` AS `validation`, 
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
$valide=$db->result[0]['valide'];
$validation=$db->result[0]['validation'];
$hre_debut=substr($debut,-8);
$hre_fin=substr($fin,-8);
$debut=substr($debut,0,10);
$fin=substr($fin,0,10);
if($hre_debut=="00:00:00" and $hre_fin=="23:59:59"){
  $checked="checked='checked'";
  $display="style='display:none;'";
}
$select1=$valide==0?"selected='selected'":null;
$select2=$valide>0?"selected='selected'":null;
$select3=$valide<0?"selected='selected'":null;
$validation_texte=$valide>0?"Valid&eacute;e":"&nbsp;";
$validation_texte=$valide<0?"Refus&eacute;e":$validation_texte;
$validation_texte=$valide==0?"En attente de validation":$validation_texte;

// Sécurité
// Droit 1 = modification de toutes les absences
// Droit 6 = modification de ses propres absences
// Les admins ont toujours accès à cette page
$acces=in_array(1,$droits)?true:false;
if(!$acces){
  // Les non admin ayant le droits de modifier leurs absences ont accès si l'absence les concerne
  $acces=(in_array(6,$droits) and $perso_id==$_SESSION['login_id'])?true:false;
}
// Si config Absences-adminSeulement, seuls les admins ont accès à cette page
if($config['Absences-adminSeulement'] and !in_array(1,$droits)){
  $acces=false;
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
echo "<table class='tableauFiches'>\n";
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
echo "Heure de fin : \n";
echo "</td><td>\n";
echo "<select name='hre_fin'>\n";
selectHeure(8,23,true);
echo "</select>\n";
echo "</td></tr>\n";

echo "<tr><td>";
echo "Motif : </td><td>";

echo "<select name='motif' style='width:90%;'>\n";
echo "<option value=''>-----------------------</option>\n";
$db_select=new db();
$db_select->query("select valeur from {$dbprefix}select_abs order by rang;");
foreach($db_select->result as $elem){
  $selected=$elem['valeur']==$motif?"selected='selected'":null;
  echo "<option value='".$elem['valeur']."' $selected>".$elem['valeur']."</option>\n";
}
echo "</select>\n";
if($admin){
  echo "<a href='javascript:popup(\"include/ajoutSelect.php&amp;table=select_abs&amp;terme=motif\",400,400);'>\n";
  echo "<img src='img/add.gif' alt='*' style=width:15px;'/></a>\n";
}

echo "</td></tr><tr style='vertical-align:top;'><td>";
echo "Commentaires : </td><td>";
echo "<textarea name='commentaires' cols='25' rows='5'>$commentaires</textarea>";
echo "</td></tr>";

if($config['Absences-validation']){
  echo "<tr><td>Validation : </td><td>\n";
  if($admin){
    echo "<select name='valide'>\n";
    echo "<option value='0' $select1>En attente de validation</option>\n";
    echo "<option value='1' $select2>Accept&eacute;e</option>\n";
    echo "<option value='-1' $select3>Refus&eacute;e</option>\n";
    echo "</select>\n";
  }
  else{
    echo $validation_texte;
    echo "<input type='hidden' name='valide' value='$valide' />\n";
  }
  echo "</td></tr>\n";
}

echo "<tr><td colspan='2'><br/>\n";
if($admin or $valide==0 or $config['Absences-validation']==0){
  echo "<input type='button' value='Supprimer' onclick='document.location.href=\"index.php?page=absences/delete.php&amp;id=$id\";'/>";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='button' value='Annuler' onclick='annuler(1);'/>\n";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='submit' value='Valider'/>\n";
}
else{
  echo "<input type='button' value='Retour' onclick='annuler(1);'/>\n";
}
echo "</td></tr>\n";
echo "</table>\n";
echo "<input type='hidden' name='id' value='$id'/>";
echo "</form>\n";
echo "<script type='text/JavaScript'>initform(\"hre_debut=$hre_debut\");</script>";
echo "<script type='text/JavaScript'>initform(\"hre_fin=$hre_fin\");</script>";
?>