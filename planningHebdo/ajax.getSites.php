<?php
/*
Planning Biblio, Version 2.0
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : planningHebdo/ajax.getSites.php
Création : 5 juin 2015
Dernière modification : 5 juin 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

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
  foreach($sites as $elem){
    $options[]=array($elem,$config["Multisites-site".$elem]);
  }
}
echo json_encode($options);
?>