<?php
/**
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/ajax.appelDispoMsg.php
Création : 21 décembre 2015
Dernière modification : 21 décembre 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Récupère le message par défaut pour l'appel à disponibilité
Script appelé depuis la function JS appelDispo (planning/poste/js/planning.js)
lors du clic sur le lien "Appel à disponibilité" dans le menu permettant de placer les agents
*/

ini_set("display_errors", 0);

session_start();

// TEST MT43669
error_log('MT43669 01 planning/poste/ajax.appelDispoMsg.php:24 loginId : ' . $_SESSION['login_id']);

// Includes
require_once "../../include/config.php";

$tab=array(null,null);

$db=new db();
$db->select2("config", "valeur", array("nom"=>"Planning-AppelDispoSujet"));
if ($db->result) {
    $tab[0]=html_entity_decode($db->result[0]["valeur"], ENT_QUOTES|ENT_IGNORE, "utf-8");
}
$db=new db();
$db->select2("config", "valeur", array("nom"=>"Planning-AppelDispoMessage"));
if ($db->result) {
    $tab[1]=html_entity_decode($db->result[0]["valeur"], ENT_QUOTES|ENT_IGNORE, "utf-8");
}

// TEST MT43669
error_log('MT43669 02 planning/poste/ajax.appelDispoMsg.php:43 loginId : ' . $_SESSION['login_id']);

echo json_encode($tab);
