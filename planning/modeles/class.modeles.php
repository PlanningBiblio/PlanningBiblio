<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/modeles/class.modeles.php
Création : 16 janvier 2013
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe modeles 
Utilisée par les fichiers du dossier "planning/modeles"
*/

// Si pas de $version => acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../../index.php");
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