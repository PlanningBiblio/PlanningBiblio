<?php
/**
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/poste/ajax.hiddenTables.php
Création : 14 décembre 2015
Dernière modification : 14 décembre 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet l'enregistrement des préférences sur les tableaux cachés

Cette page est appelée par la function JavaScript "afficheTableauxDiv" utilisé par le fichier planning/poste/index.php
*/

ini_set("display_errors",0);

session_start();

// Includes
require_once "../../include/config.php";

$perso_id=$_SESSION['login_id'];
$tableId=filter_input(INPUT_POST,"tableId",FILTER_SANITIZE_NUMBER_INT);
$hiddenTables=filter_input(INPUT_POST,"hiddenTables",FILTER_SANITIZE_STRING);

$db=new db();
$db->delete2("hiddenTables",array("perso_id"=>$perso_id,"tableau"=>$tableId));

$db=new db();
$db->insert2("hiddenTables",array("perso_id"=>$perso_id,"tableau"=>$tableId,"hiddenTables"=>$hiddenTables));
echo json_encode("");
?>