<?php
/*
Planning Biblio, Version 1.8.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : plugins/class.plugins.php
Création : 18 juin 2014
Dernière modification : 18 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe plugins
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

class plugins{
  public $liste=array();	// Liste des plugins (array("ldap","conges"))
  public $tab=array();		// Tableau contenant les noms et versions des plugins (array("ldap"=>array("nom"=>"ldap","version"=>"1.0")))

  public function plugins(){
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