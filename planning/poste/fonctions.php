<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/poste/fonctions.php
Création : mai 2011
Dernière modification : 18 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Fonctions utilisées par les pages des dossiers planning/poste et planning/postes_cgf
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../../index.php");
}

function cellule_poste($debut,$fin,$colspan,$output,$poste,$categorie){
  $resultats=array(null,null);
  $classe=array(null,null);
  $i=0;
  
  if($GLOBALS['cellules']){
    foreach($GLOBALS['cellules'] as $elem){
      if($elem['poste']==$poste and $elem['debut']==$debut and $elem['fin']==$fin){
	//		Affichage du nom et du prénom
	$resultat=$elem['nom'];
	if($elem['prenom'])
	  $resultat.=" ".substr($elem['prenom'],0,1).".";
	//		Affichage des sans repas
	if($GLOBALS['config']['Planning-sansRepas']){
	  if($debut>="11:30:00" and $fin<="14:30:00"){
	    $sr=0;
	    foreach($GLOBALS['cellules'] as $elem2){
	      if($elem2['debut']>="11:30:00" and $elem2['fin']<="14:30:00" and $elem2['perso_id']==$elem['perso_id']){
		$sr++;
	      }
	    }
	    if($sr>1){
	      $resultat.="<label class='sansRepas'> (SR)</label>";
	    }
	  }
	}
	//		On barre les absents
	if($elem['absent'] or $elem['supprime']){
	  $resultat="<s style='color:red;'>$resultat</s>";
	}
	//		On barre les congés
	if(in_array("conges",$GLOBALS['plugins'])){
	  include "plugins/conges/planning_cellule_poste.php";
	}
	  
	// Classe en fonction du statut et du service
	$class_tmp=array();
	if($elem['statut']){
	  $class_tmp[]="statut_".strtolower(removeAccents(str_replace(" ","_",$elem['statut'])));
	}
	if($elem['service']){
	  $class_tmp[]="service_".strtolower(removeAccents(str_replace(" ","_",$elem['service'])));
	}
	$classe[$i]=empty($class_tmp)?null:join(" ",$class_tmp);

	$resultats[$i]=$resultat;
	$i++;
      }
    }
  }
  $GLOBALS['idCellule']++;
  $classTD=$resultats[1]?null:$classe[0];
  $classCellule=$resultats[1]?"demi_cellule":"cellule";
  $cellule="<td id='td{$GLOBALS['idCellule']}' colspan='$colspan' style='text-align:center;' oncontextmenu='debut=\"$debut\";fin=\"$fin\";poste=\"$poste\";output=\"$output\";categorie=\"$categorie\";cellule={$GLOBALS['idCellule']}' class='$classTD' >";
  $cellule.="<div id='cellule{$GLOBALS['idCellule']}' class='$classCellule {$classe[0]}' >{$resultats[0]}</div>";
  $cellule.="<div id='cellule{$GLOBALS['idCellule']}b' class='$classCellule {$classe[1]}' >{$resultats[1]}</div>";
  $cellule.="</td>\n";
  return $cellule;
}

function deja_place($date,$poste){
  $deja=array(0);
  $req="SELECT `perso_id` FROM `{$GLOBALS['config']['dbprefix']}pl_poste` WHERE `date`='$date' AND `absent`='0' AND `poste`='$poste' GROUP BY `perso_id`;";
  $db=new db();
  $db->query($req);
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

//		-------------	paramétrage de la largeur des colonnes		--------------//
function nb30($debut,$fin){
  $tmpFin=explode(":",$fin);
  $tmpDebut=explode(":",$debut);
  $time=(($tmpFin[0]*60)+$tmpFin[1]-($tmpDebut[0]*60)-$tmpDebut[1])/30;
  return $time;
}
//		-------------	FIN paramétrage de la largeur des colonnes		--------------//
?>