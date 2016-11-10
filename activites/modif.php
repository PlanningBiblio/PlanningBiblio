<?php
/**
Planning Biblio, Version 2.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : activites/modif.php
Création : mai 2011
Dernière modification : 10 novembre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'ajouter une activité ou de modifier le nom d'une activité

Page appelée par la page index.php
*/

require_once "class.activites.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);

if($id){
  echo "<h3>Modification de l'activité</h3>\n";
  $db=new db();
  $db->select2("activites","*",array("id"=>$id));
  $action="modif";
  $classePoste=$db->result[0]['classePoste'];
  $nom=$db->result[0]['nom'];
}
else{
  echo "<h3>Ajout d'une activité</h3>\n";
  $action="ajout";
  $classePoste=null;
  $nom=null;
}

echo "<form method='get' action='index.php' name='form'>";
echo "<input type='hidden' name='page' value='activites/valid.php' />\n";
echo "<table class='tableauFiches'>";
echo "<tr><td>";
echo "Nom :";
echo "</td><td>";
echo "<input type='text' value='$nom' name='nom' style='width:250px' class='ui-widget-content ui-corner-all'/>";
echo "</td></tr>";

echo "<tr><td>";
echo "Classe pour les postes associ&eacute;s &agrave; cette activit&eacute;<sup>*</sup> :";
echo "</td><td>";
echo "<input type='text' value='$classePoste' name='classePoste' style='width:250px' class='ui-widget-content ui-corner-all'/>";
echo "</td></tr>";

echo "<tr><td colspan='2' style='text-align:center; padding-top:40px;'>\n";
echo "<input type='hidden' value='$action' name='action'/>";
echo "<input type='hidden' value='$id' name='id'/>\n";
echo "<input type='button' value='Annuler' onclick='history.go(-1);' class='ui-button'/>\n";
echo "&nbsp;&nbsp;&nbsp;\n";
echo "<input type='submit' value='Valider' class='ui-button'/>\n";
echo "</td></tr>\n";
echo "</table>\n";

echo "</form>\n";

?>
<div class='important' style='margin-top:40px;'>
<p style='margin-left:30px;'>
<sup>* Classe CSS appliqu&eacute;e sur les lignes des postes associ&eacute;s &agrave; cette activit&eacute; dans les plannings. Permet de personnaliser l&apos;affichage de ces lignes.</sup><br/>
</p>
</div>