<?php
/*
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/class.absences.php
Création : mai 2011
Dernière modification : 2 septembre 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Classe absences : contient les fonctions de recherches des absences

Page appelée par les autres pages du dossier absences
*/

// pas de $version=acces direct aux pages de ce dossier => Accès refusé
if(!isset($version)){
  include_once "../include/accessDenied.php";
}

class absences{
  public $agents_supprimes=array(0);
  public $debut=null;
  public $edt=array();
  public $elements=array();
  public $error=false;
  public $fin=null;
  public $heures=0;
  public $heures2=null;
  public $ignoreFermeture=false;
  public $minutes=0;
  public $perso_id=null;
  public $recipients=array();
  public $valide=false;

  public function absences(){
  }


  /**
  * @function calculHeuresAbsences
  * @param date string, date de début au format YYYY-MM-DD
  * Calcule les heures d'absences des agents pour la semaine définie par $date ($date = une date de la semaine)
  * Utilisée par planning::menudivAfficheAgent pour ajuster le nombre d'heure de SP à effectuer en fonction des absences
  */
  public function calculHeuresAbsences($date){
    $config=$GLOBALS['config'];
    $version=$GLOBALS['version'];
    $path=strpos($_SERVER['SCRIPT_NAME'],"planning/poste/ajax")?"../../":null;
    require_once "{$path}include/horaires.php";
    require_once "{$path}personnel/class.personnel.php";
    require_once "{$path}planningHebdo/class.planningHebdo.php";

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
      $tmp=array();
      foreach($heures as $key => $value){
	$tmp[(int) $key] = $value;
      }
      $heures=$tmp;
    }


    // Vérifie si la table absences a été mise à jour depuis le dernier calcul
    $aUpdate=strtotime($this->update_time());

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

      // Recherche des agents pour appliquer le pourcentage sur les heures d'absences en fonction du taux de SP
      $p=new personnel();
      $p->fetch();
      $agents=$p->elements;
      
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

