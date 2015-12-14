<?php
/**
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/ajax.getHiddenTables.php
Création : 14 décembre 2015
Dernière modification : 14 décembre 2015
@author : Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de récupérer les préférences sur les tableaux cachés

Cette page est appelée en Ajax lors de l'affichage du planning (planning/poste/index.php)
*/

ini_set("display_errors",0);

session_start();

// Includes
require_once "../../include/config.php";

$perso_id=$_SESSION['login_id'];
$tableId=filter_input(INPUT_POST,"tableId",FILTER_SANITIZE_NUMBER_INT);

$db=new db();
$db->select2("hiddenTables","*",array("perso_id"=>$perso_id,"tableau"=>$tableId));
if($db->result){
  echo json_encode(html_entity_decode($db->result[0]["hiddenTables"],ENT_QUOTES|ENT_IGNORE,"utf-8"));
}else{
  echo json_encode(null);
}
?>