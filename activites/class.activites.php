<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : activites/class.activites.php
Création : mai 2011
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe activites : contient les fonctions de recherches des activites
Page appelée par les pages du dossier activites
*/

// Si pas de $version => acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
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