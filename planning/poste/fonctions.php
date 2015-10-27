<?php
/*
Planning Biblio, Version 1.9.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/poste/fonctions.php
Création : mai 2011
Dernière modification : 27 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Fonctions utilisées par les pages des dossiers planning/poste et planning/postes_cgf
*/

// Securité : Traitement pour une reponse Ajax
if(array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
  $version='ajax';
}

// pas de $version=acces direct au fichier => Accès refusé
if(!isset($version)){
  include_once "../../include/accessDenied.php";
}

function cellule_poste($date,$debut,$fin,$colspan,$output,$poste,$site){
  $resultats=array();
  $classe=array();
  $i=0;
  
  if($GLOBALS['cellules']){
    foreach($GLOBALS['cellules'] as $elem){
      if($elem['poste']==$poste and $elem['debut']==$debut and $elem['fin']==$fin){
	//		Affichage du nom et du prénom
	$resultat=$elem['nom'];
	if($elem['prenom'])
	  $resultat.=" ".substr($elem['prenom'],0,1).".";

	//		Affichage des sans repas
	$p=new planning();
	if($p->sansRepas($date,$debut,$fin,$elem['perso_id'])){
	  $resultat.="<font class='sansRepas'>&nbsp;(SR)</font>";
	}

	$class_tmp=array();
	//		On barre les absents
	if($elem['absent'] or $elem['supprime']){
	  $class_tmp[]="red";
	  $class_tmp[]="striped";
	}
	//		On barre les congés
	if(in_array("conges",$GLOBALS['plugins'])){
	  include "plugins/conges/planning_cellule_poste.php";
	}
	  
	// Classe en fonction du statut et du service
	if($elem['statut']){
	  $class_tmp[]="statut_".strtolower(removeAccents(str_replace(" ","_",$elem['statut'])));
	}
	if($elem['service']){
	  $class_tmp[]="service_".strtolower(removeAccents(str_replace(" ","_",$elem['service'])));
	}
	$classe[$i]=join(" ",$class_tmp);

	// Création d'une balise span avec les classes cellSpan, et agent_ de façon à les repérer et agir dessus à partir de la fonction JS bataille_navale.
	$span="<span class='cellSpan agent_{$elem['perso_id']}'>$resultat</span>";

	$resultats[$i]=array("text"=>$span, "perso_id"=>$elem['perso_id']);
	$i++;
      }
    }
  }
  $GLOBALS['idCellule']++;
  $cellule="<td id='td{$GLOBALS['idCellule']}' colspan='$colspan' style='text-align:center;' class='menuTrigger' 
    oncontextmenu='cellule={$GLOBALS['idCellule']}'
    data-start='$debut' data-end='$fin' data-situation='$poste' data-cell='{$GLOBALS['idCellule']}' data-perso-id='0'>";
  for($i=0;$i<count($resultats);$i++){
    $cellule.="<div id='cellule{$GLOBALS['idCellule']}_$i' class='cellDiv {$classe[$i]}' data-perso-id='{$resultats[$i]['perso_id']}'>{$resultats[$i]['text']}</div>";
  }

  $cellule.="</td>\n";
  return $cellule;
}

function deja_place($date,$poste){
  $deja=array(0);
  $db=new db();
  $db->select2("pl_poste","perso_id",array("date"=>$date, "absent"=>"0", "poste"=>$poste),"GROUP BY `perso_id`");
  if($db->result){
    foreach($db->result as $elem){
      $deja[]=$elem['perso_id'];
    }
  }
  return $deja;
}

function deuxSP($date,$debut,$fin){
  $tab=array(0);
  $db=new db();
  $db->select("pl_poste","perso_id","date='$date' AND (debut='$fin' OR fin='$debut')","group by perso_id");
  if($db->result){
    foreach($db->result as $elem){
      $tab[]=$elem['perso_id'];
    }
  }
  return $tab;
}

//--------		Vérifier si le poste demandé appartient à un groupe, si oui, on recherche les personnes qualifiées pour ce groupe (poste=groupe) --------//
function groupe($poste){
  $db=new db();
  $db->query("SELECT `groupe_id` FROM `{$GLOBALS['config']['dbprefix']}postes` WHERE `id`='$poste';");
  if($db->result and $db->result[0]['groupe_id']!=0)
    $poste=$db->result[0]['groupe_id'];
  return $poste;
}
//--------		FIN Vérifier si le poste demandé appartient à un groupe, si oui, on recherche les personnes qualifiées pour ce groupe (poste=groupe) ---------//


// Vérifie si la ligne du tableau correspondant au poste $poste est vide ou non
function isAnEmptyLine($poste){
  if(!$poste){
    return false;
  }
  foreach($GLOBALS['cellules'] as $elem){
    if($poste==$elem['poste']){
      return false;
    }
  }
  return true;
}


//		-------------	paramétrage de la largeur des colonnes		--------------//
function nb30($debut,$fin){
  $tmpFin=explode(":",$fin);
  $tmpDebut=explode(":",$debut);
  $time=(($tmpFin[0]*60)+$tmpFin[1]-($tmpDebut[0]*60)-$tmpDebut[1])/30;
  return $time;
}
//		-------------	FIN paramétrage de la largeur des colonnes		--------------//
?>