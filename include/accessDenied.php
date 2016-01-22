<?php
/*
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : include/accessDenied.php
Création : 8 avril 2015
Dernière modification : 22 janvier 2016
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
</head>

<body>
<div id='auth-logo' style='margin:30px auto;'></div>
<h2 id='h2-authentification'>Accès refusé</h2>
<center>
<p style='font-weight:bold;'>
<?php echo $link; ?>
</p>
</center>
<div class='footer'>
PlanningBiblio - Copyright &copy; 2011-2016 - J&eacute;r&ocirc;me Combes - 
<a href='http://www.planningbiblio.fr' target='_blank' style='font-size:9pt;'>www.planningbiblio.fr</a>
</div>
</body>
</html>
<?php
exit;
?>