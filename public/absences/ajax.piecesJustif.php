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

require_once(__DIR__ . '/../../init/init_ajax.php');
require_once(__DIR__ . '/../../legacy/Class/class.absences.php');

$id = $request->get('id');
$pj = $request->get('pj');
$checked = $request->get('checked');
$CSRFToken = $request->get('CSRFToken');

$id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);

$a=new absences();
$a->CSRFToken = $CSRFToken;
$a->piecesJustif($id, $pj, $checked);
