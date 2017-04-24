<?php
/**
Planning Biblio, Version 2.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/postes_cfg/ajax.suppression.php
Création : 4 novembre 2014
Dernière modification : 21 avril 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime une sélection de tableaux. Supprime les horaires, cellules grisées, lignes et l'identifiant du tableau (table pl_poste_tab).

Page appelée en arrière plan par la fonction supprime_select en cas de suppressions multiples
*/

session_start();

require_once "../../include/config.php";
require_once "class.tableaux.php";

$ids=filter_input(INPUT_GET,"ids",FILTER_SANITIZE_STRING);
$CSRFToken = filter_input(INPUT_GET,'CSRFToken',FILTER_SANITIZE_STRING);

$today=date("Y-m-d H:i:s");
$set=array("supprime"=>$today);
$where=array("tableau"=>"IN $ids");

$db=new db();
$db->query("UPDATE `{$dbprefix}pl_poste_tab_grp` SET `supprime`='$today' WHERE `lundi` IN ($ids) OR `mardi` IN ($ids) OR `mercredi` IN ($ids) OR `jeudi` IN ($ids) OR `vendredi` IN ($ids) OR `samedi` IN ($ids) OR `dimanche` IN ($ids);");

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->update2("pl_poste_tab",$set,$where);
?>