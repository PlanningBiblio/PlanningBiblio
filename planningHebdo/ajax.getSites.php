<?php
/**
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planningHebdo/ajax.getSites.php
Création : 5 juin 2015
Dernière modification : 4 septembre 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Fichier permettant la récupération des sites d'un agent en arrière plan.
Appelé par l'évenement $("#perso_id").change() dans la page planningHebdo/modif.php (planningHebdo/js/script.planningHebdo.js)
*/

require_once "../include/config.php";

$options=array();

$id=filter_input(INPUT_POST,"id",FILTER_SANITIZE_NUMBER_INT);
$db=new db();
$db->select2("personnel","sites",array("id"=>$id));
if($db->result){
  $sites=unserialize($db->result[0]['sites']);
  if(is_array($sites)){
    foreach($sites as $elem){
      $options[]=array($elem,$config["Multisites-site".$elem]);
    }
  }
}
echo json_encode($options);
?>