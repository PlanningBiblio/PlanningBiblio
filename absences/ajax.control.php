<?php
/*
Planning Biblio, Version 1.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/ajax.control.php
Création : mai 2011
Dernière modification : 21 janvier 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de controler en arrière-plan si un agent est absent entre 2 dates et s'il n'est pas placé sur un planning validé

Page appelée par la fonction javascript verif_absences utilisée par les page absences/ajouter.php et absences/modif.php
*/

require_once "../include/config.php";
require_once "class.absences.php";

$id=$_GET['id'];
$perso_id=$_GET['perso_id'];
$debut=$_GET['debut'];
$fin=$_GET['fin'];


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
  $db=new db();
  $db->select("pl_poste","date,site","perso_id='$perso_id' AND date>='$debut' AND date<='$fin'","group by date");
  if($db->result){
    foreach($db->result as $elem){
      $db2=new db();
      $db2->select("pl_poste_verrou","*","date='{$elem['date']}' AND site='{$elem['site']}' AND verrou2='1'");
      if($db2->result){
	$datesValidees[]=dateFr($elem['date']);
      }
    }
  }
  if(!empty($datesValidees)){
    $result["planning"]=join(" ; ",$datesValidees);
  }
}

echo json_encode($result);
?>