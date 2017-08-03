<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/poste/ajax.notes.php
Création : 3 juin 2014
Dernière modification : 3 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Enregistre dans la base de donées les notes en bas des plannings
*/

session_start();
ini_set('display_errors',0);
include_once "../../include/config.php";
include_once "class.planning.php";

$CSRFToken=filter_input(INPUT_POST,"CSRFToken",FILTER_SANITIZE_STRING);
$date=filter_input(INPUT_POST,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));
$site=filter_input(INPUT_POST,"site",FILTER_SANITIZE_NUMBER_INT);
$text=filter_input(INPUT_POST,"text",FILTER_SANITIZE_STRING);
$text=urldecode($text);

// Sécurité : droits d'accès à la page
if($config['Multisites-nombre']>1){
  $required1=300+$site;		// Droits de modifier les plannings du sites N° $site
  $required2=800+$site;		// Droits de modifier les commentaires sites N° $site
}else{
  $required1=12;		// Droits de modifier les plannings en monosite
  $required2=801;		// Droits de modifier les commentaires en monosite
}

if(!in_array($required1,$_SESSION['droits']) and !in_array($required2,$_SESSION['droits'])){
  echo json_encode(array("error"=>"Vous n'avez pas le droit de modifier les commentaires"));
  exit;
}
$p=new planning();
$p->date=$date;
$p->site=$site;
$p->notes=$text;
$p->CSRFToken = $CSRFToken;
$p->updateNotes();

$p->getNotes();
$notes=$p->notes;
$validation=$p->validation;

echo json_encode(array("notes"=>$notes, "validation"=>$validation));
?>