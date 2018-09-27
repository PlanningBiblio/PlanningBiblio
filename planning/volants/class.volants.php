<?php
/**
Planning Biblio, Version 2.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : planning/volants/class.volants.php
Création : 7 avril 2018
Dernière modification : 7 avril 2018
@author Jérôme Combes <jerome@planningbiblio.fr>


Description :
Classe utilisée pour la gestion des agents volants

Cette page est appelée par la page planning/volants/index.php
*/


require_once __DIR__.'/../../include/function.php';
require_once __DIR__.'/../../personnel/class.personnel.php';

class volants {

  public $error = null;
  public $selected = array();
  public $tous = array();

  function __construct(){
  }
  
  public function fetch($date){

    // Date du lundi
    $d = new datePl($date);
    $date = $d->dates[0];

    // Tous les agents
    $p = new personnel();
    $p->fetch('nom', 'Actif');
    $tous = $p->elements;

    // Agents sélectionnés
    $selected = array();
    $selectionnes = array();

    $db = new db();
    $db->select2('volants', null, array('date' => $date));
    if($db->result){
      foreach($db->result as $elem){
        $selected[] = $elem['perso_id'];
        $selectionnes[] = array($elem['perso_id'], nom($elem['perso_id'], $tous));
      }
    }

    // Agents disponibles
    $dispo = array();
    foreach($tous as $elem){
      if( !in_array($elem['id'], $selected)){
        $dispo[] = array($elem['id'], nom($elem['id'], $tous));
      }
    }
    
    $this->selected = $selected;
    $this->tous = $tous;
  }
  
  public function set($date, $ids, $CSRFToken){
  
    $db = new db();
    $db->CSRFToken = $CSRFToken;
    $db->delete('volants', array('date' => $date));
    if($db->error){
      $this->error = $db->error;
    }
  
    if(!empty($ids)){
      $db = new dbh();
      $db->CSRFToken = $CSRFToken;

      $db->prepare("INSERT INTO `{$GLOBALS['dbprefix']}volants` (`date`, `perso_id`) VALUES ('$date', :perso_id);");
      foreach($ids as $elem){
        $db->execute(array(':perso_id' => $elem));
      }
      
      if($db->error){
        $this->error = $db->error;
      }
    }
  
  }

}
