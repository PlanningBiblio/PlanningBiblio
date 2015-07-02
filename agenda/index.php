<?php
/*
Planning Biblio, Version 2.0
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : agenda/index.php
Création : mai 2011
Dernière modification : 2 juillet 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche l'agenda d'un agent entre 2 dates
Par défaut, la semaine courante de l'agent connecté est affiché

Page appelée par la page index.php
*/

// pas de $version=acces direct au fichier => Accès refusé
if(!isset($version)){
  include_once "../include/accessDenied.php";
}

// Includes
include "joursFeries/class.joursFeries.php";

//	Initialisation des variables
$debut=filter_input(INPUT_GET,"debut",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$fin=filter_input(INPUT_GET,"fin",FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));

if(!array_key_exists('agenda_debut',$_SESSION)){
  $_SESSION['agenda_debut']=null;
  $_SESSION['agenda_fin']=null;
  $_SESSION['agenda_perso_id']=$_SESSION['login_id'];
}

$debut=$debut?$debut:$_SESSION['agenda_debut'];
$fin=$fin?$fin:$_SESSION['agenda_fin'];

$admin=in_array(3,$droits)?true:false;
if($admin){
  $perso_id=filter_input(INPUT_GET,"perso_id",FILTER_SANITIZE_NUMBER_INT);
  $perso_id=$perso_id?$perso_id:$_SESSION['agenda_perso_id'];
}
else{
  $perso_id=$_SESSION['agenda_perso_id'];
}
$d=new datePl(date("Y-m-d"));
$debutSQL=$debut?dateSQL($debut):$d->dates[0];	// lundi de la semaine courante
$debut=dateFr3($debutSQL);
$finSQL=$fin?dateSQL($fin):$d->dates[6];	// dimance de la semaine courante
$fin=dateFr3($finSQL);
$_SESSION['agenda_debut']=$debut;
$_SESSION['agenda_fin']=$fin;
$_SESSION['agenda_perso_id']=$perso_id;
$class=null;

