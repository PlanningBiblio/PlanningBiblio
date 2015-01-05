<?php
/*
Planning Biblio, Version 1.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/ajax.notes.php
Création : 3 juin 2014
Dernière modification : 6 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Enregistre dans la base de donées les notes en bas des plannings
*/

ini_set('display_errors',0);
include_once "../../include/config.php";
include_once "class.planning.php";

$p=new planning();
$p->date=$_GET['date'];
$p->site=$_GET['site'];
$p->notes=$_GET['text'];
$p->updateNotes();
?>
