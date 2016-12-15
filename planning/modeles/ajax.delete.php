<?php
/**
Planning Biblio, Version 2.4.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/modeles/ajax.delete.php
Création : 4 novembre 2014
Dernière modification : 3 octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime un modèle
Page appelée par la fonction JS supprime() lors du clique sur l'icône suppression de la page planning/modeles/index.php
*/

require_once "../../include/config.php";
require_once "class.modeles.php";

$id=filter_input(INPUT_GET,'id',FILTER_SANITIZE_STRING);

$m=new modeles();
$m->nom=$id;
$m->delete();
?>