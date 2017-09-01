<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/postes_cfg/ajax.lignes.php
Création : 3 février 2014
Dernière modification : 29 août 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Met à jour les lignes des tableaux
Appelé en Ajax via la fonction configLignes à partir de la page lignes.php (dans modif.php)
*/

ini_set('display_errors',0);

session_start();

include "../../include/config.php";
include "class.tableaux.php";

$CSRFToken = filter_input(INPUT_POST,"CSRFToken",FILTER_SANITIZE_STRING);
$tableauNumero=filter_input(INPUT_POST,"id",FILTER_SANITIZE_NUMBER_INT);

$post=array();
foreach($_POST as $key => $value){
  $key=filter_var($key,FILTER_SANITIZE_STRING);
  $post[$key]=filter_var($value,FILTER_SANITIZE_STRING);
}

// Suppression des infos concernant ce tableau dans la table pl_poste_lignes
$db=new db();
$db->CSRFToken = $CSRFToken;
$db->delete("pl_poste_lignes",array("numero"=>$tableauNumero));

// Insertion des données dans la table pl_poste_lignes
$values=array();
foreach($post as $key => $value){
  if($value and substr($key,0,6)=="select"){
    $tab=explode("_",$key);  //1: tableau ; 2 lignes
    if(substr($tab[1],-5)=="Titre"){
      $type="titre";
      $tab[1]=substr($tab[1],0,-5);
    }
    elseif(substr($tab[1],-6)=="Classe"){
      $type="classe";
      $tab[1]=substr($tab[1],0,-6);
    }
    elseif(substr($value,-5)=="Ligne"){
      $type="ligne";
      $value=substr($value,0,-5);
    }
    else{
      $type="poste";
    }
    $values[]=array(":numero"=>$tableauNumero, ":tableau"=>$tab[1], ":ligne"=>$tab[2], ":poste"=>$value, ":type"=>$type);
  }
}
if($values[0]){
  $sql="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`,`tableau`,`ligne`,`poste`,`type`) ";
  $sql.="VALUES (:numero, :tableau, :ligne, :poste, :type);";

  $db=new dbh();
  $db->CSRFToken = $CSRFToken;
  $db->prepare($sql);
  foreach($values as $elem){
    $db->execute($elem);
  }
}

// Suppression des infos concernant ce tableau dans la table pl_poste_cellules
$db=new db();
$db->CSRFToken = $CSRFToken;
$db->delete("pl_poste_cellules",array("numero"=>$tableauNumero));

// Insertion des données dans la table pl_poste_cellules
$values=array();
foreach($post as $key => $value){
  if($value and substr($key,0,8)=="checkbox"){
    $tab=explode("_",$key);  //1: tableau ; 2 lignes ; 3 colonnes
    $values[]=array(":numero"=>$tableauNumero, ":tableau"=>$tab[1], ":ligne"=>$tab[2], ":colonne"=>$tab[3]);
  }
}
if(!empty($values)){
  $sql="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`,`tableau`,`ligne`,`colonne`) ";
  $sql.="VALUES (:numero, :tableau, :ligne, :colonne)";

  $db=new dbh();
  $db->CSRFToken = $CSRFToken;
  $db->prepare($sql);
  foreach($values as $elem){
    $db->execute($elem);
  }
}
?>