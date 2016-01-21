<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/postes_cfg/groupes2.php
Création : 18 septembre 2012
Dernière modification : 13 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Valide l'ajout ou la modification d'un groupe de tableaux.

Page appelée suite à la validation du formulaire "planning/postes_cfg/groupes.php"
*/

require_once "class.tableaux.php";

$post=filter_input_array(INPUT_POST,FILTER_SANITIZE_STRING);
unset ($post['page']);

$t=new tableau();
$t->update($post);

echo "<script type='text/JavaScript'>location.href='index.php?page=planning/postes_cfg/index.php';</script>\n";
?>