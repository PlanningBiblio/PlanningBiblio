<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.6.6
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2014 - Jérôme Combes											*
*																*
* Fichier : planning/postes_cfg/suppression.php											*
* Création : 10 septembre 2012													*
* Dernière modification : 17 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Supprime un complétement un tableau ou une sélection de tableaux. Supprime les horaires, cellules grisées, lignes et 		*
* et l'identifiant du tableau (table pl_poste_tab).										*
*																*
* Page appelée en arrière plan par la fonction JavaScript "popup" en cas de click sur l'icône suppression			*
* ou par la fonction supprime_select en cas de suppressions multiples								*
*********************************************************************************************************************************/

require_once "class.tableaux.php";

if(isset($_GET['ids'])){		//	Suppression multiple, function supprime_select
  $db=new db();
  $db->delete("pl_poste_horaires","numero IN ({$_GET['ids']})");
  $db=new db();
  $db->delete("pl_poste_cellules","numero IN ({$_GET['ids']})");
  $db=new db();
  $db->delete("pl_poste_lignes","numero IN ({$_GET['ids']})");
  $db=new db();
  $db->delete("pl_poste_tab","tableau IN ({$_GET['ids']})");
  exit;
}

echo "<div style='text-align:center'>\n";	//	Suppression simple
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
  echo "<br/>Etes vous sûr(e) de vouloir supprimmer ce tableau ?<br/><br/>\n";
  echo "<a href='javascript:popup_closed();'>Non</a>&nbsp;&nbsp;&nbsp;\n";
  echo "<a href='index.php?page=planning/postes_cfg/suppression.php&amp;menu=off&amp;confirm=confirm&amp;numero={$_GET['numero']}'>Oui</a>&nbsp;&nbsp;&nbsp;\n";
}
echo "</div>\n";
?>