	  // On applique le pourcentage
	  if(strpos($agents[$perso_id]["heuresHebdo"],"%")){
	    $pourcent=(float) str_replace("%",null,$agents[$perso_id]["heuresHebdo"]);
	    $heures[$perso_id]=$heures[$perso_id]*$pourcent/100;
	  }
	}
      }

      // Enregistrement des heures dans la base de données
      $db=new db();
      $db->delete2("heures_Absences",array("semaine"=>$j1));
      $db=new db();
      $db->insert2("heures_Absences",array("semaine"=>$j1,"update_time"=>time(),"heures"=>json_encode($heures)));
    }

    return (array) $heures;
  }
  
  
  
  /**
  * @function calculTemps
  * @param debut string, date de début au format YYYY-MM-DD [H:i:s]
  * @param fin string, date de fin au format YYYY-MM-DD [H:i:s]
  * @param perso_id int, id de l'agent
  * Calcule le temps de travail d'un agent entre 2 dates.
  * Utilisé pour calculer le nombre d'heures correspondant à une absence
  * Ne calcule pas le temps correspondant aux jours de fermeture
  */
  public function calculTemps($debut,$fin,$perso_id){
    $version=$GLOBALS['config']['Version'];

    $path=strpos($_SERVER['SCRIPT_NAME'],"planning/poste/ajax")?"../../":null;
    require_once "{$path}joursFeries/class.joursFeries.php";

    $hre_debut=substr($debut,-8);
    $hre_fin=substr($fin,-8);
    $hre_fin=$hre_fin=="00:00:00"?"23:59:59":$hre_fin;
    $debut=substr($debut,0,10);
    $fin=substr($fin,0,10);

    // Calcul du nombre d'heures correspondant à une absence
    $current=$debut;
    $difference=0;

    // Pour chaque date
    while($current<=$fin){

      // On ignore les jours de fermeture
      $j=new joursFeries();
      $j->fetchByDate($current);
      if(!empty($j->elements)){
	foreach($j->elements as $elem){
	  if($elem['fermeture']){
	    $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
	    continue 2;
	  }
	}
      }

      // On consulte le planning de présence de l'agent
      // On ne calcule pas les heures si le module planningHebdo n'est pas activé, le calcul serait faux si les emplois du temps avaient changé
      if(!$GLOBALS['config']['PlanningHebdo']){
	$this->error=true;
	$this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
	return false;
      }

      // On consulte le planning de présence de l'agent
      if($GLOBALS['config']['PlanningHebdo']){
	require_once "{$path}planningHebdo/class.planningHebdo.php";

	$p=new planningHebdo();
	$p->perso_id=$perso_id;
	$p->debut=$current;
	$p->fin=$current;
	$p->valide=true;
	$p->fetch();
	// Si le planning n'est pas validé pour l'une des dates, on retourne un message d'erreur et on arrête le calcul
	if(empty($p->elements)){
	  $this->error=true;
	  $this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
	  return false;
	}

	// Sinon, on calcule les heures d'absence
	$d=new datePl($current);
	$semaine=$d->semaine3;
	$jour=$d->position?$d->position:7;
	$jour=$jour+(($semaine-1)*7)-1;
	$temps=null;
	if(array_key_exists($jour,$p->elements[0]['temps'])){
	  $temps=$p->elements[0]['temps'][$jour];
	}
      }

      if($temps){
	$temps[0]=strtotime($temps[0]);
	$temps[1]=strtotime($temps[1]);
	$temps[2]=strtotime($temps[2]);
	$temps[3]=strtotime($temps[3]);
	$debutAbsence=$current==$debut?$hre_debut:"00:00:00";
	$finAbsence=$current==$fin?$hre_fin:"23:59:59";
	$debutAbsence=strtotime($debutAbsence);
	$finAbsence=strtotime($finAbsence);


	// Calcul du temps du matin
	if($temps[0] and $temps[1]){
	  $debutAbsence1=$debutAbsence>$temps[0]?$debutAbsence:$temps[0];
	  $finAbsence1=$finAbsence<$temps[1]?$finAbsence:$temps[1];
	  if($finAbsence1>$debutAbsence1){
	    $difference+=$finAbsence1-$debutAbsence1;
	  }
	}

	// Calcul du temps de l'après-midi
	if($temps[2] and $temps[3]){
	  $debutAbsence2=$debutAbsence>$temps[2]?$debutAbsence:$temps[2];
	  $finAbsence2=$finAbsence<$temps[3]?$finAbsence:$temps[3];
	  if($finAbsence2>$debutAbsence2){
	    $difference+=$finAbsence2-$debutAbsence2;
	  }
	}

	// Calcul du temps de la journée s'il n'y a pas de pause le midi
	if($temps[0] and $temps[3] and !$temps[1] and !$temps[2]){
	  $debutAbsence=$debutAbsence>$temps[0]?$debutAbsence:$temps[0];
	  $finAbsence=$finAbsence<$temps[3]?$finAbsence:$temps[3];
	  if($finAbsence>$debutAbsence){
	    $difference+=$finAbsence-$debutAbsence;
	  }
	}
      }

      $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
    }

    $this->minutes=$difference/60;
    $this->heures=$difference/3600;
    $this->heures2=str_replace(array(".00",".25",".50",".75"),array("h00","h15","h30","h45"),number_format($this->heures, 2, '.', ' '));
  }

  /**
  * @function calculTemps2
  * @param debut string, date de début au format YYYY-MM-DD [H:i:s]
  * @param fin string, date de fin au format YYYY-MM-DD [H:i:s]
  * @param edt array, tableau contenant les emplois du temps des agents
  * @param perso_id int, id de l'agent
  * @param ignoreFermeture boolean, default=false : ignorer les jours de fermeture
  * Calcule le temps de travail d'un agents entre 2 dates.
  * Utilisé pour calculer le nombre d'heures correspondant à une absence
  * Les heures de présences sont données en paramètre dans un tableau. Offre de meilleurs performance que la fonction calculTemps 
  * lorsqu'elle est executée pour plusieurs agents
  */
  public function calculTemps2(){
    $version=$GLOBALS['config']['Version'];

    $path=strpos($_SERVER['SCRIPT_NAME'],"planning/poste/ajax")?"../../":null;
    require_once "{$path}joursFeries/class.joursFeries.php";

    $debut=$this->debut;
    $edt=$this->edt;
    $fin=$this->fin;
    $perso_id=$this->perso_id;

    $hre_debut=substr($debut,-8);
    $hre_fin=substr($fin,-8);
    $hre_fin=$hre_fin=="00:00:00"?"23:59:59":$hre_fin;
    $debut=substr($debut,0,10);
    $fin=substr($fin,0,10);

    // Calcul du nombre d'heures correspondant à une absence
    $current=$debut;
    $difference=0;

    // Pour chaque date
    while($current<=$fin){
      // On ignore les jours de fermeture
      if(!$this->ignoreFermeture){
	$j=new joursFeries();
	$j->fetchByDate($current);
	if(!empty($j->elements)){
	  foreach($j->elements as $elem){
	    if($elem['fermeture']){
	      $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
	      continue 2;
	    }
	  }
	}
      }

      // On consulte le planning de présence de l'agent
      // On ne calcule pas les heures si le module planningHebdo n'est pas activé, le calcul serait faux si les emplois du temps avaient changé
      if(!$GLOBALS['config']['PlanningHebdo']){
	$this->error=true;
	$this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
	$this->minutes="N/A";
	$this->heures="N/A";
	$this->heures2="N/A";
	return false;
      }

      // On consulte le planning de présence de l'agent
      if($GLOBALS['config']['PlanningHebdo']){
	require_once "{$path}planningHebdo/class.planningHebdo.php";

	$edt=array();
	if($this->edt and !empty($this->edt)){
	  foreach($this->edt as $elem){
	    if($elem['perso_id'] == $perso_id){
	      $edt=$elem;
	      break;
	    }
	  }
	}

	// Si le planning n'est pas validé pour l'une des dates, on retourne un message d'erreur et on arrête le calcul
	if(empty($edt)){
	  $this->error=true;
	  $this->message="Impossible de déterminer le nombre d'heures correspondant aux congés demandés.";
	  $this->minutes="N/A";
	  $this->heures="N/A";
	  $this->heures2="N/A";
	  return false;
	}

	// Sinon, on calcule les heures d'absence
	$d=new datePl($current);
	$semaine=$d->semaine3;
	$jour=$d->position?$d->position:7;
	$jour=$jour+(($semaine-1)*7)-1;
	$temps=null;
	if(array_key_exists($jour,$edt['temps'])){
	  $temps=$edt['temps'][$jour];
	}
      }

      if($temps){
	$temps[0]=strtotime($temps[0]);
	$temps[1]=strtotime($temps[1]);
	$temps[2]=strtotime($temps[2]);
	$temps[3]=strtotime($temps[3]);
	$debutAbsence=$current==$debut?$hre_debut:"00:00:00";
	$finAbsence=$current==$fin?$hre_fin:"23:59:59";
	$debutAbsence=strtotime($debutAbsence);
	$finAbsence=strtotime($finAbsence);


	// Calcul du temps du matin
	if($temps[0] and $temps[1]){
	  $debutAbsence1=$debutAbsence>$temps[0]?$debutAbsence:$temps[0];
	  $finAbsence1=$finAbsence<$temps[1]?$finAbsence:$temps[1];
	  if($finAbsence1>$debutAbsence1){
	    $difference+=$finAbsence1-$debutAbsence1;
	  }
	}

	// Calcul du temps de l'après-midi
	if($temps[2] and $temps[3]){
	  $debutAbsence2=$debutAbsence>$temps[2]?$debutAbsence:$temps[2];
	  $finAbsence2=$finAbsence<$temps[3]?$finAbsence:$temps[3];
	  if($finAbsence2>$debutAbsence2){
	    $difference+=$finAbsence2-$debutAbsence2;
	  }
	}

	// Calcul du temps de la journée s'il n'y a pas de pause le midi
	if($temps[0] and $temps[3] and !$temps[1] and !$temps[2]){
	  $debutAbsence=$debutAbsence>$temps[0]?$debutAbsence:$temps[0];
	  $finAbsence=$finAbsence<$temps[3]?$finAbsence:$temps[3];
	  if($finAbsence>$debutAbsence){
	    $difference+=$finAbsence-$debutAbsence;
	  }
	}
      }

      $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
    }

    $this->minutes=$difference/60;
    $this->heures=$difference/3600;
    $this->heures2=str_replace(array(".00",".25",".50",".75"),array("h00","h15","h30","h45"),number_format($this->heures, 2, '.', ' '));
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


    // N'affiche que les absences des agents non supprimés par défaut : $this->agents_supprimes=array(0);
    // Affiche les absences des agents supprimés si précisé : $this->agents_supprimes=array(0,1) ou array(0,1,2)
    $deletedAgents=join("','",$this->agents_supprimes);
    $filter.=" AND `{$dbprefix}personnel`.`supprime` IN ('$deletedAgents') ";

    if($agent==0){
      $agent=null;
    }

    if(is_numeric($agent)){
      $filter.=" AND `{$dbprefix}personnel`.`id`='$agent' ";
      $agent=null;
    }

    // Sort
    $sort=$sort?$sort:"`debut`,`fin`,`nom`,`prenom`";

    //	Select All
    $req="SELECT `{$dbprefix}personnel`.`nom` AS `nom`, `{$dbprefix}personnel`.`prenom` AS `prenom`, "
      ."`{$dbprefix}personnel`.`id` AS `perso_id`, "
      ."`{$dbprefix}absences`.`id` AS `id`, `{$dbprefix}absences`.`debut` AS `debut`, "
      ."`{$dbprefix}absences`.`fin` AS `fin`, `{$dbprefix}absences`.`nbjours` AS `nbjours`, "
      ."`{$dbprefix}absences`.`motif` AS `motif`, `{$dbprefix}absences`.`commentaires` AS `commentaires`, "
      ."`{$dbprefix}absences`.`valide` AS `valide`, `{$dbprefix}absences`.`validation` AS `validation`, "
      ."`{$dbprefix}absences`.`valideN1` AS `valideN1`, `{$dbprefix}absences`.`validationN1` AS `validationN1`, "
      ."`{$dbprefix}absences`.`pj1` AS `pj1`, `{$dbprefix}absences`.`pj2` AS `pj2`, `{$dbprefix}absences`.`so` AS `so`, "
      ."`{$dbprefix}absences`.`demande` AS `demande` "
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
    $db=new db();
    $db->selectInnerJoin(array("absences","perso_id"),array("personnel","id"),
      array("id","debut","fin","nbjours","motif","motif_autre","commentaires","valideN1","validationN1","pj1","pj2","so","demande",
      array("name"=>"valide","as"=>"valideN2"),array("name"=>"validation","as"=>"validationN2")),
      array("nom","prenom","sites",array("name"=>"id","as"=>"perso_id"),"mail","mailsResponsables"),
      array("id"=>$id));

    if($db->result){
      $elem=$db->result[0];
      $elem['mailsResponsables']=explode(";",html_entity_decode($elem['mailsResponsables'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
      $this->elements=$elem;
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
	// Emploi du temps si module planningHebdo activé
	if($config['PlanningHebdo']){
	  include_once "planningHebdo/class.planningHebdo.php";
	  $p=new planningHebdo();
	  $p->perso_id=$perso_id;
	  $p->debut=$date;
	  $p->fin=$date;
	  $p->valide=true;
	  $p->fetch();

	  if(empty($p->elements)){
	    $temps=array();
	  }
	  else{  
	    $temps=$p->elements[0]['temps'];
	  }
	}
	// Vérifions le numéro de la semaine de façon à contrôler le bon planning de présence hebdomadaire
	$d=new datePl($date);
	$jour=$d->position?$d->position:7;
	$semaine=$d->semaine3;
	// Récupération du numéro du site concerné par la date courante
	$j=$jour-1+($semaine*7)-7;
	$site=null;
	if(is_array($temps)){
	  if(array_key_exists($j,$temps) and array_key_exists(4,$temps[$j])){
	    $site=$temps[$j][4];
	  }
	}
	// Ajout du numéro du droit correspondant à la gestion des absences de ce site
	if(!in_array("20".$site,$droitsAbsences) and $site){
	  $droitsAbsences[]="20".$site;
	}
	$date=date("Y-m-d",strtotime("+1 day",strtotime($date)));
      }
      // Si les jours d'absences ne concernent aucun site, on ajoute les responsables des 2 sites par sécurité
      if(empty($droitsAbsences)){
	for($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++){
	  $droitsAbsences[]=200+$i;
	}
      }
    }
    // Si un seul site, le droit de gestion des absences est 1
    else{
      $droitsAbsences[]=1;
    }

    $db=new db();
    $db->select("personnel",null,"supprime='0'");
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

  public function getRecipients($validation,$responsables,$mail,$mailsResponsables){
    /*
    Retourne la liste des destinataires des notifications en fonction du niveau de validation.
    $validation = niveau de validation (int) :
      1 : enregistrement d'une nouvelle absences
      2 : modification d'une absence sans validation ou suppression
      3 : validation N1
      4 : validation N2
    $responsables : listes des agents (array) ayant le droit de gérer les absences
    $mail : mail de l'agent concerné par l'absence
    $mailsResponsables : mails de ses responsables (tableau)
    */

    $categories=$GLOBALS['config']["Absences-notifications{$validation}"];
    $categories=unserialize(stripslashes($categories));
    /*
    $categories : Catégories de personnes à qui les notifications doivent être envoyées
      tableau sérialisé issu de la config. : champ Absences-notifications, Absences-notifications2, 
      Absences-notifications3, Absences-notifications4, en fonction du niveau de validation ($validation)
      Valeurs du tableau : 
	0 : agents ayant le droits de gérer les absences
	1 : responsables directs (mails enregistrés dans la fiche des agents)
	2 : cellule planning (mails enregistrés dans la config.)
	3 : l'agent
    */

    // recipients : liste des mails qui sera retournée
    $recipients=array();

    // Agents ayant le droits de gérer les absences
    if(in_array(0,$categories)){
      foreach($responsables as $elem){
	if(!in_array(trim(html_entity_decode($elem['mail'],ENT_QUOTES|ENT_IGNORE,"UTF-8")),$recipients)){
	  $recipients[]=trim(html_entity_decode($elem['mail'],ENT_QUOTES|ENT_IGNORE,"UTF-8"));
	}
      }
    }

    // Responsables directs
    if(in_array(1,$categories)){
      if(is_array($mailsResponsables)){
	foreach($mailsResponsables as $elem){
	  if(!in_array(trim(html_entity_decode($elem,ENT_QUOTES|ENT_IGNORE,"UTF-8")),$recipients)){
	    $recipients[]=trim(html_entity_decode($elem,ENT_QUOTES|ENT_IGNORE,"UTF-8"));
	  }
	}
      }
    }

    // Cellule planning
    if(in_array(2,$categories)){
      $mailsCellule=explode(";",trim($GLOBALS['config']['Mail-Planning']));
      if(is_array($mailsCellule)){
	foreach($mailsCellule as $elem){
	  if(!in_array(trim(html_entity_decode($elem,ENT_QUOTES|ENT_IGNORE,"UTF-8")),$recipients)){
	    $recipients[]=trim(html_entity_decode($elem,ENT_QUOTES|ENT_IGNORE,"UTF-8"));
	  }
	}
      }
    }

    // L'agent
    if(in_array(3,$categories)){
      if(!in_array(trim(html_entity_decode($mail,ENT_QUOTES|ENT_IGNORE,"UTF-8")),$recipients)){
	$recipients[]=trim(html_entity_decode($mail,ENT_QUOTES|ENT_IGNORE,"UTF-8"));
      }
    }

    $this->recipients=$recipients;
  }

  function piecesJustif($id,$pj,$checked){
    $db=new db();
    $db->update2("absences",array($pj => $checked),array("id"=>$id));
  }

  public function update_time(){
    $db=new db();
    $db->query("show table status from {$GLOBALS['config']['dbname']} like '{$GLOBALS['dbprefix']}absences';");
    return $db->result[0]['Update_time'];
  }

}
?>