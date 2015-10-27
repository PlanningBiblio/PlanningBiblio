<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/ajax.piecesJustif.php
Création : 5 novembre 2014
Dernière modification : 5 novembre 2014
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Enregistre les modifications apportées sur les cases à cacher "Pièces justificatives" dans la liste des absences (voir.php)
Appelé lors du clic sur les cases à cocher, évènement $(".absences-pj input[type=checkbox]").click(), fichier voir.js
*/

require_once "../include/config.php";
require_once "class.absences.php";

$a=new absences();
$a->piecesJustif($_GET['id'],$_GET['pj'],$_GET['checked']);
?>