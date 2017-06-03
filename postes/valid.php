<?php
/**
Planning Biblio, Version 2.6.91
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : postes/valid.php
Création : mai 2011
Dernière modification : 1er juin 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Valide l'ajout ou la modification d'un poste.

Page appelée par le fichier index.php.
*/

require_once "class.postes.php";

$CSRFToken = filter_input(INPUT_GET,"CSRFToken",FILTER_SANITIZE_STRING);
$nom=trim(filter_input(INPUT_GET,"nom",FILTER_SANITIZE_STRING));

if(!$nom){
  $msg=urlencode("Le nom est obligatoire");
  echo "<script type='text/JavaScript'>document.location.href='index.php?page=postes/modif.php&msg={$msg}&msgType=error';</script>\n";
  exit;
}

if($nom){
  $get=filter_input_array(INPUT_GET,FILTER_SANITIZE_STRING);
  $activites = array_key_exists("activites",$get) ? json_encode($get['activites']) : json_encode(array());
  $categories = array_key_exists("categories",$get) ? json_encode($get['categories']) : json_encode(array());

  $id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
  $site=filter_input(INPUT_GET,"site",FILTER_SANITIZE_NUMBER_INT);
  $bloquant=filter_input(INPUT_GET,"bloquant",FILTER_CALLBACK,array("options"=>"sanitize_on01"));
  $statistiques=filter_input(INPUT_GET,"statistiques",FILTER_CALLBACK,array("options"=>"sanitize_on01"));

  $action=$get["action"];
  $etage = htmlentities($get["etage"], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
  $groupe = htmlentities($get["groupe"], ENT_QUOTES|ENT_IGNORE, 'UTF-8', false);
  $obligatoire=$get["obligatoire"];
  $site=$site?$site:1;

  $data=array("nom"=>$nom,"obligatoire"=>$obligatoire,"etage"=>$etage,"groupe"=>$groupe,"activites"=>$activites,
  "statistiques"=>$statistiques,"bloquant"=>$bloquant,"site"=>$site,"categories"=>$categories);

  switch($action){
    case "ajout" :	
      $db=new db();
      $db->insert2("postes",$data);
      if($db->error){
	$msgType="error";
	$msg=urlencode("Une erreur est survenue lors de l'ajout du poste");
      }else{
	$msgType="success";
	$msg=urlencode("Le poste a été ajouté avec succés");
      }
      break;

    case "modif" :
      $db=new db();
      $db->CSRFToken = $CSRFToken;
      $db->update2("postes",$data,array("id"=>$id));
      if($db->error){
	$msgType="error";
	$msg=urlencode("Une erreur est survenue lors de la modification du poste");
      }else{
	$msgType="success";
	$msg=urlencode("Le poste a été modifié avec succés");
      }
      break;
  }
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=postes/index.php&msg={$msg}&msgType=$msgType';</script>\n";
?>