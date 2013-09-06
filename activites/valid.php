<?php
/************************************************************************************************************************
* Planning Biblio, Version 1.5.5												*
* Licence GNU/GPL (version 2 et au dela)										*
* Voir les fichiers README.txt et COPYING.txt										*
* Copyright (C) 2011-2013 - Jérôme Combes										*
*															*
* Fichier : activites/valid.php												*
* Création : mai 2011													*
* Dernière modification : 11 janvier 2013										*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr									*
*															*
* Description :														*
* Valide l'ajout ou la modification d'une activité									*
*															*
* Page appelée par la page index.php											*
*************************************************************************************************************************/

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
	  
  case "supprime" :
    $db=new db();
    $db->delete("activites","`id`='$id'");
    break;
}

echo "<script type='text/JavaScript'>document.location.href='index.php?page=activites/index.php';</script>\n";
?>