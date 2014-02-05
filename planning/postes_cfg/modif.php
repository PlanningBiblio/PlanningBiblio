<?php
/*
Planning Biblio, Version 1.7.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/modif.php
Création : mai 2011
Dernière modification : 22 janvier 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet la modification des tableaux. Séparée en 2 onglets, un pour configurer les horaires, un autre pour les lignes.
Appelle les pages planning/postes_cfg/horaires.php et planning/postes_cfg/lignes.php

Page appelée par le fichier index.php, accessible en cliquant sur les icônes "modifier" du tableau "Listes des tableaux"
*/

require_once "class.tableaux.php";
include "planning/poste/fonctions.php";

// Choix du tableau
if(!in_array("cfg_num",$_SESSION))
  $_SESSION['cfg_num']="1";
$tableauNumero=isset($_POST['numero'])?$_POST['numero']:$_SESSION['cfg_num'];
if(isset($_GET['numero']))
  $tableauNumero=$_GET['numero'];
$_SESSION['cfg_num']=$tableauNumero;

$db=new db();
$db->query("SELECT * FROM `{$dbprefix}pl_poste_tab` WHERE `tableau`='$tableauNumero';");
$tableauNom=$db->result[0]['nom'];

// Affichage
echo "<h3>Configuration du tableau &quot;$tableauNom&quot;</h3>\n";
echo "<div id='tabs'>\n";
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
echo "<div id='div_tableaux'>\n";
include "tableaux.php";
echo "</div>\n";

// Onglet Horaires
echo "<div id='div_horaires'>\n";
include "horaires.php";
echo "</div>\n";

// Onglet Lignes
echo "<div id='div_lignes'>\n";
include "lignes.php";
echo "</div>\n";

echo "</div>\n";
?>

<!-- Affichage des informations de mise à jour -->
<div id='TableauxTips' class='ui-widget' style='position:absolute;'></div>

<!-- Initialisation des onglets, lien retour et affichage d'informations -->
<script type='text/JavaScript'>
$("#tabs").tabs();
$(".retour").click(function(){
  document.location.href="index.php?page=planning/postes_cfg/index.php";
});

$("#tabs").click(function(){
  $("#TableauxTips").hide();
});

<?php
if(isset($_REQUEST['cfg-type'])){
  echo <<<EOD
    $("#tabs").ready(function(){
      $("#{$_REQUEST['cfg-type']}").click();
    });
EOD;
}
?>
</script>