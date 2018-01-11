<?php
/**
Planning Biblio, Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : activites/ajax.delete.php
Création : 4 novembre 2014
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime une activité
Page appelée par la fonction JS supprime() lors du clique sur l'icône suppression de la page activites/index.php
*/

session_start();

require_once "../include/config.php";
require_once "class.activites.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$CSRFToken = filter_input(INPUT_GET,"CSRFToken",FILTER_SANITIZE_STRING);

$a=new activites();
$a->id=$id;
$a->CSRFToken = $CSRFToken;
$a->delete();
?>