<?php
/**
Planning Biblio, Plugin Congés Version 1.6.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.getReliquat.php
Création : 6 mars 2014
Dernière modification : 18 mars 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Recupére le nombre d'heure de reliquat pour un agent donné
Utilisé pour l'alimentation du CET, formulaire de la page conges/cet.php
*/

include(__DIR__.'/../include/config.php');
include "class.conges.php";

$c=new conges();
$c->perso_id=$_GET['perso_id'];
$c->fetchCredit();
$reliquatHeures=array_key_exists("reliquat", $c->elements)?$c->elements['reliquat']:0;
$reliquatJours=number_format($reliquatHeures/7, 2, ",", " ");
echo json_encode(array("reliquatHeures"=>$reliquatHeures,"reliquatJours"=>$reliquatJours));
