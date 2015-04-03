<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/modif.php
Création : mai 2011
Dernière modification : 3 avril 2015
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
if(isset($_REQUEST['message'])){
  echo <<<EOD
    information("{$_REQUEST['message']}","{$_REQUEST['msg-type']}");
EOD;
}

if(isset($_REQUEST['cfg-type'])){
  echo <<<EOD
    $("#tabs").ready(function(){
      $("#{$_REQUEST['cfg-type']}").click();
    });
EOD;
}
?>
</script>