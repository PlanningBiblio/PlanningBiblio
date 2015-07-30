<?php
/*
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/ajax.heuresSP.php
Création : 30 juillet 2015
Dernière modification : 30 juillet 2015
Auteur : Jérôme Combes jerome@planningbilbio.fr

Description :
Calcul les heures de service public à effectuer pour la semaine courante pour chaque agent
*/

session_start();

ini_set("display_error",0);

require_once "../../include/config.php";
require_once "../../include/function.php";
require_once "../../include/horaires.php";
require_once "../../personnel/class.personnel.php";

//	Initilisation des variables
$date=filter_input(INPUT_POST,"date",FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));

$d=new datePl($date);
$dates=$d->dates;
$semaine3=$d->semaine3;
$j1=$dates[0];
$j7=$dates[6];

// Recherche des heures de SP des agents pour cette semaine
// Recherche si les tableaux contenant les heures de SP existe
$db=new db();
$db->select2("heures_SP","*",array("semaine"=>$j1));
$heuresSPUpdate=0;
if($db->result){
  $heuresSPUpdate=$db->result[0]["update_time"];
  $heuresSP=json_decode((html_entity_decode($db->result[0]["heures"],ENT_QUOTES|ENT_IGNORE,"utf-8")));
}

// Recherche des heures de SP avec le module planningHebdo
if($config['PlanningHebdo']){
  require_once("../../planningHebdo/class.planningHebdo.php");

  // Vérifie si la table planningHebdo a été mise à jour depuis le dernier calcul
  $p=new planningHebdo();
  $pHUpdate=strtotime($p->update_time());
  
  // Si la table planningHebdo a été modifiée depuis la Création du tableaux des heures
  // Ou si le tableau des heures n'a pas été créé ($heuresSPUpdate=0), on le (re)fait.
  if($pHUpdate>$heuresSPUpdate){
    $heuresSP=array();
  
    // Recherche de tous les agents pouvant faire du service public
    $p=new personnel();
    $p->fetch("nom","Actif");
    if(!empty($p->elements)){
      // Pour chaque agents
      foreach($p->elements as $key1 => $value1){
	$heuresSP[$key1]=$value1["heuresHebdo"];

	if(strpos($value1["heuresHebdo"],"%")){
	  $minutesHebdo=0;
	  $ph=new planningHebdo();
	  $ph->debut=$j1;
	  $ph->fin=$j7;
	  $ph->valide=true;
	  $ph->fetch();
	  if($ph->elements and !empty($ph->elements)){
	    // Calcul des heures depuis les plannings de présence
	    // Pour chaque jour de la semaine
	    foreach($dates as $key2 => $jour){
	      // On cherche le planning de présence valable pour chaque journée
	      foreach($ph->elements as $edt){
		if($edt['perso_id']==$value1["id"]){
		  // Planning de présence trouvé
		  if($jour>=$edt['debut'] and $jour<=$edt['fin']){
		    // $pause = true si pause détectée le midi
		    $pause=false;
		    // Offset : pour semaines 1,2,3 ...
		    $offset=($semaine3*7)-7;
		    $key3=$key2+$offset;
		    // Si heure de début et de fin de matiné
		    if(array_key_exists($key3,$edt['temps']) and $edt['temps'][$key3][0] and $edt['temps'][$key3][1]){
		      $minutesHebdo+=diff_heures($edt['temps'][$key3][0],$edt['temps'][$key3][1],"minutes");
		      $pause=true;
		    }
		    // Si heure de début et de fin d'après midi
		    if(array_key_exists($key3,$edt['temps']) and $edt['temps'][$key3][2] and $edt['temps'][$key3][3]){
		      $minutesHebdo+=diff_heures($edt['temps'][$key3][2],$edt['temps'][$key3][3],"minutes");
		      $pause=true;
		    }
		    // Si pas de pause le midi
		    if(!$pause){
		      // Et heure de début et de fin de journée
		      if(array_key_exists($key3,$edt['temps']) and $edt['temps'][$key3][0] and $edt['temps'][$key3][3]){
			$minutesHebdo+=diff_heures($edt['temps'][$key3][0],$edt['temps'][$key3][3],"minutes");
		      }
		    }
		  }
		}
	      }
	    }
	  }

	  $heuresRelles=$minutesHebdo/60;
	  // On applique le pourcentage
	  $pourcent=(float) str_replace("%",null,$value1["heuresHebdo"]);
	  $heuresRelles=$heuresRelles*$pourcent/100;
	  $heuresSP[$key1]=$heuresRelles;
	}
      }
    }
    
    // Enregistrement des horaires dans la base de données
    $db=new db();
    $db->delete2("heures_SP",array("semaine"=>$j1));
    $db=new db();
    $db->insert2("heures_SP",array("semaine"=>$j1,"update_time"=>time(),"heures"=>json_encode($heuresSP)));
  }

// Recherche des heures de SP sans le module planningHebdo
}else{
  // Vérifie si la table personnel a été mise à jour depuis le dernier calcul
  $p=new personnel();
  $pUpdate=strtotime($p->update_time());

  // Si la table personnel a été modifiée depuis la Création du tableaux des heures
  // Ou si le tableau des heures n'a pas été créé ($heuresSPUpdate=0), on le (re)fait.
  if($pUpdate>$heuresSPUpdate){
    $heuresSP=array();
    $p=new personnel();
    $p->fetch("nom","Actif");
    if(!empty($p->elements)){
      // Pour chaque agents
      foreach($p->elements as $key1 => $value1){
	$heuresSP[$key1]=$value1["heuresHebdo"];

	if(strpos($value1["heuresHebdo"],"%")){
	  $minutesHebdo=0;
	  if($value1['temps'] and is_serialized($value1['temps'])){
	    $temps=unserialize($value1['temps']);

	    // Calcul des heures
	    // Pour chaque jour de la semaine
	    foreach($dates as $key2 => $jour){
	      // $pause = true si pause détectée le midi
	      $pause=false;
	      // Offset : pour semaines 1,2,3 ...
	      $offset=($semaine3*7)-7;
	      $key3=$key2+$offset;
	      // Si heure de début et de fin de matiné
	      if(array_key_exists($key3,$temps) and $temps[$key3][0] and $temps[$key3][1]){
		$minutesHebdo+=diff_heures($temps[$key3][0],$temps[$key3][1],"minutes");
		$pause=true;
	      }
	      // Si heure de début et de fin d'après midi
	      if(array_key_exists($key3,$temps) and $temps[$key3][2] and $temps[$key3][3]){
		$minutesHebdo+=diff_heures($temps[$key3][2],$temps[$key3][3],"minutes");
		$pause=true;
	      }
	      // Si pas de pause le midi
	      if(!$pause){
		// Et heure de début et de fin de journée
		if(array_key_exists($key3,$temps) and $temps[$key3][0] and $temps[$key3][3]){
		  $minutesHebdo+=diff_heures($temps[$key3][0],$temps[$key3][3],"minutes");
		}
	      }
	    }
	  }

	  $heuresRelles=$minutesHebdo/60;
	  // On applique le pourcentage
	  $pourcent=(float) str_replace("%",null,$value1["heuresHebdo"]);
	  $heuresRelles=$heuresRelles*$pourcent/100;
	  $heuresSP[$key1]=$heuresRelles;
	}
      }
    }

    // Enregistrement des horaires dans la base de données
    $db=new db();
    $db->delete2("heures_SP",array("semaine"=>$j1));
    $db=new db();
    $db->insert2("heures_SP",array("semaine"=>$j1,"update_time"=>time(),"heures"=>json_encode($heuresSP)));
  }
}
$_SESSION['oups']['heuresSP'] = (array) $heuresSP;
?>