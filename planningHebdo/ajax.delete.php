<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planningHebdo/ajax.delete.php
Création : 17 septembre 2013
Dernière modification : 15 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant la suppression d'un planning de présence en arrière plan.
Appelé par la fonction JS plHebdoSupprime (planningHebdo/js/script.planningHebdo.js)
*/

session_start();
require_once "../include/config.php";

$CSRFToken = filter_input(INPUT_GET, 'CSRFToken', FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);

$db=new db();
$db->CSRFToken = $CSRFToken;
$db->delete("planning_hebdo", "id=$id");
$db=new db();
$db->CSRFToken = $CSRFToken;
$db->update('planning_hebdo', array('remplace'=>'0'), array('remplace'=>$id));

echo json_encode('ok');
