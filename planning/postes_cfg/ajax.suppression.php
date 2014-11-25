<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/ajax.suppression.php
Création : 4 novembre 2014
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Supprime une sélection de tableaux. Supprime les horaires, cellules grisées, lignes et l'identifiant du tableau (table pl_poste_tab).

Page appelée en arrière plan par la fonction supprime_select en cas de suppressions multiples
*/

require_once "../../include/config.php";
require_once "class.tableaux.php";

$db=new db();
$db->delete("pl_poste_horaires","numero IN ({$_GET['ids']})");
$db=new db();
$db->delete("pl_poste_cellules","numero IN ({$_GET['ids']})");
$db=new db();
$db->delete("pl_poste_lignes","numero IN ({$_GET['ids']})");
$db=new db();
$db->delete("pl_poste_tab","tableau IN ({$_GET['ids']})");
?>