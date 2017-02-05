<?php
/**
Planning Biblio, Version 2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/postes_cfg/ajax.suppression.php
Création : 4 novembre 2014
Dernière modification : 20 février 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime une sélection de tableaux. Supprime les horaires, cellules grisées, lignes et l'identifiant du tableau (table pl_poste_tab).

Page appelée en arrière plan par la fonction supprime_select en cas de suppressions multiples
*/

require_once "../../include/config.php";
require_once "class.tableaux.php";

$ids=filter_input(INPUT_GET,"ids",FILTER_SANITIZE_STRING);

$today=date("Y-m-d H:i:s");
$set=array("supprime"=>$today);
$where=array("tableau"=>"IN $ids");

$db=new db();
$db->query("UPDATE `{$dbprefix}pl_poste_tab_grp` SET `supprime`='$today' WHERE `Lundi` IN ($ids) OR `Mardi` IN ($ids) OR `Mercredi` IN ($ids) OR `Jeudi` IN ($ids) OR `Vendredi` IN ($ids) OR `Samedi` IN ($ids) OR `Dimanche` IN ($ids);");

$db=new db();
$db->update2("pl_poste_tab",$set,$where);
?>