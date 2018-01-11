<?php
/**
Planning Biblio, Version 2.6.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planningHebdo/ajax.getSites.php
Création : 5 juin 2015
Dernière modification : 12 mai 2017
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
  $sites = json_decode(html_entity_decode($db->result[0]['sites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
  if(is_array($sites)){
    foreach($sites as $elem){
      $options[]=array($elem,$config["Multisites-site".$elem]);
    }
  }
}
echo json_encode($options);
?>