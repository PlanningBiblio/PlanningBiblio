<?php
/**
Planning Biblio, Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : postes/ajax.delete.php
Création : 4 novembre 2014
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime un poste
Page appelée par la fonction JS supprime() lors du clique sur l'icône suppression de la page postes/index.php
*/

session_start();

require_once "../include/config.php";
require_once "class.postes.php";

$CSRFToken = filter_input(INPUT_GET, 'CSRFToken', FILTER_SANITIZE_STRING);
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$p=new postes();
$p->CSRFToken=$CSRFToken;
$p->id=$id;
$p->delete();
?>