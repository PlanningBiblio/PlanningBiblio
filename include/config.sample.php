<?php
/**
Planning Biblio, Version 2.5.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : include/config.sample.php
Création : 2 avril 2015
Dernière modification : 7 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Exemple de fichier de configuration. 
A copier vers include/config.php SI la création du fichier n'est pas possible avec le setup.
Remplacez les informarions "your_db_xxx" par vos valeurs

Contient les informations de connexion à la base de données MySQL.
Initialise la variable globale "$config" avec les informations contenues dans la table "config".
Le fichier include/config.php est inclus dans les pages index.php, authentification.php, admin/index.php, setup/index.php et setup/fin.php
*/


// Securité : Traitement pour une reponse Ajax
if(array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
  $version='ajax';
}

global $config;
$config=Array();

//		Paramètres MySQL
$config['dbhost']="your_db_host";
$config['dbname']="your_db_name";
$config['dbuser']="your_db_user";
$config['dbpass']="your_db_pass";
$config['dbprefix']="your_db_prefix";

$dbprefix=$config['dbprefix'];

$config['secret']="0449770b2bd5046b6dcb1697";     // Pour votre sécurité, veuillez modifier cette valeur. La clé doit comporter 24 caractères.

include_once "db.php";

//		Récuperation des paramètres stockés dans la base de données
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}config` ORDER BY `id`;");
foreach($db->result as $elem){
  $config[$elem['nom']]=$elem['valeur'];
}

// Si pas de $version ou pas de reponseAjax => acces direct au fichier => Accès refusé
if(!isset($version)){
  include_once "accessDenied.php";
}
?>