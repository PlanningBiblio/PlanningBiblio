<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : activites/class.activites.php
Création : mai 2011
Dernière modification : 8 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe activites : contient les fonctions de recherches des activites
Page appelée par les pages du dossier activites
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../include/accessDenied.php";
}

class activites{
  public $id=null;

  public function activites(){
  }

  public function delete(){
    $db=new db();
    $db->delete2("activites",array("id"=>$this->id));
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