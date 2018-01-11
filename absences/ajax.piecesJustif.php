<?php
/**
Planning Biblio, Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : absences/ajax.piecesJustif.php
Création : 5 novembre 2014
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Enregistre les modifications apportées sur les cases à cacher "Pièces justificatives" dans la liste des absences (voir.php)
Appelé lors du clic sur les cases à cocher, évènement $(".absences-pj input[type=checkbox]").click(), fichier voir.js
*/
session_start();

require_once "../include/config.php";
require_once "class.absences.php";

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$pj = filter_input(INPUT_GET, 'pj', FILTER_SANITIZE_STRING);
$checked = filter_input(INPUT_GET, 'checked', FILTER_SANITIZE_STRING);
$CSRFToken = filter_input(INPUT_GET, 'CSRFToken', FILTER_SANITIZE_STRING);

$a=new absences();
$a->CSRFToken = $CSRFToken;
$a->piecesJustif($id,$pj,$checked);
?>