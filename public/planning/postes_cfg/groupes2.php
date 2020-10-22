<?php
/*
Planning Biblio, Version 2.5.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/postes_cfg/groupes2.php
Création : 18 septembre 2012
Dernière modification : 10 février 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Valide l'ajout ou la modification d'un groupe de tableaux.

Page appelée suite à la validation du formulaire "planning/postes_cfg/groupes.php"
*/

require_once "class.tableaux.php";

$post=filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$CSRFToken = $post['CSRFToken'];
unset($post['CSRFToken']);

unset($post['page']);

$t=new tableau();
$t->CSRFToken = $CSRFToken;
$t->update($post);

echo "<script type='text/JavaScript'>parent.location.href = '{$config['URL']}/framework';</script>\n";
