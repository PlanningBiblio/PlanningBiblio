<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/postes_cfg/ajax.supprimeLignes.php
Création : 10 septembre 2012
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime une ligne de séparation d'un tableau. Appelée par la fonction JavaScript "supprimeLigne" lors du click sur une 
icône de suppression du tableau "Lignes de séparation".

Page appelée en arrière plan par la fonction JavaScript "supprimeLigne"
*/

session_start();

require_once "../../include/config.php";
require_once "class.tableaux.php";

$CSRFToken = filter_input(INPUT_POST,"CSRFToken",FILTER_SANITIZE_STRING);
$id=filter_input(INPUT_POST,"id",FILTER_SANITIZE_NUMBER_INT);

$t=new tableau();
$t->id=$id;
$t->CSRFToken = $CSRFToken;
$t->deleteLine();
echo json_encode(null);
?>