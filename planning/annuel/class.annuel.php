<?php
/**
Planning Biblio, Version 2.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/annuel/class.annuel.php
Création : 2 août 2016
Dernière modification : 2 août 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Classe pour la gestion des plannings annuels

*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../../include/accessDenied.php";
}
if(!$config['PlanningAnnuel']){
  include_once "include/accessDenied.php";
}

class annuel{

  public $elements = array();


  public function fetch(){
    $tab = array();
    $db = new db();
    // TODO : a continuer
    $db->selectInnerJoin(array('pl_annuels','id'), array('pl_annuels_elements','pl_id'));
    
    if($db->result){
      foreach($db->result as $elem){
        if(!isset($tab[$elem['pl_id']])){
          $tab[$elem['pl_id']] = array();
        }
        $tab[$elem['pl_id']][] = $elem;
      }
    }
  }









}
