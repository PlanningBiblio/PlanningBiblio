<?php
/**
Planning Biblio, Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/modeles/index.php
Création : mai 2011
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche la liste des modèles de planning enregistrés.
Permet de les supprimer (icône corbeille) ou de les renommer (icône papier)

Cette page est appelée par le fichier index.php
*/

require_once "class.modeles.php";


//	Initialisation des variables
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}pl_poste_modeles` GROUP BY `nom`;");
if(!$db->result){
  echo "Aucun modèle enregistré\n";
  include "include/footer.php";
  exit;
}
	
$modeles=$db->result;

echo "<h3>Modèles de planning</h3>\n";
echo "<table id='tableModeles' class='CJDataTable' data-sort='[[1]]'>";
echo "<thead><tr><th class='dataTableNoSort'>&nbsp;</th><th>nom</th></tr></thead>\n";
echo "<tbody>\n";

foreach($modeles as $elem){
  echo "<tr>\n";
  echo "<td>\n";
  echo "<a href='index.php?page=planning/modeles/modif.php&amp;nom={$elem['nom']}'><span class='pl-icon pl-icon-edit' title='Modifier'></span></a>";
  echo "<a href='javascript:supprime(\"planning/modeles\",\"{$elem['nom']}\",\"$CSRFSession\");'><span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>";
  echo "</td>\n";
  echo "<td>{$elem['nom']}</td>\n";
  echo "</tr>\n";
}
echo "</tbody>\n";
echo "</table>\n";
?>