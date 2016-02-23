<?php
/*
Planning Biblio, Version 2.0.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : postes/class.postes.php
Création : 29 novembre 2012
Dernière modification : 15 septembre 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

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

    $all=array();
    if($db->result){
      foreach($db->result as $elem){
	$all[$elem['id']]=$elem;
      }
    }

    //	By default $result=$all
    $result=$all;

    //	If name, keep only matching results
    if(!empty($all) and $name){
      $result=array();
      foreach($all as $elem){
	if(pl_stristr($elem['nom'],$name)){
	  $result[$elem['id']]=$elem;
	}
      }
    }

    $this->elements=$result;
  }

}
?>