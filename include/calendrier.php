<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : include/calendrier.php
Création : mai 2011
Dernière modification : 4 octobre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'intégrer le calendrier dans un cadre flottant <iframe>

Utilisée directement dans la page planning/poste/index.php (page d'affichage du planning)
Utilisée par la fonction JS calendrier qui affiche la calendrier lors des clics sur les icônes "calendrier"
*/

session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Calendrier</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel='StyleSheet' href='calendrier/calendar.css' type='text/css' />
<link rel='StyleSheet' href='../css/calendrier.css' type='text/css' />
<link rel='StyleSheet' href='../css/custom.css' type='text/css' />
<script type="text/JavaScript" src='../js/calendrier.js'></script>
</head>
<body>
<?php
require_once("calendrier/calendar.php");
$parametres=array("DATE_URL"=>"javascript:setPlDate('__DATE__')","USE_SESSION"=>true,"URL_DAY_DATE_FORMAT"=>"Ymd");

switch($_GET['champ']){
  case "pl_poste" :
    Calendar($parametres);
      break;
  default : 
    $parametres["DATE_URL"]="javascript:setDate('__DATE__','{$_GET['champ']}','{$_GET['form']}')";
    $parametres["USE_SESSION"]=false;
    Calendar($parametres);
    echo "<a href='javascript:parent.document.getElementById(\"calendrier\").style.display=\"none\";'>Fermer</a>\n";
      break;
}
?>
</body>
</html>