<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : activites/modif.php
Création : mai 2011
Dernière modification : 4 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

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
  $nom=$db->result[0]['nom'];
  $action="modif";
}
else{
  echo "<h3>Ajout d'une activité</h3>\n";
  $action="ajout";
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
echo "<tr><td colspan='2' style='text-align:center;'>\n";
echo "<br/>";
echo "<input type='hidden' value='$action' name='action'/>";
echo "<input type='hidden' value='$id' name='id'/>\n";
echo "<input type='button' value='Annuler' onclick='history.go(-1);' class='ui-button'/>\n";
echo "&nbsp;&nbsp;&nbsp;\n";
echo "<input type='submit' value='Valider' class='ui-button'/>\n";
echo "</td></tr>\n";
echo "</table>\n";
echo "</form>\n";
?>