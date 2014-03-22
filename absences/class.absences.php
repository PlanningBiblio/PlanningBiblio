<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : absences/class.absences.php
Création : mai 2011
Dernière modification : 21 mars 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe absences : contient les fonctions de recherches des absences

Page appelée par les autres pages du dossier absences
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

class absences{
  public $elements=array();
  public $valide=false;

  public function absences(){
  }

  public function fetch($sort="`debut`,`fin`,`nom`,`prenom`",$only_me=null,$agent=null,$debut=null,$fin=null,$sites=null){
    $filter="";
    //	DB prefix
    $dbprefix=$GLOBALS['config']['dbprefix'];
    // Date, debut, fin
    $date=date("Y-m-d");
    if($debut){
      $fin=$fin?$fin:$date;
      if(strlen($fin)==10){
	$fin=$fin." 23:59:59";
      }
      $dates="`debut`<='$fin' AND `fin`>='$debut'";
    }
    else{
      $dates="`fin`>='$date'";
    }

    // Multisites, filtre pour n'afficher que les agents des sites choisis
    $sites_req=null;
    if(!empty($sites)){
      $tmp=array();
      foreach($sites as $site){
	$tmp[]="`{$dbprefix}personnel`.`sites` LIKE '%\"$site\"%'";
      }
      if(!empty($tmp)){
	$sites_req.=" AND (".join(" OR ",$tmp).") ";
      }
    }

    if($this->valide and $GLOBALS['config']['Absences-validation']){
      $filter.=" AND `{$dbprefix}absences`.`valide`>0 ";
    }

    if($agent==0){
      $agent=null;
    }

    if(is_numeric($agent)){
      $filter.=" AND `{$dbprefix}personnel`.`id`='$agent' ";
      $agent=null;
    }

    //	Select All
    $req="SELECT `{$dbprefix}personnel`.`nom` AS `nom`, `{$dbprefix}personnel`.`prenom` AS `prenom`, "
      ."`{$dbprefix}personnel`.`id` AS `perso_id`, "
      ."`{$dbprefix}absences`.`id` AS `id`, `{$dbprefix}absences`.`debut` AS `debut`, "
      ."`{$dbprefix}absences`.`fin` AS `fin`, `{$dbprefix}absences`.`nbjours` AS `nbjours`, "
      ."`{$dbprefix}absences`.`motif` AS `motif`, `{$dbprefix}absences`.`commentaires` AS `commentaires`, "
      ."`{$dbprefix}absences`.`valide` AS `valide`, `{$dbprefix}absences`.`validation` AS `validation`, "
      ."`{$dbprefix}absences`.`valideN1` AS `valideN1`, `{$dbprefix}absences`.`validationN1` AS `validationN1` "
      ."FROM `{$dbprefix}absences` INNER JOIN `{$dbprefix}personnel` "
      ."ON `{$dbprefix}absences`.`perso_id`=`{$dbprefix}personnel`.`id` "
      ."WHERE $dates $only_me $sites_req $filter ORDER BY $sort;";
    $db=new db();
    $db->query($req);

    $all=array();
    if($db->result){
      foreach($db->result as $elem){
	$tmp=$elem;
	$debut=dateFr(substr($elem['debut'],0,10));
	$fin=dateFr(substr($elem['fin'],0,10));
	$debutHeure=substr($elem['debut'],-8);
	$finHeure=substr($elem['fin'],-8);
	if($debutHeure=="00:00:00" and $finHeure=="23:59:59"){
	  $debutHeure=null;
	  $finHeure=null;
	}
	else{
	  $debutHeure=heure2($debutHeure);
	  $finHeure=heure2($finHeure);
	}
	$tmp['debutAff']="$debut $debutHeure";
	$tmp['finAff']="$fin $finHeure";
	$all[]=$tmp;
      }
    }

    //	By default $result=$all
    $result=$all;
    //	If name, keep only matching results
    if(is_array($all) and $agent){
      $result=array();
      foreach($all as $elem){
	if(pl_stristr($elem['nom'],$agent) or pl_stristr($elem['prenom'],$agent)){
	  $result[]=$elem;
	}
      }
    }
    if($result){
      $this->elements=$result;
    }
  }

  public function fetchById($id){
    $dbprefix=$GLOBALS['config']['dbprefix'];
    $req="SELECT `{$dbprefix}personnel`.`nom` AS `nom`, `{$dbprefix}personnel`.`prenom` AS `prenom`, "
      ."`{$dbprefix}personnel`.`id` AS `perso_id`, `{$dbprefix}personnel`.`mail` AS `mail`,"
      ."`{$dbprefix}absences`.`id` AS `id`, `{$dbprefix}absences`.`debut` AS `debut`, "
      ."`{$dbprefix}absences`.`fin` AS `fin`, `{$dbprefix}absences`.`nbjours` AS `nbjours`, "
      ."`{$dbprefix}absences`.`motif` AS `motif`, `{$dbprefix}absences`.`commentaires` AS `commentaires` "
      ."FROM `{$dbprefix}absences` INNER JOIN `{$dbprefix}personnel` "
      ."ON `{$dbprefix}absences`.`perso_id`=`{$dbprefix}personnel`.`id` "
      ."WHERE `{$dbprefix}absences`.`id`='$id';";
    $db=new db();
    $db->query($req);
    if($db->result){
      $this->elements=$db->result[0];
    }
  }


  function getResponsables($debut=null,$fin=null,$perso_id){
    $responsables=array();
    $droitsAbsences=array();
    //	Si plusieurs sites et agents autorisés à travailler sur plusieurs sites, vérifions dans l'emploi du temps quels sont les sites concernés par l'absence
    if($GLOBALS['config']['Multisites-nombre']>1){
      $db=new db();
      $db->select("personnel","temps","id='$perso_id'");
      $temps=unserialize($db->result[0]['temps']);
      $date=$debut;
      while($date<=$fin){
	// Emploi du temps si plugin planningHebdo
	if(in_array("plannningHebdo",$GLOBALS['plugins'])){
	  include "plugins/planningHebdo/absences.php";
	}
	// Vérifions le numéro de la semaine de façon à contrôler le bon planning de présence hebdomadaire
	$d=new datePl($date);
	$jour=$d->position?$d->position:7;
	$semaine=$d->semaine3;
	// Récupération du numéro du site concerné par la date courante
	$j=$jour-1+($semaine*7)-7;
	$site=null;
	if(array_key_exists($j,$temps)){
	  $site=$temps[$j][4];
	}
	// Ajout du numéro du droit correspondant à la gestion des absences de ce site
	if(!in_array("20".$site,$droitsAbsences) and $site){
	  $droitsAbsences[]="20".$site;
	}
	$date=date("Y-m-d",strtotime("+1 day",strtotime($date)));
      }
      // Si les jours d'absences ne concernent aucun site, on ajoute les responsables des 2 sites par sécurité
      if(empty($droitsAbsences)){
	$droitsAbsences=array(201,202);
      }
    }
    // Si un seul site, le droit de gestion des absences est 1
    else{
      $droitsAbsences[]=1;
    }

    $db=new db();
    $db->select("personnel");
    foreach($db->result as $elem){
      $d=unserialize($elem['droits']);
      foreach($droitsAbsences as $elem2){
	if(is_array($d) and in_array($elem2,$d) and !in_array($elem,$responsables)){
	  $responsables[]=$elem;
	}
      }
    }
    $this->responsables=$responsables;
  }

}
?>