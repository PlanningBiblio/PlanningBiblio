<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/modeles/ajax.delete.php
Création : 4 novembre 2014
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Supprime un modèle
Page appelée par la fonction JS supprime() lors du clique sur l'icône suppression de la page planning/modeles/index.php
*/

require_once "../../include/config.php";
require_once "class.modeles.php";

$m=new modeles();
$m->nom=$_GET['id'];
$m->delete();
?>