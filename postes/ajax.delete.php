<?php
/**
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : postes/ajax.delete.php
Création : 4 novembre 2014
Dernière modification : 4 novembre 2014
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime un poste
Page appelée par la fonction JS supprime() lors du clique sur l'icône suppression de la page postes/index.php
*/

require_once "../include/config.php";
require_once "class.postes.php";

$p=new postes();
$p->id=$_GET['id'];
$p->delete();
?>