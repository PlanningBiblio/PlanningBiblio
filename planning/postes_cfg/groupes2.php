<?php
/*
Planning Biblio, Version 1.7.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/groupes2.php
Création : 18 septembre 2012
Dernière modification : 16 janvier 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Valide l'ajout ou la modification d'un groupe de tableaux.

Page appelée suite à la validation du formulaire "planning/postes_cfg/groupes.php"
*/

require_once "class.tableaux.php";

unset ($_POST['page']);

$t=new tableau();
$t->update($_POST);

echo "<script type='text/JavaScript'>location.href='index.php?page=planning/postes_cfg/index.php';</script>\n";
?>