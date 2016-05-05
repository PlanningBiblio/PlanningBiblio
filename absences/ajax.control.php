<?php
/**
Planning Biblio, Version 2.3.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : absences/ajax.control.php
Création : mai 2011
Dernière modification : 6 mai 2016
@author Jérôme Combes <jerome@planningbiblio.fr>
@author Etienne Cavalié <etienne.cavalie@unice.fr>

Description :
Permet de controler en arrière-plan si un agent est absent entre 2 dates et s'il n'est pas placé sur un planning validé

Page appelée par la fonction javascript verif_absences utilisée par les page absences/ajouter.php et absences/modif.php
*/

ini_set('display_errors',0);

require_once "../include/config.php";
require_once "../include/function.php";
require_once "class.absences.php";

$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$groupe=filter_input(INPUT_GET,"groupe",FILTER_SANITIZE_STRING);
$debut=filter_input(INPUT_GET,"debut",FILTER_CALLBACK,array("options"=>"sanitize_dateTimeSQL"));
$fin=filter_input(INPUT_GET,"fin",FILTER_CALLBACK,array("options"=>"sanitize_dateTimeSQL"));
$perso_ids=filter_input(INPUT_GET,"perso_ids",FILTER_SANITIZE_STRING);
$perso_ids=json_decode(html_entity_decode($perso_ids,ENT_QUOTES|ENT_IGNORE,"UTF-8"));

$resul=array();

// Pour chaque agent, contrôle si autre absence, si placé sur planning validé, si placé sur planning en cours d'élaboration 
foreach($perso_ids as $perso_id){

  $result[$perso_id]=array("perso_id"=>$perso_id, "autreAbsence"=>null, "planning"=>null);

  // Contrôle des autres absences
  if($groupe){
    // S'il s'agit de la modification d'un groupe, contrôle s'il y a d'autres absences en dehors du groupe
    $db=new db();
    $db->select("absences",null,"`perso_id`='$perso_id' AND `groupe`<>'$groupe' AND ((debut<='$debut' AND fin>'$debut') OR (debut<'$fin' AND fin>='$fin') OR (debut>='$debut' AND fin <='$fin'))");
  }else{
    // S'il ne s'agit pas d'un groupe, contrôle s'il y a d'autre absences en dehors de celle sélectionnée
    $db=new db();
    $db->select("absences",null,"`perso_id`='$perso_id' AND `id`<>'$id' AND ((debut<='$debut' AND fin>'$debut') OR (debut<'$fin' AND fin>='$fin') OR (debut>='$debut' AND fin <='$fin'))");
  }
  
  if($db->result){
    $result[$perso_id]["autreAbsence"]=dateFr($db->result[0]['debut'])." ".heure2(substr($db->result[0]['debut'],-8))." et le ".dateFr($db->result[0]['fin'])." ".heure2(substr($db->result[0]['fin'],-8));
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
      $result[$perso_id]["planning"]=join(" ; ",$datesValidees);
    }
  }

  // Contrôle si placé sur planning en cours d'élaboration;
  $result[$perso_id]["planningVide"]=0;
  if($config['Absences-planningVide']==0){
    $debut=substr($debut,0,10);
    $fin=substr($fin,0,10);
    $db=new db();	  
    $req="SELECT COUNT(`id`) as `cnt` FROM `{$dbprefix}pl_poste` WHERE `date` BETWEEN '$debut' AND '$fin';";
    $db->query($req);
    if($db->result){
      $result[$perso_id]["planningVide"]=$db->result[0]['cnt'];
    }
    //for testing purpose $result[$perso_id]["planningVide"]=1;
  }
  
  // Ajoute le nom de l'agent
  $result[$perso_id]['nom']=nom($perso_id);  
}
echo json_encode($result);
?>