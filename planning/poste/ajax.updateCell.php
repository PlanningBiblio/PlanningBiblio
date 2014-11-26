<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/poste/ajax.updateCell.php
Création : 31 octobre 2014
Dernière modification : 26 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet la mise à jour en arrière plan de la base de données (table pl_poste) lors de l'utilisation du menu contextuel de la 
page planning/poste/index.php pour placer les agents

Cette page est appelée par la function JavaScript "bataille_navale" utilisé par le fichier planning/poste/menudiv.php
*/

session_start();
// Includes
require_once "../../include/config.php";
require_once "../../include/function.php";
require_once "../../plugins/plugins.php";

ini_set("display_errors",0);
ini_set("error_reporting",E_ALL);

//	Initialisation des variables
$site=$_SESSION['oups']['site'];
$ajouter=$_POST['ajouter'];
$perso_id=$_POST['perso_id'];
$perso_id_origine=$_POST['perso_id_origine'];
$date=$_SESSION['PLdate'];
$debut=$_POST['debut'];
$fin=$_POST['fin'];
$absent=isset($_POST['absent'])?$_POST['absent']:"0";
$poste=$_POST['poste'];
$barrer=$_POST['barrer'];


// Pärtie 1 : Enregistrement des nouveaux éléments

// Suppression ou marquage absent
if($perso_id==0){
  if($barrer){
    $req="UPDATE `{$dbprefix}pl_poste` SET `absent`='1', `chgt_login`='{$_SESSION['login_id']}', `chgt_time`=SYSDATE() WHERE `date`='$date' AND `debut`='$debut' AND `fin`='$fin' AND `poste`='$poste' AND `site`='$site' AND `perso_id`='$perso_id_origine';";
  }
  else{
    $req="DELETE FROM `{$dbprefix}pl_poste` WHERE `date`='$date' AND `debut`='$debut' AND `fin`='$fin' AND `poste`='$poste' AND `site`='$site' AND `perso_id`='$perso_id_origine';";
  }
}
// Remplacement
else{
  if(!$barrer and !$ajouter){		// on remplace
    $db=new db();
    $db->query("DELETE FROM `{$dbprefix}pl_poste` WHERE `date`='$date' AND `debut`='$debut' AND `fin`='$fin' AND `poste`='$poste' AND `site`='$site' AND `perso_id`='$perso_id_origine';");
    $req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`debut`,`fin`,`poste`,`site`,`perso_id`,`chgt_login`,`chgt_time`) VALUES ('$date','$debut','$fin','$poste','$site','$perso_id','{$_SESSION['login_id']}',SYSDATE());";
  }
  elseif($barrer){			// on barre l'ancien et ajoute le nouveau
    $db=new db();
    $db->query("UPDATE `{$dbprefix}pl_poste` SET `absent`='1', `chgt_login`='{$_SESSION['login_id']}' WHERE `date`='$date' AND `debut`='$debut' AND `fin`='$fin' AND `poste`='$poste' AND `site`='$site' AND `perso_id`='$perso_id_origine'");
    $req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`debut`,`fin`,`poste`,`site`,`perso_id`,`chgt_login`,`chgt_time`) VALUES ('$date','$debut','$fin','$poste','$site','$perso_id','{$_SESSION['login_id']}',SYSDATE());";
  }
  elseif($ajouter){			// on ajoute
    $req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`debut`,`fin`,`poste`,`site`,`perso_id`,`chgt_login`,`chgt_time`) 
      VALUES ('$date','$debut','$fin','$poste','$site','$perso_id','{$_SESSION['login_id']}',SYSDATE());";
    }
}

$db=new db();
$db->query($req);


// Partie 2 : Récupération de l'ensemble des éléments
// Et transmission à la fonction JS bataille_navale pour mise à jour de l'affichage de la cellule
$db=new db();
$db->query("SELECT `{$dbprefix}personnel`.`nom` AS `nom`,`{$dbprefix}personnel`.`prenom` AS `prenom`, 
  `{$dbprefix}personnel`.`statut` AS `statut`, `{$dbprefix}personnel`.`service` AS `service`, 
  `{$dbprefix}pl_poste`.`perso_id` AS `perso_id`, `{$dbprefix}pl_poste`.`absent` AS `absent`, 
  `{$dbprefix}pl_poste`.`supprime` AS `supprime` 
  FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id`
  WHERE `{$dbprefix}pl_poste`.`date`='$date' AND `{$dbprefix}pl_poste`.`debut`='$debut' AND `{$dbprefix}pl_poste`.`fin`='$fin' 
  AND `{$dbprefix}pl_poste`.`poste`='$poste' AND `{$dbprefix}pl_poste`.`site`='$site' ORDER BY `nom`,`prenom`");

if(!$db->result){
  return;
}

$tab=$db->result;
for($i=0;$i<count($tab);$i++){
  // Mise en forme des statut et service pour affectation des classes css
  $tab[$i]["statut"]=removeAccents($tab[$i]["statut"]);
  $tab[$i]["service"]=removeAccents($tab[$i]["service"]);

  // Ajout des Sans Repas (SR)
  $tab[$i]["sr"]=0;
  if($config['Planning-sansRepas'] and $debut>="11:30:00" and $fin<="14:30:00"){
    $db=new db();
    $db->select("pl_poste","*","`date`='$date' AND `perso_id`='{$tab[$i]['perso_id']}' AND `debut` >='11:30:00' AND `fin`<='14:30:00'");
    if($db->nb>1){
      $tab[$i]["sr"]=1;
    }
  }
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