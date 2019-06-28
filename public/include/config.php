<?php
/**
Planning Biblio, Version 2.8.04
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : include/config.php
Création : mai 2011
Dernière modification : 8 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier de configuration. Contient les informations de connexion à la base de données MySQL.
Initialise la variable globale "$config" avec les informations contenues dans la table "config".

Ce fichier est inclus dans les pages index.php, authentification.php, admin/index.php, setup/index.php et setup/fin.php
*/

// Securité : Traitement pour une reponse Ajax
if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $version='ajax';
}

global $config;
$config=array();

// Paramètres MySQL
$config['dbhost']="localhost";
$config['dbname']="test_utf";
$config['dbname']="bulac";
// $config['dbname']="test_multisites";
$config['dbuser']="root";
$config['dbpass']="Pc4wxy";
$config['dbprefix']="oups_";
$dbprefix=$config['dbprefix'];

$config['secret']="6bbab46090b2c7bc8118a2f4";
// $config['demo'] = true;
// $config['demo-password'] = 'TiZ6W9iAGh';

include 'db.php';

// Récuperation des paramètres stockés dans la base de données
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}config` ORDER BY `id`;");
foreach ($db->result as $elem) {
    $config[$elem['nom']]=$elem['valeur'];
}

// Si pas de $version ou pas de reponseAjax => acces direct au fichier => Accès refusé
if (!isset($version)) {
    include_once "accessDenied.php";
}
