<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/ajax.supprimeGroupe.php
Création : 4 février 2015
Dernière modification : 13 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Supprime un groupe de tableau.
Page appelée par la fonction supprimeGroupe (planning/postes_cfg/js/tableaux.js) 
lors du clique sur les croix rouges dans la liste des groupe (planning/postes_cfg/index.php)
*/

require_once "../../include/config.php";
require_once "class.tableaux.php";

$id=filter_input(INPUT_POST,"id",FILTER_SANITIZE_NUMBER_INT);

$t=new tableau();
$t->id=$id;
$t->deleteGroup();
echo json_encode(null);
?>