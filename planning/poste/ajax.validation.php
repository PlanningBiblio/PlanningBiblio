<?php
/**
Planning Biblio, Version 2.7.01
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/poste/ajax.validation.php
Création : 23 février 2015
Dernière modification : 30 septembre 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet de verrouiller (et de déverrouiller) le planning du jour courant pour en interdire la modification et le rendre 
visible aux agents n'ayant pas le droit de modifier les plannings

Page appelée en ajax lors du click sur les cadenas de la page planning/poste/index.php 
(événements $("#icon-lock").click et $("#icon-unlock").click, page planning/poste/js/planning.js)
*/

session_start();
require_once "../../include/config.php";
require_once "class.planning.php";

// Initialisation des variables
$date=filter_input(INPUT_GET,'date',FILTER_SANITIZE_STRING);
$CSRFToken = filter_input(INPUT_GET, 'CSRFToken', FILTER_SANITIZE_STRING);
$site=filter_input(INPUT_GET,'site',FILTER_SANITIZE_NUMBER_INT);
$verrou=filter_input(INPUT_GET,'verrou',FILTER_SANITIZE_NUMBER_INT);
// 
$d=new datePl($date);
$d1=$d->dates[0];
$perso_id=$_SESSION['login_id'];

// Sécurité
// Refuser l'accès aux agents n'ayant pas les droits de modifier le planning
$droit=($config['Multisites-nombre']>1)?(300+$site):12;
$db=new db();
$db->select2("personnel","droits",array("id"=>$perso_id));
$droits_agent=json_decode(html_entity_decode($db->result[0]['droits'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);
if(!in_array((300+$site),$droits_agent) and !in_array((1000+$site),$droits_agent)){
  echo json_encode(array("Accès refusé","error"));
  exit;
}

// Date de validation
$validation=date("Y-m-d H:i:s");

$db=new db();
$db->select2("pl_poste_verrou","*",array("date"=>$date, "site"=>$site));
if($db->result){
  if($verrou==1){
    $set=array("verrou2"=>"1", "validation2"=>$validation, "perso2"=>$perso_id);
    $where=array("date"=>$date, "site"=>$site);
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("pl_poste_verrou",$set,$where);
  }else{
    $set=array("verrou2"=>"0", "perso2"=>$perso_id);
    $where=array("date"=>$date, "site"=>$site);
    $db=new db();
    $db->CSRFToken = $CSRFToken;
    $db->update("pl_poste_verrou",$set,$where);
  }
}else{
  $insert=array("date"=>$date, "verrou2"=>$verrou, "validation2"=>$validation, "perso2"=>$perso_id, "site"=>$site);
  $db=new db();
  $db->CSRFToken = $CSRFToken;
  $db->insert("pl_poste_verrou",$insert);
}

if(!$db->error and $verrou==1){
  // Affichage du message "..validé avec succès"
  $result=array("Le planning a &eacute;t&eacute; valid&eacute; avec succ&egrave;s","highlight");
  // Affichage du Div "Validation : ..."
  $result[]="<u>Validation</u><br/>".nom($perso_id)." ".date("d/m/Y H:i");
  // Mise à jour de #planning-data data-validation pour éviter un refresh_poste inutile
  $result[]=$validation;
  echo json_encode($result);
  exit;
}elseif(!$db->error and $verrou==0){
  echo json_encode(array("Le planning a &eacute;t&eacute; d&eacute;verrouill&eacute; avec succ&egrave;s","highlight"));
  exit;
}	
?>