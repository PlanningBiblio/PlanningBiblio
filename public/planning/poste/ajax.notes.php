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

require_once(__DIR__ . '/../../../init/init_ajax.php');
require_once(__DIR__ . '/../../../legacy/Class/class.planning.php');

$CSRFToken = $request->get('CSRFToken');
$date = $request->get('date');
$site = $request->get('site');
$text = $request->get('text');

$date = filter_var($date, FILTER_CALLBACK, array('options' => 'sanitize_dateSQL'));
$site = filter_var($site, FILTER_SANITIZE_NUMBER_INT);
$text = urldecode($text);
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
