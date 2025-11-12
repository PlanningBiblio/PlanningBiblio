<?php
/**
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/noConfig.php
Création : 8 avril 2015
Dernière modification : 8 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche une page renvoyant vers le fichier setup/index.php si le fichier de configuration est absent

Page appelée (include) par le fichier et index.php si le fichier include/config.php est absent
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once(__DIR__.'/../include/accessDenied.php');
    exit;
}

// Construction du chemin relatif pour trouver les fichiers css
$dir1=dirname(__DIR__);
// Fichier demandé
$dir2=$_SERVER["SCRIPT_FILENAME"];
// On récupère les 2 derniers dossiers de l'application plutôt que de récupérer le chemin absolut pour éviter les problèmes d'alias
$tmp1=explode("/", $dir1);
$tmp2=$tmp1[count($tmp1)-2]."/".$tmp1[count($tmp1)-1];
// On recherche $tmp2 dans $dir2 pour récupérer la position
$pos=stripos($dir2, $tmp2);
$tmp3=substr($dir2, $pos);
$nb=substr_count($tmp3, "/")-2;
$path="";
for ($i=0;$i<$nb;$i++) {
    $path.="../";
}

?>
<!DOCTYPE html>
<html>
<head>
<title>Planning</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
<link rel='StyleSheet' href='<?php echo $path; ?>themes/default/default.css' type='text/css' media='all'/>
</head>

<body>
<div id='auth-logo'></div>
<h2 id='h2-authentification'>Fichier de configuration manquant</h2>
<center>
<strong>
Le fichier de configuration est manquant.<br/> 
<a href='setup'>Cliquez ici pour commencer l'installation.</a>
</strong>
</center>
<?php
include(__DIR__.'/footer.php');
exit;
?>