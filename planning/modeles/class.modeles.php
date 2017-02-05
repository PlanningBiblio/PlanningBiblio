<?php
/*
Planning Biblio, Version 1.9.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/modeles/class.modeles.php
Création : 16 janvier 2013
Dernière modification : 8 avril 2015
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe modeles 
Utilisée par les fichiers du dossier "planning/modeles"
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../../include/accessDenied.php";
}

class modeles{
  public $id=null;
  public $nom=null;

  public function delete(){
    $nom=htmlentities($this->nom,ENT_QUOTES|ENT_IGNORE,"UTF-8");
    $db=new db();
    $db->delete2("pl_poste_modeles",array("nom"=>$nom));
    $db=new db();
    $db->delete2("pl_poste_modeles_tab",array("nom"=>$nom));
  }
}
?>