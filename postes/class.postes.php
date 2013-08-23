<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.5.2													*
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : postes/class.postes.php												*
* Création : 29 novembre 2012													*
* Dernière modification : 16 janvier 2013											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Classe postes contenant la fonction postes::fetch permettant de rechercher les postes dans la base de données			*
*																*
* Utilisée par les fichiers du dossier "postes"											*
*********************************************************************************************************************************/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

class postes{
  public function postes(){
  }

  public function fetch($sort="nom",$name=null,$group=null){
    //	Select All
    $db=new db();
    $db->select("postes",null,null,"ORDER BY $sort");
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