<?php
/**
Planning Biblio, Version 2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : activites/class.activites.php
Création : mai 2011
Dernière modification : 21 mars 2016
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
  public $elements=array();

  public function activites(){
  }

  public function delete(){
    $db=new db();
    $db->delete2("activites",array("id"=>$this->id));
  }

  public function fetch(){
    $activites=array();
    $db=new db();
    $db->select2("activites");
    if($db->result){
      foreach($db->result as $elem){
	$activites[$elem['id']]=$elem;
      }
    }
    $this->elements=$activites;
  }

}
?>