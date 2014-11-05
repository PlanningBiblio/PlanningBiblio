<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : activites/ajax.delete.php
Création : 4 novembre 2014
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Supprime une activité
Page appelée par la fonction JS supprime() lors du clique sur l'icône suppression de la page activites/index.php
*/

require_once "../include/config.php";
require_once "class.activites.php";

$a=new activites();
$a->id=$_GET['id'];
$a->delete();
?>