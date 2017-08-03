<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/postes_cfg/modif.php
Création : mai 2011
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet la modification des tableaux. Séparée en 2 onglets, un pour configurer les horaires, un autre pour les lignes.
Appelle les pages planning/postes_cfg/horaires.php et planning/postes_cfg/lignes.php

Page appelée par le fichier index.php, accessible en cliquant sur les icônes "modifier" du tableau "Listes des tableaux"
*/

require_once "class.tableaux.php";
include "planning/poste/fonctions.php";

// Initialisation des variables
$CSRFToken = filter_input(INPUT_POST,"CSRFToken",FILTER_SANITIZE_STRING);
$cfgType=filter_input(INPUT_POST,"cfg-type",FILTER_SANITIZE_STRING);
$cfgTypeGet=filter_input(INPUT_GET,"cfg-type",FILTER_SANITIZE_STRING);
$tableauNumero=filter_input(INPUT_POST,"numero",FILTER_SANITIZE_NUMBER_INT);
$tableauGet=filter_input(INPUT_GET,"numero",FILTER_SANITIZE_NUMBER_INT);

// Choix du tableau
if($tableauGet){
  $tableauNumero=$tableauGet;
}

// Choix de l'onglet (cfg-type)
if($cfgTypeGet){
  $cfgType=$cfgTypeGet;
}
if(!$cfgType and in_array("cfg_type",$_SESSION)){
  $cfgType=$_SESSION['cfg_type'];
}
if(!$cfgType and !in_array("cfg_type",$_SESSION)){
  $cfgType="infos";
}
$_SESSION['cfg_type']=$cfgType;

$db=new db();
$db->select2("pl_poste_tab","*",array("tableau"=>$tableauNumero));
$tableauNom=$db->result[0]['nom'];

// Affichage
if(!$tableauNumero){
  echo "<h3>Nouveau tableau</h3>\n";
}else{
  echo "<h3>Configuration du tableau &quot;$tableauNom&quot;</h3>\n";
}

echo "<div id='tabs' class='ui-tabs' data-active='$cfgType'>\n";
echo "<ul>\n";
echo "<li><a href='#div_infos' id='infos'>Infos générales</a></li>\n";
echo "<li><a href='#div_horaires' id='horaires'>Horaires</a></li>\n";
echo "<li><a href='#div_lignes' id='lignes'>Lignes</a></li>\n";
echo "<li class='ui-tab-cancel'><a href='index.php?page=planning/postes_cfg/index.php' >Retour</a></li>\n";
echo "<li class='ui-tab-submit'><a href='javascript:tableauxInfos();' class='tableaux-valide'>Valider</a></li>\n";

echo "</ul>\n";

// Onglet Infos générales
echo "<div id='div_infos'>\n";
include "infos.php";
echo "</div>\n";

// Onglet Horaires
echo "<div id='div_horaires' style='display:none;'>\n";
include "horaires.php";
echo "</div>\n";

// Onglet Lignes
echo "<div id='div_lignes' style='display:none;'>\n";
include "lignes.php";
echo "</div>\n";

echo "</div>\n";
?>