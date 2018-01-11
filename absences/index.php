<?php
/**
Planning Biblio, Version 1.8.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : absences/index.php
Création : mai 2011
Dernière modification : 24 juin 2014
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche les liens voir les absences, ajouter une absence, ajouter une information (relative aux absences)
Affiche les informations relatives aux absences_infos

Page appelée par la page index.php 
*/

require_once "class.absences.php";
?>

<h3>Absences</h3>
<table>
<tr style='vertical-align:top;'>
<td style='width:400px;'>
<ul style='margin-top:0px;'>
<li><a href='index.php?page=absences/voir.php'>Voir les absences</a></li>
<li><a href='index.php?page=absences/ajouter.php'>Ajouter une absence</a></li>
<?php
$admin=in_array(1,$droits)?true:false;
if($admin)
  echo "<li><a href='index.php?page=absences/infos.php'>Ajouter une information</a></li>\n";

echo "</ul>\n";
echo "</td>\n";
echo "<td style='color:#FF5E0E;'>\n";

$date=date("Y-m-d");
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}absences_infos` WHERE `fin`>='$date' ORDER BY `debut`,`fin`;");
if($db->result){
  echo "<b>Informations sur les absences :</b><br/><br/>\n";
  foreach($db->result as $elem){
    if($admin){
      echo "<a href='index.php?page=absences/infos.php&amp;id={$elem['id']}'>\n";
      echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
    }
    echo "Du ".dateFr($elem['debut'])." au ".dateFr($elem['fin'])." : {$elem['texte']}<br/>\n";
  }	
}
?>
</td></tr></table>