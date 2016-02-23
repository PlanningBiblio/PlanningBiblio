<?php
/**
Planning Biblio, Version 2.0
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planningHebdo/ajax.delete.php
Création : 17 septembre 2013
Dernière modification : 29 mai 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant la suppression d'un planning de présence en arrière plan.
Appelé par la fonction JS plHebdoSupprime (planningHebdo/js/script.planningHebdo.js)
*/

require_once "../include/config.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$db=new db();
$db->delete("planningHebdo","id=$id");
$db=new db();
$db->update("planningHebdo","remplace='0'","remplace='$id'");
?>