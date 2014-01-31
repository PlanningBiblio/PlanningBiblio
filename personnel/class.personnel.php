<?php
/*
Planning Biblio, Version 1.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : personnel/class.personnel.php
Création : 16 janvier 2013
Dernière modification : 15 octobre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe personnel : contient la fonction personnel::fetch permettant de rechercher les agents. 
personnel::fetch prend en paramètres $tri (nom de la colonne), $actif (string), $name (string, nom ou prenom de l'agent)

Page appelée par les autres fichiers du dossier personnel
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

class personnel{
  public $elements=array();
  // supprime : permet de sélectionner les agents selon leur état de suppression
  // Tableau, valeur 0=pas supprimé, 1=1ère suppression (corbeille), 2=suppression définitive
  public $supprime=array(0);

  public function personnel(){
  }

  public function delete($liste){
    $update=array("supprime"=>"2","login"=>"CONCAT(id,".time().")","mail"=>null,"arrivee"=>null,"depart"=>null,"postes"=>null,"droits"=>null,
      "password"=>null,"commentaires"=>"Suppression définitive le ".date("d/m/Y"), "last_login"=>null, "temps"=>null, 
      "informations"=>null, "recup"=>null, "heuresTravail"=>null, "heuresHebdo"=>null, "site"=>null);
    
    $db=new db();
    $db->update2("personnel",$update,"`id` IN ($liste)");

    $db=new db();
    $db->select("plugins");
    $plugins=array();
    if($db->result){
      foreach($db->result as $elem){
	$plugins[]=$elem['nom'];
      }
    }
 
    $version=$GLOBALS['config']['Version'];	// Pour autoriser les accès aux pages suppression_agents
    if(in_array("conges",$plugins)){
      include "plugins/conges/suppression_agents.php";
    }
    if(in_array("planningHebdo",$plugins)){
      include "plugins/planningHebdo/suppression_agents.php";
    }

  }

  public function fetch($tri="nom",$actif=null,$name=null){
    $filter=array();

    // Filtre selon le champ actif (administratif, service public)
    $actif=htmlentities($actif,ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
    if($actif){
      $filter[]="`actif`='$actif'";
    }

    // Filtre selon le champ supprime
    $supprime=join("','",$this->supprime);
    $filter[]="`supprime` IN ('$supprime')";

    $filter=join(" AND ",$filter);

    $db=new db();
    $db->select("personnel",null,$filter,"ORDER BY $tri");
    $all=$db->result;
    if(!$db->result)
      return false;

    //	By default $result=$all
    $result=array();
    foreach($all as $elem){
      $result[$elem['id']]=$elem;
    }

    //	If name, keep only matching results
    if($name){
      $result=array();
      foreach($all as $elem){
	if(pl_stristr($elem['nom'],$name) or pl_stristr($elem['prenom'],$name)){
	  $result[$elem['id']]=$elem;
	}
      }
    }
  
    //	Suppression de l'utilisateur "Tout le monde"
    if(!$GLOBALS['config']['toutlemonde']){
      unset($result[2]);
    }

    $this->elements=$result;
  }


  public function fetchById($id){
    $db=new db();
    $db->select("personnel",null,"id='$id'");
    $this->elements=$db->result;
  }


  public function fetchEDTSamedi($perso_id,$debut,$fin){
    if(!$GLOBALS['config']['EDTSamedi']){
      return false;
    }
    $db=new db();
    $db->select("EDTSamedi","*","semaine>='$debut' AND semaine<='$fin' AND perso_id='$perso_id'");
    if($db->result){
      foreach($db->result as $elem){
	$this->elements[]=$elem['semaine'];
      }
    }
  }

  public function updateEDTSamedi($eDTSamedi,$debut,$fin,$perso_id){
    if(!$GLOBALS['config']['EDTSamedi']){
      return false;
    }

    $db=new db();
    $db->delete("EDTSamedi","`semaine`>='$debut' AND `semaine`<='$fin' AND `perso_id`='$perso_id'");

    if($eDTSamedi and !empty($eDTSamedi)){
      $insert=array();
      foreach($eDTSamedi as $elem){
	$insert[]=array("perso_id"=>$perso_id, "semaine"=>$elem);
      }
      $db=new db();
      $db->insert2("EDTSamedi",$insert);
    }
 }

}

?>