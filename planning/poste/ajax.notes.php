<?php
/*
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/ajax.notes.php
Création : 3 juin 2014
Dernière modification : 2 septembre 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Enregistre dans la base de donées les notes en bas des plannings
*/

ini_set('display_errors',0);
include_once "../../include/config.php";
include_once "class.planning.php";

$date=filter_input(INPUT_GET,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));
$site=filter_input(INPUT_GET,"site",FILTER_SANITIZE_NUMBER_INT);
$text=filter_input(INPUT_GET,"text",FILTER_SANITIZE_STRING);
$text=urldecode($text);

$p=new planning();
$p->date=$date;
$p->site=$site;
$p->notes=$text;
$p->updateNotes();

$p->getNotes();
$notes=$p->notes;
$notesTextarea=$p->notesTextarea;

echo json_encode(array("notes"=>$notes, "notesTextarea"=>$notesTextarea));
?>