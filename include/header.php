<?php
/*
Planning Biblio, Version 1.7.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : include/header.php
Création : mai 2011
Dernière modification : 2 février 2014
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Planning</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type='text/JavaScript' src='js/jquery-1.11.0.min.js'></script>
<script type='text/JavaScript' src='js/jquery-ui-1.10.4/ui/jquery-ui.js'></script>
<script type='text/JavaScript' src='js/datePickerFr.js'></script>
<script type='text/JavaScript' src='js/carhartl-jquery-cookie-3caf209/jquery.cookie.js'></script>
<script type='text/JavaScript' src='js/dataTables/jquery.dataTables.min.js'></script>
<script type='text/JavaScript' src='js/dataTables/FixedColumns.nightly.js'></script>
<script type='text/JavaScript' src='js/dataTables/sort.js'></script>
<script type='text/JavaScript' src='js/script.js'></script>
<script type='text/JavaScript' src='js/infobulles.js'></script>
<?php
getJSFiles($page);
?>

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel='StyleSheet' href='css/dataTables/jquery.dataTables_themeroller.css' type='text/css' media='all'/>
<link rel='StyleSheet' href='themes/<?php echo $theme; ?>/jquery-ui.min.css' type='text/css' media='all'/>
<link rel='StyleSheet' href='css/style.css' type='text/css' media='all'/>
<link rel='StyleSheet' href='css/print.css' type='text/css' media='print'/>
<link rel='StyleSheet' href='themes/<?php echo $theme; ?>/<?php echo $theme; ?>.css' type='text/css' media='all'/>
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
<iframe id='calendrier' style='display:none' scrolling='no'></iframe>
</div>
