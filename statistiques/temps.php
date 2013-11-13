<?php
/*
Planning Biblio, Version 1.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : statistiques/temps.php
Création : mai 2011
Dernière modification : 13 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche un tableau avec le nombre d'heures de service public effectué par agent par jour et par semaine

Page appelée par le fichier index.php, accessible par le menu statistiques / Feuille de temps
*/

require_once "class.statistiques.php";

echo "<h3>Feuille de temps</h3>\n";

include "include/horaires.php";

//	Initialisation des variables
if(!array_key_exists('stat_temps_tri',$_SESSION)){
  $_SESSION['stat_temps_tri']=null;
}
if(isset($_GET['debut'])){
  $debut=$_GET['debut'];
  $fin=$_GET['fin']?$_GET['fin']:$debut;
}
elseif(array_key_exists("stat_temps_debut",$_SESSION['oups'])){
  $debut=$_SESSION['oups']['stat_temps_debut'];
  $fin=$_SESSION['oups']['stat_temps_fin'];
}
else{
  $date=$_SESSION['PLdate'];
  $d=new datePl($date);
  $debut=$d->dates[0];
  $fin=$config['Dimanche']?$d->dates[6]:$d->dates[5];
}
$_SESSION['oups']['stat_temps_debut']=$debut;
$_SESSION['oups']['stat_temps_fin']=$fin;

$current=$debut;
while($current<=$fin){
  if(date("w",strtotime($current))==0 and !$config['Dimanche']){}
  else{
    $dates[]=array($current,dateAlpha2($current));
  }
  $current=date("Y-m-d",strtotime("+1 day",strtotime($current)));
}

$debutFr=dateFr($debut);
$finFr=dateFr($fin);
$heures=array(); 	// Nombre total d'heures pour chaque jour
$agents=array();	// Même chose avec le nombre d'agents
$agents_id=array();	// Utilisé pour compter les agents présents chaque jour
$nbAgents=array();	// Nombre d'agents pour chaque jour
$tab=array();
$nb=count($dates);	// Nombre de dates
$nbSemaines=$nb/($config['Dimanche']?7:6);	// Nombre de semaines
$totalAgents=0;		// Les totaux
$totalHeures=0;

// Les tris
$tri=isset($_GET['tri'])?$_GET['tri']:$_SESSION['stat_temps_tri'];
$_SESSION['stat_temps_tri']=$tri;
$tri2=null;

switch($tri){
  case "agent desc" :	$tri="`nom` desc, `prenom` desc";		break;
  case "statut" :	$tri="`statut`,`nom`,`prenom`";			break;
  case "statut desc" :	$tri="`statut` desc,`nom`,`prenom`";		break;
  case "total" :	$tri="`nom`,`prenom`"; $tri2="total";		break;
  case "total desc" :	$tri="`nom`,`prenom`"; $tri2="totaldesc";	break;
  case "max" :		$tri="`heuresHebdo`,`nom`,`prenom`";		break;
  case "max desc" :	$tri="`heuresHebdo` desc,`nom`,`prenom`";	break;
  default :		$tri="`nom`, `prenom`";				break;
}

// Récupération des couleur en fonction des statuts
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}select_statuts`;");
$couleurStatut=$db->result;

$req="SELECT `{$dbprefix}pl_poste`.`date` AS `date`, `{$dbprefix}pl_poste`.`debut` AS `debut`, ";
$req.="`{$dbprefix}pl_poste`.`fin` AS `fin`, `{$dbprefix}personnel`.`id` AS `perso_id`, ";
$req.="`{$dbprefix}personnel`.`nom` AS `nom`,`{$dbprefix}personnel`.`prenom` AS `prenom`, ";
$req.="`{$dbprefix}personnel`.`heuresHebdo` AS `heuresHebdo`,`{$dbprefix}personnel`.`statut` AS `statut` ";
$req.="FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` ";
$req.="INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}postes`.`id`=`{$dbprefix}pl_poste`.`poste` ";
$req.="WHERE `date`>='$debut' AND `date`<='$fin' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}postes`.`statistiques`='1' ";
$req.="ORDER BY $tri;";

