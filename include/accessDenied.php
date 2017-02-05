<?php
/**
Planning Biblio, Version 2.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : include/accessDenied.php
Création : 8 avril 2015
Dernière modification : 4 février 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche un message "Accès refusé" si la page incluant ce fichier est appelée directement (sans index.php, donc sans contrôle)
*/

// Construction du chemin relatif pour trouver les fichiers css
// Dossier contenant l'application Planning Biblio, également utilisé pour les include
$dir1=dirname(__DIR__);
// Fichier demandé
$dir2=$_SERVER["SCRIPT_FILENAME"];
// On récupère les 2 derniers dossiers de l'application plutôt que de récupérer le chemin absolut pour éviter les problèmes d'alias
$tmp1=explode("/",$dir1);
$tmp2=$tmp1[count($tmp1)-2]."/".$tmp1[count($tmp1)-1];
// On recherche $tmp2 dans $dir2 pour récupérer la position
$pos=stripos($dir2,$tmp2);
$tmp3=substr($dir2,$pos);
$nb=substr_count($tmp3,"/")-2;
$path="";
for($i=0;$i<$nb;$i++){
  $path.="../";
}

// Besoin de config pour récupérer le thème
// Utilise le chemin absolut plutôt que relatif sinon pb avec le dossier admin qui contient également un config.php
require_once $dir1."/include/sanitize.php";
require_once $dir1."/include/config.php";
$theme=$config['Affichage-theme'];

// Lien proposé pour le retour à l'application
$link="<a href='{$path}index.php'>Retour à l'application</a>";
?>

<!DOCTYPE html>
<html>
<head>
<title>Planning</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel='StyleSheet' href='<?php echo $path; ?>themes/default/default.css' type='text/css' media='all'/>
<link rel='StyleSheet' href='<?php echo $path; ?>themes/default/print.css' type='text/css' media='print'/>
<link rel='StyleSheet' href='<?php echo $path; ?>themes/<?php echo "$theme/$theme"; ?>.css' type='text/css' media='all'/>
<script type='text/JavaScript' src='<?php echo $path; ?>vendor/jquery-1.11.1.min.js'></script>
<script type='text/JavaScript' src='<?php echo $path; ?>js/script.js'></script>
</head>

<body>
<div id='auth-logo' style='margin:30px auto;'></div>
<h2 id='h2-authentification'>Accès refusé</h2>
<center>
<p style='font-weight:bold;'>
<?php
// IP Blocker : Message affiché si l'IP a été bloquée
if(isset($IPBlocker)){
	echo "L'adresse IP \"{$_SERVER['REMOTE_ADDR']}\" a &eacute;t&eacute; bloqu&eacute;e.\n";
	echo "<p id='chrono'></p>\n";
	echo "<p id='link' style='display:none;'>$link</p>\n";
	echo "<script type='text/JavaScript'>decompte($IPBlocker);</script>\n";
}else{
// Affichage du lien Retour vers l'application si tentative d'accès à une page interdite
	echo $link;
}
?>
</p>
</center>
<div class='footer'>
PlanningBiblio - Copyright &copy; 2011-2017 - J&eacute;r&ocirc;me Combes - 
<a href='http://www.planningbiblio.fr' target='_blank' style='font-size:9pt;'>www.planningbiblio.fr</a>
</div>
</body>
</html>
<?php
exit;
?>