//	Sélection du personnel pour le menu déroulant
$toutlemonde=$config['toutlemonde']?null:" AND id<>2 ";
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}personnel` WHERE actif='Actif' $toutlemonde ORDER BY `nom`,`prenom`;");
$agents=$db->result;
if(is_array($agents)){
  foreach($agents as $elem){
    if($elem['id']==$perso_id){
      $agent=$elem['nom']." ".$elem['prenom'];
      break;
    }
  }
}

// Jours fériés
$j=new joursFeries();
$j->debut=$debutSQL;
$j->fin=$finSQL;
$j->index="date";
$j->fetch();
$joursFeries=$j->elements;

// Affichage
echo "<div id='div_agenda'>\n";
if(isset($agent)){
  echo "<h3>Agenda de $agent du $debut au $fin</h3>\n";
}
else{
  echo "<h3>Agenda</h3>\n";
}
if(is_array($agents)){
  echo "<form name='form' method='get' action='index.php'>\n";
  echo "<input type='hidden' name='page' value='agenda/index.php' />\n";
  echo "Début : <input type='text' name='debut' id='debut' value='$debut' class='datepicker'/>\n";
  echo "&nbsp;&nbsp;Fin : <input type='text' name='fin' value='$fin' class='datepicker'/>\n";
  if($admin){
    echo "&nbsp;&nbsp;Agent : \n";
    echo "<select name='perso_id' class='ui-widget-content ui-corner-all'>\n";
    foreach($agents as $elem){
      $selected=$elem['id']==$perso_id?"selected='selected'":null;
      echo "<option value='{$elem['id']}' $selected >{$elem['nom']} {$elem['prenom']}</option>\n";
    }
    echo "</select>\n";
  }
  echo "&nbsp;&nbsp;<input type='submit' value='OK' class='ui-button'/>\n";
  echo "</form>\n";
}
else{
  echo "Aucun agent disponible";
}

if(!isset($agent)){
  echo "<br/><br/><br/><br/>";
  include "include/footer.php";
  exit;
}

//	Selection des horaires de travail
$db=new db();
$db->select2("personnel","temps",array("id"=>$perso_id));
$temps=unserialize($db->result[0]['temps']);		//	$temps = emploi du temps

//	Selection des absences
$filter=$config['Absences-validation']?"AND `valide`>0":null;
$db=new db();
$db->select("absences",null,"`perso_id`='$perso_id' $filter ");
$absences=$db->result;					//	$absences = tableau d'absences
	
//	Selection des postes occupés
$db=new db();
$perso_id=$db->escapeString($perso_id);
$debutREQ=$db->escapeString($debutSQL);
$finREQ=$db->escapeString($finSQL);

$requete="SELECT pl_poste.`date` AS `date`, pl_poste.debut AS debut, pl_poste.fin AS fin, pl_poste.absent AS absent, 
  postes.nom as poste FROM pl_poste INNER JOIN postes on pl_poste.poste=postes.id WHERE pl_poste.perso_id='$perso_id' 
  and `date`>='$debutREQ' and `date`<='$finREQ' order by `date`,`debut`,`fin`;";
$requete=str_replace("pl_poste","`{$dbprefix}pl_poste`",$requete);
$requete=str_replace("postes","`{$dbprefix}postes`",$requete);
$db->query($requete);
$postes=$db->result;

if($debutSQL>$finSQL){
  echo "<p class='information'>La date de fin doit &ecirc;tre sup&eacute;rieure &agrave; la date de d&eacute;but.</p>\n";
}else{
  echo <<<EOD
    <br/>
    <table cellpadding='10' cellspacing='2' border='0' id='tab_agenda'>
    <thead>
    <tr class='center'>
    <th class='ui-widget-header ui-corner-all'>Lundi</th>
    <th class='ui-widget-header ui-corner-all'>Mardi</th>
    <th class='ui-widget-header ui-corner-all'>Mercredi</th>
    <th class='ui-widget-header ui-corner-all'>Jeudi</th>
    <th class='ui-widget-header ui-corner-all'>Vendredi</th>
    <th class='ui-widget-header ui-corner-all'>Samedi</th>
    <th class='ui-widget-header ui-corner-all'>Dimanche</th>
    </tr>
    </thead>
    <tbody>
    <tr>
EOD;

  // Affiche des cellules vides devant le premier jour demandé de façon à avoir les lundis dans la première colonne
  $d=new datePl($debutSQL);
  $cellsBefore=$d->position>0?$d->position-1:6;
  for($i=0;$i<$cellsBefore;$i++){
    echo "<td class='cellule_grise'>&nbsp;</td>\n";
  }
  $nb=$cellsBefore;

  $current=$debutSQL;
  while($current<=$finSQL){
    $current_postes=array();
    $date_tab=explode("-",$current);
    $date_aff=dateAlpha($current,false,false);
    $semaine=date("W",strtotime($current));
    $jour=date("w",strtotime($current))-1;
    if($jour<0)
      $jour=6;
    if($config['nb_semaine']==2 and $semaine%2==0)
      $jour=$jour+7;

    //	Horaires de traval si le module PlanningHebdo est activé
    if($config['PlanningHebdo']){
      include_once "planningHebdo/class.planningHebdo.php";
      $p=new planningHebdo();
      $p->perso_id=$perso_id;
      $p->debut=$current;
      $p->fin=$current;
      $p->valide=true;
      $p->fetch();

      if(empty($p->elements)){
	$temps=array();
      }
      else{  
	$temps=$p->elements[0]['temps'];
      }
    }

    $horaires=null;
    if(is_array($temps) and array_key_exists($jour,$temps)){
      $horaires=$temps[$jour];
    }

    $d=new datePl($current);
    $current_date=ucfirst($d->jour_complet);
    if(is_array($postes))
    foreach($postes as $elem){
      if($elem['date']==$current){
	$current_postes[]=$elem;
      }
    }
    $current_abs=array();
    if(is_array($absences))
    foreach($absences as $elem){
      $abs_deb=substr($elem['debut'],0,10);
      $abs_fin=substr($elem['fin'],0,10);
      if(($abs_deb<$current and $abs_fin>$current) or $abs_deb==$current or $abs_fin==$current){
	$current_abs[]=$elem;
      }
    }
    if(($nb++)%7==0){
      echo "</tr><tr>\n";
    }
    echo "<td>";
    echo "<div class='div_date'>$date_aff</div>\n";

    // Jours fériés : affiche Bibliothèque fermée et passe au jour suivant
    if(array_key_exists($current,$joursFeries) and $joursFeries[$current]['fermeture']){
      echo "<div class='ferie'>\n";
      echo "Biblioth&egrave;que ferm&eacute;e<br/><b>{$joursFeries[$current]['nom']}</b>";
      echo "</div></td>\n";
      $current=date("Y-m-d",mktime(0,0,0,$date_tab[1],$date_tab[2]+1,$date_tab[0]));
      if(($nb)%7==0){
	echo "</tr>\n";
      }
      continue;
    }

    // Si l'agent est absent : affiche s'il est abent toutes la journée ou ses heures d'absences
    $absent=false;
    $absences_affichage=array();

    foreach($current_abs as $elem){
      if($elem['debut']<=$current." 00:00:00" and $elem['fin']>=$current." 23:59:59"){
	$absent=true;
	$absences_affichage[]="Toute la journée : ".$elem['motif'];
      }
      elseif(substr($elem['debut'],0,10)==$current and substr($elem['fin'],0,10)==$current){
	$deb=heure2(substr($elem['debut'],-8));
	$fi=heure2(substr($elem['fin'],-8));
	$absences_affichage[]="De $deb &agrave; $fi : ".$elem['motif'];
      }
      elseif(substr($elem['debut'],0,10)==$current and $elem['fin']>=$current." 23:59:59"){
	$deb=heure2(substr($elem['debut'],-8));
	$absences_affichage[]="&Agrave; partir de $deb : ".$elem['motif'];
      }
      elseif($elem['debut']<=$current." 00:00:00" and substr($elem['fin'],0,10)==$current){
	$fi=heure2(substr($elem['fin'],-8));
	$absences_affichage[]="Jusqu'&agrave; $fi : ".$elem['motif'];
      }
      else{
	$absences_affichage[]="{$elem['debut']} &rarr; {$elem['fin']} : {$elem['motif']}";
      }
    }

    // Intégration des congés
    if(in_array("conges",$plugins)){
      include "plugins/conges/agenda.php";
    }


    // Si l'agent n'est pas absent toute la journée : affiche ses heures de présences
    if(!$absent){
      $site=null;
      if($config['Multisites-nombre']>1 and isset($horaires[4])){
	if($horaires[4]){
	  $site="&agrave; ".$config['Multisites-site'.$horaires[4]];
	}
      }

      $horaire="";
      if($horaires[0])
	$horaire="Pr&eacute;sent(e) $site de ".heure2($horaires[0])." &agrave; ";
      if($horaires[1])
	$horaire.=heure2($horaires[1]);
      if($horaires[1] and $horaires[2])
	$horaire.=" et de ";
      if($horaires[2])
	$horaire.=heure2($horaires[2])." &agrave; ";
      if($horaires[3])
	$horaire.=heure2($horaires[3]);

      echo "<p>$horaire</p>\n";
    }

    // Affichage des absences
    if(!empty($absences_affichage)){
      echo "<div class='important'>\n";
      echo count($absences_affichage)==1?"Absence :":"Absences :";
      echo "<ul>\n";
      foreach($absences_affichage as $elem){
	echo "<li>$elem</li>\n";
      }
      echo "</ul></div>\n";
    }

    if(!empty($current_postes)){
      echo "<div class='postes'>Postes occup&eacute;s :<ul>\n";
      foreach($current_postes as $elem){
	$heure=heure2($elem['debut'])." - ".heure2($elem['fin']);
	$barre=$elem['absent']?"text-decoration:line-through;":null;
	$class=$elem['absent']?"important":null;
	echo "<li style='$barre' class='$class'>$heure {$elem['poste']}</li>\n";
      }
    echo "</ul></div>\n";
    }
	    
    $current=date("Y-m-d",mktime(0,0,0,$date_tab[1],$date_tab[2]+1,$date_tab[0]));
    if(($nb)%7==0)
      echo "</tr>\n";
  }		

  // Cellules vides à la fin pour aller jusqu'au dimanche
  $d=new datePl($finSQL);
  $cellsAfter=$d->position>0?7-$d->position:0;
  for($i=0;$i<$cellsAfter;$i++){
    echo "<td class='cellule_grise'>&nbsp;</td>\n";
  }
  echo "</tr>\n";
  echo "</tbody>\n";
  echo "</table>\n";
}
echo "</div>";
?>
