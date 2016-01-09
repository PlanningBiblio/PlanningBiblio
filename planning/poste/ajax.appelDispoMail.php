<?php
/**
Planning Biblio, Version 2.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/ajax.appelDispoMail.php
Création : 22 décembre 2015
Dernière modification : 8 janvier 2016
@author : Jérôme Combes <jerome@planningbiblio.fr>

Description :
Envoi un mail aux agents disponibles pour l'occupation d'un poste vacant.
Lors de la validation du formulaire "Appel à disponibilité"
*/

ini_set("display_errors",0);

session_start();

// Includes
require_once "../../include/config.php";
require_once "../../include/function.php";

$site=filter_input(INPUT_POST,"site",FILTER_SANITIZE_STRING);
$poste=filter_input(INPUT_POST,"poste",FILTER_SANITIZE_STRING);
$date=filter_input(INPUT_POST,"date",FILTER_SANITIZE_STRING);
$debut=filter_input(INPUT_POST,"debut",FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_POST,"fin",FILTER_SANITIZE_STRING);
$agents=filter_input(INPUT_POST,"agents",FILTER_SANITIZE_STRING);
$sujet=filter_input(INPUT_POST,"sujet",FILTER_SANITIZE_STRING);
$message=filter_input(INPUT_POST,"message",FILTER_SANITIZE_STRING);

$agents=html_entity_decode($agents,ENT_QUOTES|ENT_IGNORE,"utf-8");
$agents=json_decode($agents,true);

$message=str_replace(array("\n","\r"),"<br/>",$message);

if(!is_array($agents)){
  return;
}

$destinataires=array();
foreach($agents as $elem){
  $destinataires[]=$elem['mail'];
}

$m=new sendmail();
$m->subject=$sujet;
$m->message=$message;
$m->to=$destinataires;
$m->send();

if($m->error){
  echo json_encode(array("error"=>$m->error));
}else{
  echo json_encode("ok");
}
?>