<?php
/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/poste/ajax.notes.php
Création : 3 juin 2014
Dernière modification : 30 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Enregistre dans la base de donées les notes en bas des plannings
*/

session_start();
ini_set('display_errors', 0);
include_once "../../include/config.php";
include_once "class.planning.php";

$CSRFToken=filter_input(INPUT_POST, "CSRFToken", FILTER_SANITIZE_STRING);
$date=filter_input(INPUT_POST, "date", FILTER_CALLBACK, array("options"=>"sanitize_dateSQL"));
$site=filter_input(INPUT_POST, "site", FILTER_SANITIZE_NUMBER_INT);
$text=filter_input(INPUT_POST, "text", FILTER_SANITIZE_STRING);
$text=urldecode($text);
$text = sanitize_html($text);

// Sécurité : droits d'accès à la page
$required1 = 300 + $site; // Droits de modifier les plannings du sites N° $site
$required2 = 800 + $site; // Droits de modifier les commentaures
$required3 = 1000 + $site; // Droits de modifier les plannings

$droits = $_SESSION['droits'];

if (!in_array($required1, $droits) and !in_array($required2, $droits) and !in_array($required3, $droits)) {
    echo json_encode(array("error"=>"Vous n'avez pas le droit de modifier les commentaires"));
    exit;
}
$p=new planning();
$p->date=$date;
$p->site=$site;
$p->notes=$text;
$p->CSRFToken = $CSRFToken;
$p->updateNotes();

$p->getNotes();
$notes=$p->notes;
$validation=$p->validation;

echo json_encode(array("notes"=>$notes, "validation"=>$validation));
