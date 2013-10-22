<?php
/*
Planning Biblio, Version 1.5.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : include/header.php
Création : mai 2011
Dernière modification : 21 octobre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affcihe l'entête HTML
Page notamment appelée par les fichiers index.php, admin/index.php
*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}
?>
<html>
<head>
<title>Planning</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type='text/JavaScript' src='js/jquery-1.10.2.min.js'></script>
<script src="js/jquery-ui-1.10.3/ui/jquery-ui.js"></script>
<script type='text/JavaScript' src='js/script.js'></script>
<script type='text/JavaScript' src='js/infobulles.js'></script>
<script type='text/JavaScript' src='js/dialog.js'></script>
<script type='text/JavaScript' src='js/dataTables/jquery.dataTables.min.js'></script>

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel='StyleSheet' href='css/dataTables/jquery.dataTables_themeroller.css' type='text/css' media='screen'/>
<link rel='StyleSheet' href='css/jquery-ui-themes-1.10.3/themes/bulac/jquery-ui-1.10.3.custom.min.css' type='text/css' media='screen'/>
<link rel='StyleSheet' href='css/style.css' type='text/css' media='screen'/>
<link rel='StyleSheet' href='css/print.css' type='text/css' media='print'/>
<link rel='StyleSheet' href='css/custom.css' type='text/css' media='all'/>

<?php
foreach($plugins as $elem){
  if(file_exists("plugins/$elem/js/script.$elem.js")){
    echo "<script type='text/JavaScript' src='plugins/$elem/js/script.$elem.js'></script>\n";
  }
}

echo "</head>\n";

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
