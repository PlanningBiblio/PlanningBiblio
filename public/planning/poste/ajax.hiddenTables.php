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

Cette page est appelée par la function JavaScript "afficheTableauxDiv" utilisé dans la page /index
*/

ini_set("display_errors", 0);

require_once(__DIR__ . '/../../../init/init_ajax.php');

$perso_id = $_SESSION['login_id'];
$CSRFToken = $request->get('CSRFToken');
$hiddenTables = $request->get('hiddenTables');
$tableId = $request->get('tableId');

$tableId = filter_var($tableId, FILTER_SANITIZE_NUMBER_INT);

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->delete("hidden_tables", array("perso_id"=>$perso_id,"tableau"=>$tableId));

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->insert("hidden_tables", array("perso_id"=>$perso_id,"tableau"=>$tableId,"hidden_tables"=>$hiddenTables));
echo json_encode("");
