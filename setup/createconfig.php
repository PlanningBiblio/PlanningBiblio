<?php
/**
Planning Biblio, Version 2.5.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : setup/createconfig.php
Création : mai 2011
Dernière modification : 8 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de créer le fichier de configuration (include/config.php) lors de l'installation.
Récupère les informations saisies dans le formulaire de la page setup/index.php (identifiant administrateur MySQL,
nom de la base de données à créer, identifiant de l'utilisateur de la base de données à créer

Inclus ensuite le fichier setup/config.php affichant le formulaire demandant les informations sur le responsable du planning
*/

$version=filter_input(INPUT_POST, "version", FILTER_SANITIZE_STRING);
$Fnm = "../include/config.php";

// Génération d'une clé pour crypter les mots de passe LDAP-Password et Mail-Password
// PHP 7
if (phpversion() >= 7) {
    $secret = bin2hex(random_bytes(12));
}

// PHP 5.3+
else {
    $secret = bin2hex(openssl_random_pseudo_bytes(12));
}

$file=array();
$file[]="<?php\n";
$file[]="/**\n";
$file[]="Planning Biblio, Version $version\n";
$file[]="Licence GNU/GPL (version 2 et au dela)\n";
$file[]="Voir les fichiers README.md et LICENSE\n";
$file[]="@copyright 2011-2018 Jérôme Combes\n";
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
$file[]="\$config['dbhost']=\"{$_POST['dbhost']}\";\n";
$file[]="\$config['dbname']=\"{$_POST['dbname']}\";\n";
$file[]="\$config['dbuser']=\"{$_POST['dbuser']}\";\n";
$file[]="\$config['dbpass']=\"{$_POST['dbpass']}\";\n";
$file[]="\$config['dbprefix']=\"{$_POST['dbprefix']}\";\n";
$file[]="\$dbprefix=\$config['dbprefix'];\n\n";
$file[]="\$config['secret']=\"$secret\";\n\n";
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

if (!$inF=fopen($Fnm, "w\n")) {
    echo "<p style='color:red;'>Ne peut pas créer le fichier include/config.php. Vérifiez les droits d'accès au dossier include.</p>\n";
    exit;
}

foreach ($file as $line) {
    fputs($inF, $line);
}
fclose($inF);

echo "<p>Le fichier config.php a bien été créé.</p>\n";
include "config.php";
