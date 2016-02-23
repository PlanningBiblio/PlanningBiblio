<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/postes_cfg/siteUpdate.php
Création : 7 novembre 2013
Dernière modification : 7 novembre 2013
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affecte un numéro de site à un tableau.
Appelé en ajax par la fonction JS tabSiteUpdate à partir du premier onglet de la page modif.php
*/

session_start();

// Includes
include "../../include/config.php";

// Update
$db=new db();
$db->update("pl_poste_tab","site='{$_GET['site']}'","tableau='{$_GET['numero']}'");
?>