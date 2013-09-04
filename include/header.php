<?php
/*
Planning Biblio, Version 1.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : include/header.php
Création : mai 2011
Dernière modification : 4 septembre 2013
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
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<?php
echo "<link rel='StyleSheet' href='{$config['url']}/css/style.css' type='text/css' media='screen'/>\n";
echo "<link rel='StyleSheet' href='{$config['url']}/css/print.css' type='text/css' media='print'/>\n";
?>

<!--[if IE]>
<style type='text/css' media='print'>
body{
  width:350mm;
  height:240mm;
  -ms-filter: "progid:DXImageTransform.Microsoft.Matrix(M11=0.75, M12=0, M21=0, M22=0.75, SizingMethod='auto expand'); progid:DXImageTransform.Microsoft.BasicImage(rotation=3);"; 
  left:-10mm;
  bottom:22mm;
}
</style>
<![endif]-->
<?php
echo "<script type='text/JavaScript' src='{$config['url']}/js/jquery-1.10.2.min.js'></script>\n";
echo "<script type='text/JavaScript' src='{$config['url']}/js/script.js'></script>\n";
echo "<script type='text/JavaScript' src='{$config['url']}/js/infobulles.js'></script>\n";
foreach($plugins as $elem){
  if(file_exists("plugins/$elem/js/script.$elem.js")){
    echo "<script type='text/JavaScript' src='{$config['url']}/plugins/$elem/js/script.$elem.js'></script>\n";
  }
}
?>
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
<div style='position:relative;top:30px;'>
<iframe id='calendrier' style='display:none' scrolling='no'></iframe>
</div>



