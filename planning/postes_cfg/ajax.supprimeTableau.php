<?php
/*
Planning Biblio, Version 1.9.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/ajax.supprimeTableau.php
Création : 4 février 2015
Dernière modification : 4 février 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Supprime complétement un tableau. Supprime les horaires, cellules grisées, lignes et l'identifiant du tableau (table pl_poste_tab).
Page appelée par la fonction supprimeTableau (planning/postes_cfg/js/tableaux.js) 
lors du clique sur les croix rouges dans la liste des tableaux (planning/postes_cfg/index.php)
*/

require_once "../../include/config.php";
require_once "class.tableaux.php";

$t=new tableau();
$t->number=$_POST['tableau'];
$t->deleteTab();
echo json_encode(null);
?>