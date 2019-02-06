<?php
/**
Planning Biblio, Plugin Congés Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/ajax.supprime.php
Création : 9 janvier 2014
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime un congé
Appelé en Ajax via la fonction supprimeConges à partir de la page modif.php
*/

include(__DIR__.'/../init_ajax.php');
include "class.conges.php";

$id=filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
$CSRFToken=filter_input(INPUT_GET, "CSRFToken", FILTER_SANITIZE_STRING);

$c=new conges();
$c->id=$id;
$c->CSRFToken=$CSRFToken;
$c->delete();
