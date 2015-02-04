<?php
/*
Planning Biblio, Version 1.9.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/ajax.supprimeLignes.php
Création : 10 septembre 2012
Dernière modification : 4 février 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Supprime une ligne de séparation d'un tableau. Appelée par la fonction JavaScript "supprimeLigne" lors du click sur une 
icône de suppression du tableau "Lignes de séparation".

Page appelée en arrière plan par la fonction JavaScript "supprimeLigne"
*/

require_once "../../include/config.php";
require_once "class.tableaux.php";

$t=new tableau();
$t->id=$_POST['id'];
$t->deleteLine();
echo json_encode(null);
?>