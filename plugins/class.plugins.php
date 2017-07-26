<?php
/**
Planning Biblio, Version 2.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : plugins/class.plugins.php
Création : 18 juin 2014
Dernière modification : 22 juin 2017
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe plugins
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../include/accessDenied.php";
}

class plugins{
  public $liste=array();	// Liste des plugins (array("ldap","conges"))
  public $tab=array();		// Tableau contenant les noms et versions des plugins (array("ldap"=>array("nom"=>"ldap","version"=>"1.0")))

  public function __construct(){
  }

  public function fetch(){
    $db=new db();
    $db->select("plugins");
    if($db->result){
      foreach($db->result as $elem){
	$this->liste[]=$elem['nom'];
	$this->tab[]=array("name"=>$elem['nom'],"version"=>$elem['version']);
      }
    }
  }

  public function checkUpdateDB(){
    if(!isset($_SESSION['login_id'])){
      return;
    }
    
    $plugins=$this->tab;
    foreach($plugins as $plugin){
      $pluginVersion=null;
      @include_once "plugins/{$plugin['name']}/version.php";
      if($pluginVersion and $plugin['version']<$pluginVersion){
	$this->updateDB($plugin['name'],$plugin['version'],$pluginVersion);
      }
    }
  }

  public function updateDB($name,$oldVersion,$version){
    include_once "plugins/$name/updateDB.php";
  }

}
?>