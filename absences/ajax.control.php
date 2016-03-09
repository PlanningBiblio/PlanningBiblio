<?php
/*
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/ajax.control.php
Création : mai 2011
Dernière modification : 4 septembre 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Permet de controler en arrière-plan si un agent est absent entre 2 dates et s'il n'est pas placé sur un planning validé

Page appelée par la fonction javascript verif_absences utilisée par les page absences/ajouter.php et absences/modif.php
*/

ini_set('display_errors',0);

require_once "../include/config.php";
require_once "class.absences.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$perso_id=filter_input(INPUT_GET,"perso_id",FILTER_SANITIZE_NUMBER_INT);
$debut=filter_input(INPUT_GET,"debut",FILTER_CALLBACK,array("options"=>"sanitize_dateTimeSQL"));
$fin=filter_input(INPUT_GET,"fin",FILTER_CALLBACK,array("options"=>"sanitize_dateTimeSQL"));

$result=array("autreAbsence"=>null, "planning"=>null);

// Contrôle des autres absences
$db=new db();
$db->select("absences",null,"`perso_id`='$perso_id' AND `id`<>'$id' AND ((debut<='$debut' AND fin>'$debut') OR (debut<'$fin' AND fin>='$fin') OR (debut>='$debut' AND fin <='$fin'))");

if($db->result){
  $result["autreAbsence"]=dateFr($db->result[0]['debut'])." ".heure2(substr($db->result[0]['debut'],-8))." et le ".dateFr($db->result[0]['fin'])." ".heure2(substr($db->result[0]['fin'],-8));
}


// Contrôle si placé sur planning validé
if($config['Absences-apresValidation']==0){
  $datesValidees=array();

  $req="SELECT `date`,`site` FROM `{$dbprefix}pl_poste` WHERE `perso_id`='$perso_id' ";
  $req.="AND CONCAT_WS(' ',`date`,`debut`)<'$fin' AND CONCAT_WS(' ',`date`,`fin`)>'$debut' ";
  $req.="GROUP BY `date`;";

  $db=new db();
  $db->query($req);
  if($db->result){
    foreach($db->result as $elem){
      $db2=new db();
      $db2->select2("pl_poste_verrou","*",array("date"=>$elem['date'], "site"=>$elem['site'], "verrou2"=>"1"));
      if($db2->result){
	$datesValidees[]=dateFr($elem['date']);
      }
    }
  }
  if(!empty($datesValidees)){
    $result["planning"]=join(" ; ",$datesValidees);
  }
}

<<<<<<< Updated upstream
// Contrôle si placé sur planning en cours d'élaboration;
if($config['Absences-planningVide']==0){
  $db=new db();	  
  $req="SELECT COUNT(`id`) as `cnt` FROM `{$dbprefix}pl_poste` WHERE `date` BETWEEN '$debut' AND '$fin';";
  $db->query($req);
  if($db->result){
	$result["planningVide"]=$db->result[0]['cnt'];
  }
  //for testing purpose $result["planningVide"]=1;
}
=======
>>>>>>> Stashed changes
echo json_encode($result);
?>
