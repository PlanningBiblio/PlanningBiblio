<?php
/**
Planning Biblio, Version 2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/postes_cfg/ajax.infos.php
Création : 20 février 2016
Dernière modification : 20 février 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Met à jour les informations généralesdu tableau sélectionné
Appelé en Ajax via la fonction tableauxInfos à partir de la page infos.php (dans modif.php)
*/

ini_set('display_errors',0);

session_start();

include "../../include/config.php";
include "class.tableaux.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$nombre=filter_input(INPUT_GET,"nombre",FILTER_SANITIZE_NUMBER_INT);
$nom=filter_input(INPUT_GET,"nom",FILTER_SANITIZE_STRING);
$site=filter_input(INPUT_GET,"site",FILTER_SANITIZE_NUMBER_INT);

$t=new tableau();
$t->id=$id;
$t->setNumbers($nombre);

$db=new db();
$db->update2("pl_poste_tab",array("nom"=>trim($nom)),array("tableau"=>$id));

if($site){
  $db=new db();
  $db->update("pl_poste_tab","site='$site'","tableau='$id'");
}

echo json_encode("OK");
?>