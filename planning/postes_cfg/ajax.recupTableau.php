<?php
/**
Planning Biblio, Version 2.3.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/postes_cfg/ajax.recupTableau.php
Création : 20 février 2016
Dernière modification : 28 mai 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Récupère un tableau supprimé
Appelé en Ajax lors de la modification du menu déroulant "Récupération d'un tableau", page index.php
*/

session_start();

include "../../include/config.php";
include "class.tableaux.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);

// Récupération des lignes du tableaux (postes) et des activités associées à ces lignes
$postes=array();

$db=new db();
$db->selectInnerJoin(array("pl_poste_lignes","numero"), array("pl_poste_tab","tableau"), array(array("name"=>"poste", "as"=>"poste")), array(), array(), array("tableau"=>$id));
if($db->result){
  foreach($db->result as $elem){
    $postes[]=$elem['poste'];
  }
}

if(!empty($postes)){

  // Récupération des postes
  $postes=implode(",",$postes);
  $db=new db();
  $db->update2("postes",array("supprime"=>null),array("id"=>"IN $postes"));

  // Récupération des activités
  $activites=array();
  
  $db=new db();
  $db->select2("postes","activites",array("id"=>"IN $postes"));
  
  if($db->result){
    foreach($db->result as $elem){
      $tmp=unserialize($elem['activites']);
      foreach($tmp as $e){
	if(!in_array($e,$activites)){
	  $activites[]=$e;
	}
      }
    }
  }
  
  if(!empty($activites)){
    $activites=implode(",",$activites);
    $db=new db();
    $db->update2("activites",array("supprime"=>null),array("id"=>"IN $activites"));
  }
}

// Recupération du tableau
$db=new db();
$db->update2("pl_poste_tab",array("supprime"=>null),array("tableau"=>$id));

echo json_encode("OK");
?>