<?php
/*
Planning Biblio, Version 1.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : planning/postes_cfg/modif.php
Création : mai 2011
Dernière modification : 21 août 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet la modification des tableaux. Séparée en 2 onglets, un pour configurer les horaires, un autre pour les lignes.
Appelle les pages planning/postes_cfg/horaires.php et planning/postes_cfg/lignes.php

Page appelée par le fichier index.php, accessible en cliquant sur les icônes "modifier" du tableau "Listes des tableaux"
*/

require_once "class.tableaux.php";
include "planning/poste/fonctions.php";

$li1="current";
$li2="li2";
if(isset($_REQUEST['cfg-type'])){
  switch($_REQUEST['cfg-type']){
    case "lignes" :	$li1="li1"; $li2="current"; $li3="li3"; break;
    case "lignes_sep" :	$li1="li1"; $li2="li2"; $li3="current"; break;
  }
}

?>
<div id='onglets'>
<font id='titre'>Configuration des tableaux</font>
<ul style='position:absolute;left:400px;'>		
<?php echo "<li id='$li1'>"; ?><a href='javascript:show("horaires","lignes","li1");'>Horaires</a></li>
<?php echo "<li id='$li2'>"; ?><a href='javascript:show("lignes","horaires","li2");'>Lignes</a></li>
</ul>
</div>

<div style='position:relative; margin:80px 0 0 0;'>

<?php
//	choix du tableau
if(!in_array("cfg_num",$_SESSION))
  $_SESSION['cfg_num']="1";
$tableauNumero=isset($_POST['numero'])?$_POST['numero']:$_SESSION['cfg_num'];
if(isset($_GET['numero']))
  $tableauNumero=$_GET['numero'];
$_SESSION['cfg_num']=$tableauNumero;

//	Debut Horaires	//
$display=$li1=="li1"?"display:none":null;
echo "<div id='horaires' style='margin-left:80px;$display'>\n";
include "horaires.php";
echo "</div>\n";
//	Fin Horaires	//

//	Debut Lignes	//
$display=$li2=="li2"?"display:none":null;
echo "<div id='lignes' style='$display'>\n";
include "lignes.php";
echo "</div>\n";
//	Fin Lignes	//
?>
</div>