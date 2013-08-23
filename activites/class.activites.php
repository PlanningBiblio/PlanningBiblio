<?php
/************************************************************************************************************************
* Planning Biblio, Version 1.5.2												*
* Licence GNU/GPL (version 2 et au dela)										*
* Voir les fichiers README.txt et COPYING.txt										*
* Copyright (C) 2011-2013 - Jérôme Combes										*
*															*
* Fichier : activites/class.activites.php										*
* Création : mai 2011													*
* Dernière modification : 11 janvier 2013										*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr									*
*															*
* Description :														*
* Classe activites : contient les fonctions de recherches des activites							*
*															*
* Page appelée par les pages du dossier activites									*
*************************************************************************************************************************/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

class activites{
  public function activites(){
  }

  public function fetch($sort="nom",$name=null){
    //	Select All Activities
    $db=new db();
    $db->select("activites",null,null,"ORDER BY $sort");
    $all=$db->result;

    //	By default $result=$all
    $result=$all;

    //	If name, keep only matching results
    if(is_array($all) and $name){
      $result=array();
      foreach($all as $elem){
	if(pl_stristr($elem['nom'],$name)){
	  $result[]=$elem;
	}
      }
    }
    $this->elements=$result;
  }

}
?>