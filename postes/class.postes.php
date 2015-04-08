<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : postes/class.postes.php
Création : 29 novembre 2012
Dernière modification : 8 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe postes contenant la fonction postes::fetch permettant de rechercher les postes dans la base de données

Utilisée par les fichiers du dossier "postes"
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../include/accessDenied.php";
}

class postes{
  public $id=null;

  public function postes(){
  }

  public function delete(){
    $db=new db();
    $db->delete2("postes",array("id"=>$this->id));
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