// Recherche des élements dans pl_poste afin  de compter les heures et le nombre d'agents
$db=new db();
$db->query($req);
if($db->result){
  foreach($db->result as $elem){
    if(!array_key_exists($elem['perso_id'],$tab)){		// création d'un tableau de données par agent (id, nom, heures de chaque jour ...)
      $tab[$elem['perso_id']]=Array("perso_id"=>$elem['perso_id'],"nom"=>$elem['nom'],
      "prenom"=>$elem['prenom'],"heuresHebdo"=>$elem['heuresHebdo'],"statut"=>$elem['statut'],"total"=>0,"semaine"=>0,
      "max"=>$nbSemaines*$elem['heuresHebdo']);
      foreach($dates as $d){
	$tab[$elem['perso_id']][$d[0]]=0;
      }
    }
	  
    $d=new datePl($elem['date']);
    $position=$d->position!=0?$d->position-1:6;
    $tab[$elem['perso_id']][$elem['date']]+=diff_heures($elem['debut'],$elem['fin'],"decimal");	// ajout des heures par jour
    $tab[$elem['perso_id']]['total']+=diff_heures($elem['debut'],$elem['fin'],"decimal");	// ajout des heures sur toutes la période
    $totalHeures+=diff_heures($elem['debut'],$elem['fin'],"decimal");		// compte la somme des heures sur la période
  }
}

// pour chaque jour, on compte les heures et les agents
foreach($dates as $d){
  $agents_id=array();
  if(is_array($tab)){
    foreach($tab as $elem){
      // on compte les heures de chaque agent
      if(!array_key_exists($d[0],$agents)){
	$agents[$d[0]]=0;
      }
      if(array_key_exists($d[0],$elem)){
	$agents[$d[0]]++;
      }
      // on compte le total d'heures par jours
      if(!array_key_exists($d[0],$heures)){
	$heures[$d[0]]=0;
      }
      if(array_key_exists($d[0],$elem)){
	$heures[$d[0]]+=$elem[$d[0]];
      }
      // on compte les agents par jours	+ le total sur la période
      if(!in_array($elem['perso_id'],$agents_id) and $elem[$d[0]]){
	$agents_id[]=$elem['perso_id'];
	$totalAgents++;
      }
    }
  }
  // on compte les agents par jours (2ème partie)
  $nbAgents[$d[0]]=count($agents_id);
}

// passage en session du tableau pour le fichier export.php
$_SESSION['stat_tab']=$tab;
$_SESSION['stat_heures']=$heures;
$_SESSION['stat_agents']=$agents;
$_SESSION['stat_dates']=$dates;
$_SESSION['oups']['stat_totalHeures']=$totalHeures;
$_SESSION['oups']['stat_nbAgents']=$nbAgents;

// Formatage des données pour affichage
$keys=array_keys($tab);
foreach($keys as $key){
  $tab[$key]['total']=number_format($tab[$key]['total'],2,'.',' ');
  $tab[$key]['semaine']=number_format($tab[$key]['total']/$nbSemaines,2,'.',' ');		// ajout la moyenne par semaine
  $tab[$key]['heuresHebdo']=$tab[$key]['max']!=0?number_format($tab[$key]['heuresHebdo'],2,'.',' '):"-";
  $tab[$key]['max']=$tab[$key]['max']!=0?number_format($tab[$key]['max'],2,'.',' '):"-";
  foreach($dates as $d){
    $tab[$key][$d[0]]=$tab[$key][$d[0]]!=0?number_format($tab[$key][$d[0]],2,'.',' '):"-";
  }
}

foreach($dates as $d){
  $heures[$d[0]]=$heures[$d[0]]!=0?number_format($heures[$d[0]],2,'.',' '):"-";
  $nbAgents[$d[0]]=$nbAgents[$d[0]]!=0?:"-";
  $totalHeures=$totalHeures!=0?number_format($totalHeures,2,'.',' '):"-";
}


//			-------------		Affichage du tableau		---------------------//
echo <<<EOD
<table>
<tr><td style='width:350px;'><b>Du $debutFr au $finFr</b></td>
<td>
<form name='form' method='get' action='index.php'>
<input type='hidden' name='page' value='statistiques/temps.php' />
Début : <input type='text' name='debut' id='debut' value='$debut' />&nbsp;
<img src='img/calendrier.gif' onclick='calendrier("debut");' alt='date'/>&nbsp;
Fin : <input type='text' name='fin' id='fin' value='$fin' />&nbsp;
<img src='img/calendrier.gif' onclick='calendrier("fin");' alt='date'/>&nbsp;
<input type='submit' value='OK' /></form>
</td></tr></table>
EOD;

