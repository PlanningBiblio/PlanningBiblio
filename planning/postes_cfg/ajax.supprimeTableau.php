<?php
/**
Planning Biblio, Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/postes_cfg/ajax.supprimeTableau.php
Création : 4 février 2015
Dernière modification : 10 fvévrier 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Supprime complétement un tableau. Supprime les horaires, cellules grisées, lignes et l'identifiant du tableau (table pl_poste_tab).
Page appelée par la fonction supprimeTableau (planning/postes_cfg/js/tableaux.js) 
lors du clique sur les croix rouges dans la liste des tableaux (planning/postes_cfg/index.php)
*/

session_start();

require_once "../../include/config.php";
require_once "class.tableaux.php";

$CSRFToken =filter_input(INPUT_POST,"CSRFToken",FILTER_SANITIZE_STRING);
$tableau=filter_input(INPUT_POST,"tableau",FILTER_SANITIZE_NUMBER_INT);

$t=new tableau();
$t->number=$tableau;
$t->CSRFToken = $CSRFToken;
$t->deleteTab();
echo json_encode(null);
?>