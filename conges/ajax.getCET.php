<?php
/*
Planning Biblio, Plugin Congés Version 1.4.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.getCET.php
Création : 10 mars 2014
Dernière modification : 10 mars 2014
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Recupére les informations d'une demande de CET
Utilisé pour la modification d'une demande de CET, formulaire de la page conges/cet.php
*/

$version='test';
include "../../include/config.php";
include "class.conges.php";

$c=new conges();
$c->id=$_GET['id'];
$c->getCET();
echo json_encode($c->elements[0]);