// S'il y a des éléments, affiche le tableau
if(is_array($tab)){
  if($tri2=="total"){
    usort($tab,"cmp_semaine");
  }
  if($tri2=="totaldesc"){
    usort($tab,"cmp_semainedesc");
  }

  echo <<<EOD
  <table id='table_temps'>
  <thead>
  <tr>
  <th>Agents</th>
  <th>Statut</th>
EOD;
  foreach($dates as $d){
    echo "<th>{$d[1]}</th>\n";
  }
  //Si nbSemaine == 1, le total=moyenne : on ne l'affiche pas
  $colspan=1;
  if($nbSemaines!=1){
    $colspan=3;
    echo "<th>Total</th>\n";
    echo "<th>Max.</th>\n";
  }
  echo <<<EOD
  <th>Moyenne<br/>Hebdo.</th>
  <th>Max. Hebdo.</th>
  </tr>
  </thead>
  <tbody>
EOD;

  foreach($tab as $elem){

    // On change de couleur en fonction du statut
    $couleur="#CCDDEE";		
    foreach($couleurStatut as $elem2){
      if($elem2['valeur']==$elem['statut']){
	$couleur=$elem2['couleur'];
	break;
      }
    }

    // Couleurs en fonction de la moyenne hebdo et des heures prévues
    $color=$elem['semaine']>$elem['heuresHebdo']?"style='background:red;font-weight:bold;'":"";
    if(($elem['heuresHebdo']-$elem['semaine'])<=0.5 and ($elem['semaine']-$elem['heuresHebdo'])<=0.5){		// 0,5 du quota hebdo : vert
      $color="style='background:lightgreen;font-weight:bold;'";
    }
    
    // Affichage des lignes : Nom, heures par jour, par semaine, heures prévues
    echo "<tr><td style='background:$couleur;'>{$elem['nom']} {$elem['prenom']}</td>\n";
    $elem['statut']=$elem['statut']?$elem['statut']:"&nbsp;";
    echo "<td style='background:$couleur;'>{$elem['statut']}</td>\n";
    foreach($dates as $d){
      $class=$elem[$d[0]]!="-"?"bg-yellow":null;
      echo "<td class='$class'>{$elem[$d[0]]}</td>\n";
    }
    if($nbSemaines!=1){
      echo "<td $color>{$elem['total']}</td>\n";
      echo "<td>{$elem['max']}</td>\n";
    }
    echo "<td $color>{$elem['semaine']}</td>\n";
    echo "<td style='background:#DDDDDD;'>{$elem['heuresHebdo']}</td>\n";
    echo "</tr>\n";
  }
  echo "</tbody>\n";

  // Affichage de la ligne "Nombre d'heures"
  echo "<tfoot><tr style='background:#DDDDDD;'><th colspan='2'>Nombre d'heures</th>\n";

  foreach($dates as $d){
    echo "<th>{$heures[$d[0]]}</th>\n";
  }
  echo "<th>$totalHeures</th><th colspan='$colspan'>&nbsp;</th>\n";
  echo "</tr>\n";


  // Affichage de la ligne "Nombre d'agents"
  echo "<tr style='background:#DDDDDD;'><th colspan='2'>Nombre d'agents</th>\n";
  foreach($dates as $d){
    echo "<th>{$nbAgents[$d[0]]}</th>\n";
  }
  echo "<th>$totalAgents</th><th colspan='$colspan'>&nbsp;</th>\n";
  echo "</tr>\n";

  echo "</tfoot>\n";
  echo "</table>\n";
  echo "Exporter \n";
  echo "<a href='javascript:export_stat(\"temps\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
  echo "<a href='javascript:export_stat(\"temps\",\"xls\");'>XLS</a>\n";
}
else{			// Si pas d'élément
  echo "Les plannings de la période choisie sont vides.<br/><br/><br/><br/><br/><br/>";
}
?>
<script type='text/JavaScript'>
$(document).ready(function(){
  var oTable=$("#table_temps").dataTable({
    "bJQueryUI": true,
    "sPaginationType": "full_numbers",
    "bStateSave": false,
    "aLengthMenu" : [[25,50,75,100,-1],[25,50,75,100,"Tous"]],
    "iDisplayLength" : -1,
    "oLanguage" : {"sUrl" : "js/dataTables/french.txt"},
    "sScrollX": "100%",
  });
  new FixedColumns( oTable, {
    "iLeftColumns" : 2
  });
});
</script>