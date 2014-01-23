<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.6.7
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2014 - Jérôme Combes											*
*																*
* Fichier : planning/modeles/index.php												*
* Création : mai 2011														*
* Dernière modification : 16 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Affiche la liste des modèles de planning enregistrés.										*
* Permet de les supprimer (icône corbeille) ou de les renommer (icône papier)							*
*																*
* Cette page est appelée par le fichier index.php										*
*********************************************************************************************************************************/

require_once "class.modeles.php";

echo "<h3>Modèles de planning</h3>\n";

//	Initialisation des variables
$class=null;
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}pl_poste_modeles` GROUP BY `nom`;");
if(!$db->result){
  echo "Aucun modèle enregistré\n";
  include "include/footer.php";
  exit;
}
	
$modeles=$db->result;

// tri
$tri=isset($_GET['tri'])?$_GET['tri']:null;
$cmp=$tri=="nom desc"?"cmp_1desc":"cmp_1";
usort($modeles,$cmp);

echo "<table style='width:100%' cellspacing='0'>";
echo "<tr class='th'>\n";
echo "<td style='width:80px;'>&nbsp;</td>\n";
echo "<td>nom";
echo "&nbsp;&nbsp;<a href='index.php?page=planning/modeles/index.php&amp;tri=nom'><img src='img/up.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "<a href='index.php?page=planning/modeles/index.php&amp;tri=nom%20desc'><img src='img/down.png' alt='-' border='0' style='width:10px;'/></a>\n";
echo "</td>";
echo "</tr>\n";


foreach($modeles as $elem){
  $class=$class=="tr2"?"tr1":"tr2";
  echo "<tr class='$class'>\n";
  echo "<td>\n";
  echo "<a href='index.php?page=planning/modeles/modif.php&amp;nom={$elem['nom']}'><img src='img/modif.png' border='0' alt='Modif' /></a>";
  echo "&nbsp;&nbsp;";
  echo "<a href='javascript:supprime(\"planning/modeles\",\"{$elem['nom']}\");'><img src='img/suppr.png' border='0' alt='Suppression' /></a>";
  echo "</td>\n";
  echo "<td>{$elem['nom']}</td>\n";
  echo "</tr>\n";
}
echo "</table>\n";
?>