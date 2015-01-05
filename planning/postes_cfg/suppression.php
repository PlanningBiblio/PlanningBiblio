<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/suppression.php
Création : 10 septembre 2012
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Supprime complétement un tableau. Supprime les horaires, cellules grisées, lignes et l'identifiant du tableau (table pl_poste_tab).

Page appelée en arrière plan par la fonction JavaScript "popup" en cas de click sur l'icône suppression
*/

require_once "class.tableaux.php";

echo "<div style='text-align:center'>\n";
if(isset($_GET['confirm'])){
  $db=new db();
  $db->query("DELETE FROM `{$dbprefix}pl_poste_horaires` WHERE `numero`='{$_GET['numero']}';");
  $db=new db();
  $db->query("DELETE FROM `{$dbprefix}pl_poste_cellules` WHERE `numero`='{$_GET['numero']}';");
  $db=new db();
  $db->query("DELETE FROM `{$dbprefix}pl_poste_lignes` WHERE `numero`='{$_GET['numero']}';");
  $db=new db();
  $db->query("DELETE FROM `{$dbprefix}pl_poste_tab` WHERE `tableau`='{$_GET['numero']}';");
  
  echo "<br/>Le tableau a été supprimé.<br/><br/>\n";
  echo "<a href='javascript:parent.location.href=\"index.php?page=planning/postes_cfg/index.php&cfg-type=horaires\";'>Fermer</a>&nbsp;&nbsp;&nbsp;\n";
}
else{
  echo "<br/>Etes vous sûr(e) de vouloir supprimer ce tableau ?<br/><br/>\n";
  echo "<a href='javascript:popup_closed();'>Non</a>&nbsp;&nbsp;&nbsp;\n";
  echo "<a href='index.php?page=planning/postes_cfg/suppression.php&amp;menu=off&amp;confirm=confirm&amp;numero={$_GET['numero']}'>Oui</a>&nbsp;&nbsp;&nbsp;\n";
}
echo "</div>\n";
?>