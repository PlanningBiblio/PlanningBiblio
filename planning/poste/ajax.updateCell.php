<?php
/**
Planning Biblio, Version 2.2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/poste/ajax.updateCell.php
Création : 31 octobre 2014
Dernière modification : 27 février 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet la mise à jour en arrière plan de la base de données (table pl_poste) lors de l'utilisation du menu contextuel de la 
page planning/poste/index.php pour placer les agents

Cette page est appelée par la function JavaScript "bataille_navale" utilisé par le fichier planning/poste/menudiv.php
*/

ini_set("display_errors",0);

session_start();

// Includes
require_once "../../include/config.php";
require_once "../../include/function.php";
require_once "../../plugins/plugins.php";
require_once "class.planning.php";

//	Initialisation des variables
$ajouter=filter_input(INPUT_POST,"ajouter",FILTER_CALLBACK,array("options"=>"sanitize_on"));
$barrer=filter_input(INPUT_POST,"barrer",FILTER_CALLBACK,array("options"=>"sanitize_on"));
$date=filter_input(INPUT_POST,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));
$debut=filter_input(INPUT_POST,"debut",FILTER_CALLBACK,array("options"=>"sanitize_time"));
$fin=filter_input(INPUT_POST,"fin",FILTER_CALLBACK,array("options"=>"sanitize_time"));
$perso_id=filter_input(INPUT_POST,"perso_id",FILTER_SANITIZE_NUMBER_INT);
$perso_id_origine=filter_input(INPUT_POST,"perso_id_origine",FILTER_SANITIZE_NUMBER_INT);
$poste=filter_input(INPUT_POST,"poste",FILTER_SANITIZE_NUMBER_INT);
$site=filter_input(INPUT_POST,"site",FILTER_SANITIZE_NUMBER_INT);
$tout=filter_input(INPUT_POST,"tout",FILTER_CALLBACK,array("options"=>"sanitize_on"));

$login_id=$_SESSION['login_id'];
$now=date("Y-m-d H:i:s");

// Pärtie 1 : Enregistrement des nouveaux éléments

// Suppression ou marquage absent
if($perso_id==0){
  // Tout barrer
  if($barrer and $tout){
    $set=array("absent"=>"1", "chgt_login"=>$login_id, "chgt_time"=>$now);
    $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site);
    $db=new db();
    $db->update2("pl_poste",$set,$where);

  // Barrer l'agent sélectionné
  }elseif($barrer){
    $set=array("absent"=>"1", "chgt_login"=>$login_id, "chgt_time"=>$now);
    $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id_origine);
    $db=new db();
    $db->update2("pl_poste",$set,$where);
  }
  // Tout supprimer
  elseif($tout){
    $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site);
    $db=new db();
    $db->delete2("pl_poste",$where);
  // Supprimer l'agent sélectionné
  }else{
    $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id_origine);
    $db=new db();
    $db->delete2("pl_poste",$where);
  }
}
// Remplacement
else{
  // si ni barrer, ni ajouter : on remplace
  if(!$barrer and !$ajouter){
    // Suppression des anciens éléments
    $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=> $perso_id_origine);
    $db=new db();
    $db->delete2("pl_poste",$where);

    // Insertion des nouveaux éléments
    $insert=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id, 
      "chgt_login"=>$login_id, "chgt_time"=>$now);
    $db=new db();
    $db->insert2("pl_poste",$insert);
  }
  // Si barrer : on barre l'ancien et ajoute le nouveau
  elseif($barrer){
    // On barre l'ancien
    $set=array("absent"=>"1", "chgt_login"=>$login_id, "chgt_time"=>$now);
    $where=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id_origine);
    $db=new db();
    $db->update2("pl_poste",$set,$where);
    
    // On ajoute le nouveau
    $insert=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id, 
      "chgt_login"=>$login_id, "chgt_time"=>$now);
    $db=new db();
    $db->insert2("pl_poste",$insert);
  }
  // Si Ajouter, on garde l'ancien et ajoute le nouveau
  elseif($ajouter){
    $insert=array("date"=>$date, "debut"=>$debut, "fin"=>$fin, "poste"=>$poste, "site"=>$site, "perso_id"=>$perso_id, 
      "chgt_login"=>$login_id, "chgt_time"=>$now);
    $db=new db();
    $db->insert2("pl_poste",$insert);
    }
}


// Partie 2 : Récupération de l'ensemble des éléments
// Et transmission à la fonction JS bataille_navale pour mise à jour de l'affichage de la cellule

$db->selectInnerJoin(
  array("pl_poste","perso_id"),
  array("personnel","id"),
  array("absent","supprime"),
  array("nom","prenom","statut","service",array("name"=>"id","as"=>"perso_id")),
  array("date"=>$date, "debut"=>$debut, "fin"=> $fin, "poste"=>$poste, "site"=>$site),
  array(),
  "ORDER BY nom,prenom");

if(!$db->result){
  echo json_encode(array());
  return;
}

$tab=$db->result;
usort($tab,"cmp_nom_prenom");

for($i=0;$i<count($tab);$i++){
  // Mise en forme des statut et service pour affectation des classes css
  $tab[$i]["statut"]=removeAccents($tab[$i]["statut"]);
  $tab[$i]["service"]=removeAccents($tab[$i]["service"]);

  // Ajout des Sans Repas (SR)
  $p=new planning();
  $tab[$i]["sr"]=$p->sansRepas($date,$debut,$fin,$tab[$i]["perso_id"]);
}

// Marquage des congés
if(in_array("conges",$plugins)){
  include "../../plugins/conges/ajax.planning.updateCell.php";
}

echo json_encode($tab);

/*
Résultat :
  [0] => Array (
    [nom] => Nom
    [prenom] => Prénom
    [statut] => Statut
    [service] => Service
    [perso_id] => 86
    [absent] => 0/1
    [supprime] => 0/1
    [sr] =>0/1
    )
  [1] => Array (
    ...
*/
?>