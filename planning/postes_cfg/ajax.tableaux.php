<?php
/*
Planning Biblio, Version 1.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/ajax.tableaux.php
Création : 21 janvier 2014
Dernière modification : 3 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Met à jour le nombre de tableaux pour le modèle sélectionné
Appelé en Ajax via la fonction tableauxNombre à partir de la page tableaux.php (dans modif.php)
*/

session_start();
ini_set('display_errors',0);
error_reporting(0);

include "../../include/config.php";
include "class.tableaux.php";

$t=new tableau();
$t->id=$_GET['id'];
$t->setNumbers($_GET['nombre']);
?>