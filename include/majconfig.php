<?php
/**
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : setup/createconfig.php
Création : 31 octobre 2013
Dernière modification : 22 janvier 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de créer le fichier de configuration (include/config.php) lors de l'installation.
Récupère les informations saisies dans le formulaire de la page setup/index.php (identifiant administrateur MySQL,
nom de la base de données à créer, identifiant de l'utilisateur de la base de données à créer

Inclus ensuite le fichier setup/config.php affichant le formulaire demandant les informations sur le responsable du planning 
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if(__FILE__ == $_SERVER['SCRIPT_FILENAME']){
  include_once "accessDenied.php";
  exit;
}

$Fnm = "include/config.php";

$file=Array();
$file[]="<?php\n";
$file[]="/*\n";
$file[]="Planning Biblio, Version 1.9.5\n";
$file[]="Licence GNU/GPL (version 2 et au dela)\n";
$file[]="Voir les fichiers README.md et LICENSE\n";
$file[]="@copyright 2011-2016 Jérôme Combes\n";
$file[]="\n";
$file[]="Fichier : include/config.php\n";
$file[]="Création : mai 2011\n";
$file[]="Dernière modification : 8 avril 2015\n";
$file[]="@author Jérôme Combes <jerome@planningbiblio.fr>\n";
$file[]="\n";
$file[]="Description :\n";
$file[]="Fichier de configuration. Contient les informations de connexion à la base de données MySQL.\n";
$file[]="Initialise la variable globale \"\$config\" avec les informations contenues dans la table \"config\".\n";
$file[]="\n";
$file[]="Ce fichier est inclus dans les pages index.php, authentification.php, admin/index.php, setup/index.php et setup/fin.php\n";
$file[]="*/\n\n";

$file[]="// Securité : Traitement pour une reponse Ajax\n";
$file[]="if(array_key_exists('HTTP_X_REQUESTED_WITH', \$_SERVER) and strtolower(\$_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){\n";
$file[]="  \$version='ajax';\n";
$file[]="}\n\n";
$file[]="global \$config;\n";
$file[]="\$config=Array();\n\n";
$file[]="// Paramètres MySQL\n";
$file[]="\$config['dbhost']=\"{$config['dbhost']}\";\n";
$file[]="\$config['dbname']=\"{$config['dbname']}\";\n";
$file[]="\$config['dbuser']=\"{$config['dbuser']}\";\n";
$file[]="\$config['dbpass']=\"{$config['dbpass']}\";\n";
$file[]="\$config['dbprefix']=\"{$config['dbprefix']}\";\n";
$file[]="\$dbprefix=\$config['dbprefix'];\n\n";
$file[]="include 'db.php';\n\n";
$file[]="// Récuperation des paramètres stockés dans la base de données\n";
$file[]="\$db=new db();\n";
$file[]="\$db->query(\"SELECT * FROM `{\$dbprefix}config` ORDER BY `id`;\");\n";
$file[]="foreach(\$db->result as \$elem){\n";
$file[]="  \$config[\$elem['nom']]=\$elem['valeur'];\n";
$file[]="}\n\n";
$file[]="// Si pas de \$version ou pas de reponseAjax => acces direct au fichier => Accès refusé\n";
$file[]="if(!isset(\$version)){\n";
$file[]="  include_once \"accessDenied.php\";\n";
$file[]="}\n";
$file[]="?>\n";

if(!$inF=fopen($Fnm,"w\n")){
  echo "<p style='color:red;'>Ne peut pas modifier le fichier include/config.php. V&eacute;rifiez les droits d&apos;acc&egrave;s au dossier include.</p>\n";
}

foreach($file as $line){
  fputs($inF,$line);
}
fclose($inF);
?>