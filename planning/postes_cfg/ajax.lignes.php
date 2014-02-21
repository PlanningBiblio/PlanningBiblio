<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/ajax.lignes.php
Création : 3 février 2014
Dernière modification : 3 février
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Met à jour les lignes des tableaux
Appelé en Ajax via la fonction configLignes à partir de la page lignes.php (dans modif.php)
*/

session_start();
ini_set('display_errors',0);
error_reporting(0);

include "../../include/config.php";
include "../../include/function.php";
include "class.tableaux.php";

$keys=array_keys($_POST);
$values=array();
$tableauNumero=$_POST['id'];

// Suppression des infos concernant ce tableau dans la table pl_poste_lignes
$db=new db();
$db->query("DELETE FROM `{$dbprefix}pl_poste_lignes` WHERE `numero`='$tableauNumero';");
// Insertion des données dans la table pl_poste_lignes
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
    $values[]="('$tableauNumero','{$tab[1]}','{$tab[2]}','{$_POST[$key]}','$type')";
  }
}
if($values[0]){
  $sql="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`,`tableau`,`ligne`,`poste`,`type`) VALUES ";
  $sql.=join($values,",").";";
  $db=new db();
  $db->query($sql);
}

$values=array();
// Suppression des infos concernant ce tableau dans la table pl_poste_cellules
$db=new db();
$db->query("DELETE FROM `{$dbprefix}pl_poste_cellules` WHERE `numero`='$tableauNumero';");
// Insertion des données dans la table pl_poste_cellules
foreach($keys as $key){
  if($_POST[$key] and substr($key,0,8)=="checkbox"){
    $tab=explode("_",$key);  //1: tableau ; 2 lignes ; 3 colonnes
    $values[]="('$tableauNumero','{$tab[1]}','{$tab[2]}','{$tab[3]}')";
  }
}
if(!empty($values)){
  $sql="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`,`tableau`,`ligne`,`colonne`) VALUES ";
  $sql.=join($values,",").";";
  $db=new db();
  $db->query($sql);
}
?>