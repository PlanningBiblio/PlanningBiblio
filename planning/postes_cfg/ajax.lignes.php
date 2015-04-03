<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/ajax.lignes.php
Création : 3 février 2014
Dernière modification : 3 mars 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Met à jour les lignes des tableaux
Appelé en Ajax via la fonction configLignes à partir de la page lignes.php (dans modif.php)
*/

session_start();
ini_set('display_errors',0);
error_reporting(0);

include "../../include/config.php";
include "class.tableaux.php";

$keys=array_keys($_POST);
$tableauNumero=$_POST['id'];

// Suppression des infos concernant ce tableau dans la table pl_poste_lignes
$db=new db();
$db->delete2("pl_poste_lignes",array("numero"=>$tableauNumero));

// Insertion des données dans la table pl_poste_lignes
$values=array();
foreach($keys as $key){
  if($_POST[$key] and substr($key,0,6)=="select"){
    $tab=explode("_",$key);  //1: tableau ; 2 lignes
    if(substr($tab[1],-5)=="Titre"){
      $type="titre";
      $tab[1]=substr($tab[1],0,-5);
    }
    elseif(substr($_POST[$key],-5)=="Ligne"){
      $type="ligne";
      $_POST[$key]=substr($_POST[$key],0,-5);
    }
    else{
      $type="poste";
    }
    $values[]=array(":numero"=>$tableauNumero, ":tableau"=>$tab[1], ":ligne"=>$tab[2], ":poste"=>$_POST[$key], ":type"=>$type);
  }
}
if($values[0]){
  $sql="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`,`tableau`,`ligne`,`poste`,`type`) ";
  $sql.="VALUES (:numero, :tableau, :ligne, :poste, :type);";

  $db=new dbh();
  $db->prepare($sql);
  foreach($values as $elem){
    $db->execute($elem);
  }
}

// Suppression des infos concernant ce tableau dans la table pl_poste_cellules
$db=new db();
$db->delete2("pl_poste_cellules",array("numero"=>$tableauNumero));

// Insertion des données dans la table pl_poste_cellules
$values=array();
foreach($keys as $key){
  if($_POST[$key] and substr($key,0,8)=="checkbox"){
    $tab=explode("_",$key);  //1: tableau ; 2 lignes ; 3 colonnes
    $values[]=array(":numero"=>$tableauNumero, ":tableau"=>$tab[1], ":ligne"=>$tab[2], ":colonne"=>$tab[3]);
  }
}
if(!empty($values)){
  $sql="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`,`tableau`,`ligne`,`colonne`) ";
  $sql.="VALUES (:numero, :tableau, :ligne, :colonne)";

  $db=new dbh();
  $db->prepare($sql);
  foreach($values as $elem){
    $db->execute($elem);
  }
}
?>