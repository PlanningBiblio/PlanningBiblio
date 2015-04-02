<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : include/config.sample.php
Création : 2 avril 2015
Dernière modification : 2 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

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

// Si pas de $version ou pas de reponseAjax => acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
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
include 'db.php';
//		Récuperation des paramètres stockés dans la base de données
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}config` ORDER BY `id`;");
foreach($db->result as $elem){
  $config[$elem['nom']]=$elem['valeur'];
}
?>