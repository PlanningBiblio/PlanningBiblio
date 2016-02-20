<?php
/**
Planning Biblio, Version 2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/postes_cfg/ajax.recupTableau.php
Création : 20 février 2016
Dernière modification : 20 février 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Récupère un tableau supprimé
Appelé en Ajax lors de la modification du menu déroulant "Récupération d'un tableau", page index.php
*/

session_start();

include "../../include/config.php";
include "class.tableaux.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);

$db=new db();
$db->update2("pl_poste_tab",array("supprime"=>null),array("tableau"=>$id));

echo json_encode("OK");
?>