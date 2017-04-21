<?php
/**
Planning Biblio, Version 2.6.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planningHebdo/ajax.delete.php
Création : 17 septembre 2013
Dernière modification : 21 avril 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant la suppression d'un planning de présence en arrière plan.
Appelé par la fonction JS plHebdoSupprime (planningHebdo/js/script.planningHebdo.js)
*/

require_once "../include/config.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$db=new db();
$db->delete("planning_hebdo","id=$id");
$db=new db();
$db->update("planning_hebdo","remplace='0'","remplace='$id'");
?>