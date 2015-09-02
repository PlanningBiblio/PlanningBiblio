<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/ajax.tableaux.php
Création : 21 janvier 2014
Dernière modification : 9 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Met à jour le nombre de tableaux pour le modèle sélectionné
Appelé en Ajax via la fonction tableauxNombre à partir de la page tableaux.php (dans modif.php)
*/

ini_set('display_errors',0);

session_start();

include "../../include/config.php";
include "class.tableaux.php";

$t=new tableau();
$t->id=$_GET['id'];
$t->setNumbers($_GET['nombre']);
?>