<?php
/*
Planning Biblio, Version 1.9.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/header.php
Création : mai 2011
Dernière modification : 23 février 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affcihe l'entête HTML
Page notamment appelée par les fichiers index.php, admin/index.php
*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}
$theme=$config['Affichage-theme']?$config['Affichage-theme']:"default";
$themeJQuery=$config['Affichage-theme']?$config['Affichage-theme']:"default";
if(!file_exists("themes/$theme/jquery-ui.min.css")){
  $themeJQuery="default";
}
if(!file_exists("themes/$theme/$theme.css")){
  $theme="default";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Planning</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type='text/JavaScript' src='vendor/jquery-1.11.1.min.js'></script>
<script type='text/JavaScript' src='vendor/jquery-ui-1.11.2/jquery-ui.js'></script>
<script type='text/JavaScript' src='vendor/carhartl-jquery-cookie-3caf209/jquery.cookie.js'></script>
<script type='text/JavaScript' src='vendor/DataTables-1.10.4/media/js/jquery.dataTables.min.js'></script>
<script type='text/JavaScript' src='vendor/DataTables-1.10.4/extensions/FixedColumns/js/dataTables.fixedColumns.min.js'></script>
<script type='text/JavaScript' src='vendor/DataTables-1.10.4/extensions/TableTools/js/dataTables.tableTools.min.js'></script>
<script type='text/JavaScript' src='vendor/dataTables.jqueryui.js'></script>
<script type='text/JavaScript' src='js/datePickerFr.js'></script>
<script type='text/JavaScript' src='js/dataTables.sort.js'></script>
<script type='text/JavaScript' src='js/script.js'></script>
<?php
getJSFiles($page);
?>

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel='StyleSheet' href='vendor/DataTables-1.10.4/media/css/jquery.dataTables_themeroller.css' type='text/css' media='all'/>
<link rel='StyleSheet' href='vendor/DataTables-1.10.4/extensions/TableTools/css/dataTables.tableTools.min.css' type='text/css' media='all'/>
<link rel='StyleSheet' href='themes/<?php echo $themeJQuery; ?>/jquery-ui.min.css' type='text/css' media='all'/>
<link rel='StyleSheet' href='themes/default/default.css' type='text/css' media='all'/>
<link rel='StyleSheet' href='themes/default/print.css' type='text/css' media='print'/>
<link rel='StyleSheet' href='themes/<?php echo "$theme/$theme"; ?>.css' type='text/css' media='all'/>
</head>

<?php
echo $page=="aide/index.php"?"<body onscroll='position_retour();'>\n":"<body>\n";
if(!isset($_GET['positionOff'])){
  echo <<<EOD
  <!--		Récupération de la position du pointeur		-->
  <form name='position' action='#'>
  <input type='hidden' name='x' />
  <input type='hidden' name='y' />
  </form>
EOD;
}
?>

<div id='opac' style='display:none'></div>
<div style='position:relative;top:30px;' class='noprint'>
</div>