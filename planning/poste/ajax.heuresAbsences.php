<?php
/*
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/ajax.heuresAbsences.php
Création : 3 août 2015
Dernière modification : 26 août 2015
Auteur : Jérôme Combes jerome@planningbilbio.fr

Description :
Calcul les heures d'absences sur la semaine courante pour chaque agent
Script appelé en ajax lors du chargement de la page planning/poste/index.php (voir planning/poste/js/planning.js)
- Recherche les heures d'absences enregistrées dans la table heures_absences pour la semaine courante
- Si l'information n'est pas présente dans la table heures_absences, effectue les calculs, les stock dans la table 
  et les met en session ($_SESSION['oups']['heuresAbsences'])
- Calcul effectué à partir de la table absences
- Calcul également effectué à partir de la table conges si plugin installé
- Les heures d'absences calculées pour chaque agent et placées dans $_SESSION['oups']['heuresAbsences'] sont utilisées 
  dans le menu permettant de placer les agents dans les plannings. (ajax.menudiv.php, class.planning.php planning::menudivAfficheAgents)
- Elles sont calculées en ajax lors du 1er chargement du planning de la semaine et mises en cache 
  afin de ne pas ralentir le chargement du menu.
- Elles sont recalculées si la table absences (est conges) est (sont)  modifiée(s).
- Ne fonctionne que si le module planningHebdo est activé
- Calcul des heures de congés : A FAIRE
*/

session_start();

ini_set("display_error",0);

require_once "../../include/config.php";
require_once "../../include/function.php";
require_once "../../include/horaires.php";
require_once "../../plugins/plugins.php";
require_once "../../absences/class.absences.php";
require_once "../../personnel/class.personnel.php";
require_once "../../planningHebdo/class.planningHebdo.php";


//	Initilisation des variables
$date=filter_input(INPUT_POST,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));

$d=new datePl($date);
$dates=$d->dates;
$semaine3=$d->semaine3;
$j1=$dates[0];
$j7=$dates[6];

// Recherche des heures d'absences des agents pour cette semaine
// Recherche si le tableau contenant les heures d'absences existe
$db=new db();
$db->select2("heures_Absences","*",array("semaine"=>$j1));
$heuresAbsencesUpdate=0;
if($db->result){
  $heuresAbsencesUpdate=$db->result[0]["update_time"];
  $heures=json_decode((html_entity_decode($db->result[0]["heures"],ENT_QUOTES|ENT_IGNORE,"utf-8")));
}


// Vérifie si la table absences a été mise à jour depuis le dernier calcul
$a=new absences();
$aUpdate=strtotime($a->update_time());

// Vérifie si la table planningHebdo a été mise à jour depuis le dernier calcul
$p=new planningHebdo();
$pHUpdate=strtotime($p->update_time());

// Si la table absences ou la table planningHebdo a été modifiée depuis la création du tableaux des heures
// Ou si le tableau des heures n'a pas été créé ($heuresAbsencesUpdate=0), on le (re)fait.
if($aUpdate>$heuresAbsencesUpdate or $pHUpdate>$heuresAbsencesUpdate){
  // Recherche de toutes les absences
  $absences=array();
  $a =new absences();
  $a->valide=true;
  $a->fetch(null,null,null,$j1,$j7,null);
  if($a->elements and !empty($a->elements)){
    $absences=$a->elements;
  }

  // Recherche de tous les plannings de présence
  $edt=array();
  $ph=new planningHebdo();
  $ph->debut=$j1;
  $ph->fin=$j7;
  $ph->valide=true;
  $ph->fetch();
  if($ph->elements and !empty($ph->elements)){
    $edt=$ph->elements;
  }

  // Calcul des heures d'absences
  $heures=array();
  if(!empty($absences)){
    // Pour chaque absence
    foreach($absences as $key => $value){
      $perso_id=$value['perso_id'];
      $h1=array_key_exists($perso_id,$heures)?$heures[$perso_id]:0;
      
      // Si $h1 n'est pas un nombre ("N/A"), une erreur de calcul a été enregistrée. Donc on ne continue pas le calcul.
      // $heures[$perso_id] restera "N/A"
      if(!is_numeric($h1)){
	continue;
      }
      
      $a=new absences();
      $a->debut=$value['debut'];
      $a->fin=$value['fin'];
      $a->perso_id=$perso_id;
      $a->edt=$edt;
      $a->ignoreFermeture=true;
      $a->calculTemps2();

      $h=$a->heures;
      if(is_numeric($h)){
	$h=$h+$h1;
      }else{
	$h="N/A";
      }

      $heures[$perso_id]=$h;
    }
  }

  // Enregistrement des heures dans la base de données
  $db=new db();
  $db->delete2("heures_Absences",array("semaine"=>$j1));
  $db=new db();
  $db->insert2("heures_Absences",array("semaine"=>$j1,"update_time"=>time(),"heures"=>json_encode($heures)));
  }

$_SESSION['oups']['heuresAbsences'] = (array) $heures;
?>