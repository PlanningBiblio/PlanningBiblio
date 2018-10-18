<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/ajax.hiddenTables.php
Création : 14 décembre 2015
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet l'enregistrement des préférences sur les tableaux cachés

Cette page est appelée par la function JavaScript "afficheTableauxDiv" utilisé par le fichier planning/poste/index.php
*/

ini_set("display_errors", 0);

session_start();

// Includes
require_once "../../include/config.php";

$perso_id=$_SESSION['login_id'];
$CSRFToken=filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING);
$tableId=filter_input(INPUT_POST, "tableId", FILTER_SANITIZE_NUMBER_INT);
$hiddenTables=filter_input(INPUT_POST, "hiddenTables", FILTER_SANITIZE_STRING);

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->delete("hidden_tables", array("perso_id"=>$perso_id,"tableau"=>$tableId));

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->insert("hidden_tables", array("perso_id"=>$perso_id,"tableau"=>$tableId,"hidden_tables"=>$hiddenTables));
echo json_encode("");
