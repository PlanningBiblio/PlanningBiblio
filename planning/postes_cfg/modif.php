<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/modif.php
Création : mai 2011
Dernière modification : 7 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Permet la modification des tableaux. Séparée en 2 onglets, un pour configurer les horaires, un autre pour les lignes.
Appelle les pages planning/postes_cfg/horaires.php et planning/postes_cfg/lignes.php

Page appelée par le fichier index.php, accessible en cliquant sur les icônes "modifier" du tableau "Listes des tableaux"
*/

require_once "class.tableaux.php";
include "planning/poste/fonctions.php";

// Initialisation des variables
$cfgType=filter_input(INPUT_POST,"cfg-type",FILTER_SANITIZE_STRING);
$cfgTypeGet=filter_input(INPUT_GET,"cfg-type",FILTER_SANITIZE_STRING);
$tableauNumero=filter_input(INPUT_POST,"numero",FILTER_SANITIZE_NUMBER_INT);
$tableauGet=filter_input(INPUT_GET,"numero",FILTER_SANITIZE_NUMBER_INT);

// Choix du tableau
if($tableauGet){
  $tableauNumero=$tableauGet;
}
if(!$tableauNumero and in_array("cfg_num",$_SESSION)){
  $tableauNumero=$_SESSION['cfg_num'];
}
if(!$tableauNumero and !in_array("cfg_num",$_SESSION)){
  $tableauNumero="1";
}
$_SESSION['cfg_num']=$tableauNumero;

// Choix de l'onglet (cfg-type)
if($cfgTypeGet){
  $cfgType=$cfgTypeGet;
}
if(!$cfgType and in_array("cfg_type",$_SESSION)){
  $cfgType=$_SESSION['cfg_type'];
}
if(!$cfgType and !in_array("cfg_type",$_SESSION)){
  $cfgType="tableaux";
}
$_SESSION['cfg_type']=$cfgType;

$db=new db();
$db->select2("pl_poste_tab","*",array("tableau"=>$tableauNumero));
$tableauNom=$db->result[0]['nom'];

// Affichage
echo "<h3>Configuration du tableau &quot;$tableauNom&quot;</h3>\n";
echo "<div id='tabs' class='ui-tabs'>\n";
echo "<ul>\n";
if($config['Multisites-nombre']>1){
  echo "<li><a href='#div_site' id='site'>Site</a></li>\n";
}
echo "<li><a href='#div_tableaux' id='tableaux'>Nombre de tableaux</a></li>\n";
echo "<li><a href='#div_horaires' id='horaires'>Horaires</a></li>\n";
echo "<li><a href='#div_lignes' id='lignes'>Lignes</a></li>\n";
echo "</ul>\n";

// Onglet Site
if($config['Multisites-nombre']>1){
  echo "<div id='div_site'>\n";
  include "site.php";
  echo "</div>\n";
}

// Onglet Tableaux
$displayTableaux=$config['Multisites-nombre']>1?"style='display:none;'":null;
echo "<div id='div_tableaux' $displayTableaux >\n";
include "tableaux.php";
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

<!-- Initialisation des onglets, lien retour et affichage d'informations -->
<script type='text/JavaScript'>
$(".retour").click(function(){
  document.location.href="index.php?page=planning/postes_cfg/index.php";
});

<?php
if($cfgType){
  echo <<<EOD
    $(document).ready(function(){
      $("#div_{$cfgType}").click();
    });
EOD;
}
?>
</script>