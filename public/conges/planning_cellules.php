<?php
/**
Planning Biblio, Plugin Conges Version 2.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/planning_cellules.php
Création : 30 janvier 2014
Dernière modification : 16 mars 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier intégré au planning (planning/poste/index.php)
Créé un tableau recensant les informations sur les congés du jour
Permet de barrer les agents en congés dans les cellules
*/

include_once "conges/class.conges.php";

$c=new conges();
$c->debut=$date." 00:00:00";
$c->fin=$date." 23:59:59";
$c->valide=false;
$c->information = false;
$c->agents_supprimes = array(0,1,2);    // required for history
$c->fetch();
$conges=$c->elements;
global $conges;
