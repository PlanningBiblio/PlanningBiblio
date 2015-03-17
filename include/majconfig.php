<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : setup/createconfig.php
Création : 31 octobre 2013
Dernière modification : 31 octobre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de créer le fichier de configuration (include/config.php) lors de l'installation.
Récupère les informations saisies dans le formulaire de la page setup/index.php (identifiant administrateur MySQL,
nom de la base de données à créer, identifiant de l'utilisateur de la base de données à créer

Inclus ensuite le fichier setup/config.php affichant le formulaire demandant les informations sur le responsable du planning 
*/

// Sécurité
if(!$version){
  header("Location: ../index.php");
}

$Fnm = "include/config.php";

$file=Array();
$file[]="<?php\n";
$file[]="/*\n";
$file[]="Planning Biblio, Version 1.7.2\n";
$file[]="Licence GNU/GPL (version 2 et au dela)\n";
$file[]="Voir les fichiers README.md et LICENSE\n";
$file[]="Copyright (C) 2011-2015 - Jérôme Combes\n";
$file[]="\n";
$file[]="Fichier : include/config.php\n";
$file[]="Création : mai 2011\n";
$file[]="Dernière modification : 31 octobre 2013\n";
$file[]="Auteur : Jérôme Combes, jerome@planningbilbio.fr\n";
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
$file[]="}\n";
$file[]="\n";
$file[]="// Si pas de \$version ou pas de reponseAjax => acces direct aux pages de ce dossier => redirection vers la page index.php\n";
$file[]="if(!\$version){\n";
$file[]="  header(\"Location: ../index.php\");\n";
$file[]="}\n";
$file[]="global \$config;\n";
$file[]="\$config=Array();\n\n";
$file[]="//		Paramètres MySQL\n";
$file[]="\$config['dbhost']=\"localhost\";\n";
$file[]="\$config['dbname']=\"{$config['dbname']}\";\n";
$file[]="\$config['dbuser']=\"{$config['dbuser']}\";\n";
$file[]="\$config['dbpass']=\"{$config['dbpass']}\";\n";
$file[]="\$config['dbprefix']=\"{$config['dbprefix']}\";\n";
$file[]="\$dbprefix=\$config['dbprefix'];\n\n";
$file[]="include 'db.php';\n\n";
$file[]="//		Récuperation des paramètres stockés dans la base de données\n";
$file[]="\$db=new db();\n";
$file[]="\$db->query(\"SELECT * FROM `{\$dbprefix}config` ORDER BY `id`;\");\n";
$file[]="foreach(\$db->result as \$elem){\n";
$file[]="  \$config[\$elem['nom']]=\$elem['valeur'];\n";
$file[]="}\n\n";
$file[]="?>\n";

if(!$inF=fopen($Fnm,"w\n")){
  echo "<p style='color:red;'>Ne peut pas modifier le fichier include/config.php. V&eacute;rifiez les droits d&apos;acc&egrave;s au dossier include.</p>\n";
  exit;
}

foreach($file as $line){
  fputs($inF,$line);
}
fclose($inF);
?>