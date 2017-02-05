<?php
/**
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : activites/ajax.delete.php
Création : 4 novembre 2014
Dernière modification : 4 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime une activité
Page appelée par la fonction JS supprime() lors du clique sur l'icône suppression de la page activites/index.php
*/

require_once "../include/config.php";
require_once "class.activites.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);

$a=new activites();
$a->id=$id;
$a->delete();
?>