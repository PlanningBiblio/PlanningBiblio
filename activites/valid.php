<?php
/**
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : activites/valid.php
Création : mai 2011
Dernière modification : 4 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Valide l'ajout ou la modification d'une activité

Page appelée par la page index.php
*/

require_once "class.activites.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$action=filter_input(INPUT_GET,"action",FILTER_SANITIZE_STRING);
$nom=trim(filter_input(INPUT_GET,"nom",FILTER_SANITIZE_STRING));

if(!$nom or !$action){
  $msg=urlencode("Le nom ne peut pas être vide");
  $msgType="error";
  echo "<script type='text/JavaScript'>document.location.href='index.php?page=activites/index.php&msg=$msg&msgType=$msgType';</script>\n";
  exit;
}

switch($action){
  case "ajout" :	
    $db=new db();
    $db->insert2("activites",array("nom"=>$nom));
    if($db->error){
      $msg=urlencode("L'activité n'a pas pu être ajoutée");
      $msgType="error";
    }else{
      $msg=urlencode("L'activité a été ajoutée avec succès");
      $msgType="success";
    }
    break;
  
  case "modif" :
    $db=new db();
    $db->update2("activites",array("nom"=>$nom),array("id"=>$id));
    if($db->error){
      $msg=urlencode("L'activité n'a pas pu être modifiée");
      $msgType="error";
    }else{
      $msg=urlencode("L'activité a été modifiée avec succès");
      $msgType="success";
    }
    break;
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=activites/index.php&msg=$msg&msgType=$msgType';</script>\n";
?>