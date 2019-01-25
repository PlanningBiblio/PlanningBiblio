<?php
/**
Planning Biblio, Plugin Conges Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2013-2018 Jérôme Combes

Fichier : conges/menudiv.php
Création : 26 septembre 2013
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier appelé lors de la suppression d'un agent par le fichier personnel/class.personnel.php, fonction personnel::delete()
Permet de supprimer les informations sur les congés des agents supprimés définitivement
La variables $liste et la liste des ids des agents à supprimer, séparés par des virgules
*/

require_once "class.conges.php";

// recherche des personnes à exclure (congés)
$c=new conges();
$c->CSRFToken = $this->CSRFToken;   // $->this = personnel::delete
$c->suppression_agents($liste);
