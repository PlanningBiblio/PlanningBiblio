<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : activites/valid.php
Création : mai 2011
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Valide l'ajout ou la modification d'une activité

Page appelée par la page index.php
*/

require_once "class.activites.php";

$id=$_GET['id'];
$action=$_GET['action'];
$nom=isset($_GET['nom'])?trim($_GET['nom']):null;

switch($action){
  case "ajout" :	
    $db=new db();
    $db->insert2("activites",array("nom"=>$nom));
    break;
  
  case "modif" :
    $db=new db();
    $db->update2("activites",array("nom"=>$nom),array("id"=>$id));
    break;
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=activites/index.php';</script>\n";
?>