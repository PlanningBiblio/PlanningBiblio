<?php
/*
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : statistiques/temps.php
Création : mai 2011
Dernière modification : 2 septembre 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Affiche un tableau avec le nombre d'heures de service public effectué par agent par jour et par semaine

Page appelée par le fichier index.php, accessible par le menu statistiques / Feuille de temps
*/

require_once "class.statistiques.php";
require_once "absences/class.absences.php";

echo "<h3>Feuille de temps</h3>\n";

require_once "include/horaires.php";

//	Initialisation des variables
if(isset($_GET['debut'])){
  $debut=filter_input(INPUT_GET,"debut",FILTER_CALLBACK,array("options"=>"sanitize_dateFR"));
  $fin=filter_input(INPUT_GET,"fin",FILTER_CALLBACK,array("options"=>"sanitize_dateFR"));
  $debut=dateSQL($debut);
  $fin=$fin?dateFr($fin):$debut;
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
$siteHeures=array(0,0);	// Heures par site
$siteAgents=array(0,0);	// Agents par site

// Récupération des couleur en fonction des statuts
$db=new db();
$db->select2("select_statuts");
$couleurStatut=$db->result;

// Recherche des heures de SP à effectuer pour tous les agents pour toutes les semaines demandées
$d=new datePl($debut);
$d1=$d->dates[0];
// Pour chaque semaine
for($d=$d1;$d<=$fin;$d=date("Y-m-d",strtotime($d."+1 week"))){
  $heuresSP[$d]=calculHeuresSP($d);
  // déduction des absences
  if($config['Planning-Absences-Heures-Hebdo']){
    $a=new absences();
    $heuresAbsences[$d]=$a->calculHeuresAbsences($d);
    foreach($heuresAbsences[$d] as $key => $value){
      if(array_key_exists($key,$heuresSP[$d])){
	$heuresSP[$d][$key]=$heuresSP[$d][$key]-$value;
	if($heuresSP[$d][$key]<0){
	  $heuresSP[$d][$key]=0;
	}
      }
    }
  }
}
// Calcul des totaux d'heures de SP à effectuer sur la période
$totalSP=array();
foreach($heuresSP as $key => $value){		// $key=date, $value=array
  foreach($value as $key2 => $value2){		// $key2=perso_id, $value2=heures
    if(!array_key_exists($key2, $totalSP)){
      $totalSP[$key2]=(float) $value2;
    }else{
      $totalSP[$key2]+=(float) $value2;
    }
  }
}
// Calcul des moyennes hebdomadaires de SP à effectuer
$moyennesSP=array();
foreach($totalSP as $key => $value){
  $moyennesSP[$key]=$value/(count($heuresSP));
}

$db=new db();
$debutREQ=$db->escapeString($debut);
$finREQ=$db->escapeString($fin);

$req="SELECT `{$dbprefix}pl_poste`.`date` AS `date`, `{$dbprefix}pl_poste`.`debut` AS `debut`, ";
$req.="`{$dbprefix}pl_poste`.`fin` AS `fin`, `{$dbprefix}personnel`.`id` AS `perso_id`, ";
$req.="`{$dbprefix}pl_poste`.`site` AS `site`, ";
$req.="`{$dbprefix}personnel`.`nom` AS `nom`,`{$dbprefix}personnel`.`prenom` AS `prenom`, ";
$req.="`{$dbprefix}personnel`.`statut` AS `statut` ";
$req.="FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` ";
$req.="INNER JOIN `{$dbprefix}postes` ON `{$dbprefix}postes`.`id`=`{$dbprefix}pl_poste`.`poste` ";
$req.="WHERE `date`>='$debutREQ' AND `date`<='$finREQ' AND `{$dbprefix}pl_poste`.`absent`<>'1' AND `{$dbprefix}pl_poste`.`supprime`<>'1' AND `{$dbprefix}postes`.`statistiques`='1' ";
$req.="ORDER BY `nom`,`prenom`;";

$db->query($req);
if($db->result){
  foreach($db->result as $elem){
    if(!array_key_exists($elem['perso_id'],$tab)){		// création d'un tableau de données par agent (id, nom, heures de chaque jour ...)
      $tab[$elem['perso_id']]=Array("perso_id"=>$elem['perso_id'],"nom"=>$elem['nom'],
      "prenom"=>$elem['prenom'],"statut"=>$elem['statut'],"site1"=>0,"site2"=>0,"total"=>0,
      "semaine"=>0);
      foreach($dates as $d){
	$tab[$elem['perso_id']][$d[0]]=0;
      }
    }
	  
    $d=new datePl($elem['date']);
    $position=$d->position!=0?$d->position-1:6;
    $tab[$elem['perso_id']][$elem['date']]+=diff_heures($elem['debut'],$elem['fin'],"decimal");	// ajout des heures par jour
    $tab[$elem['perso_id']]['total']+=diff_heures($elem['debut'],$elem['fin'],"decimal");	// ajout des heures sur toutes la période
    if($elem["site"]){
      if(!array_key_exists("site{$elem['site']}",$tab[$elem['perso_id']])){
	$tab[$elem['perso_id']]["site{$elem['site']}"]=0;
      }
      $tab[$elem['perso_id']]["site{$elem['site']}"]+=diff_heures($elem['debut'],$elem['fin'],"decimal");	// ajout des heures sur toutes la période par site
    }
    $totalHeures+=diff_heures($elem['debut'],$elem['fin'],"decimal");		// compte la somme des heures sur la période
    if(!array_key_exists($elem['site'],$siteHeures)){
      $siteHeures[$elem['site']]=0;
    }
    $siteHeures[$elem['site']]+=diff_heures($elem['debut'],$elem['fin'],"decimal");
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

	for($i=1;$i<=$config['Multisites-nombre'];$i++){
	  if(array_key_exists("site$i",$elem)){
	    if(!array_key_exists($i,$siteAgents)){
	      $siteAgents[$i]=0;
	    }
	    $siteAgents[$i]++;
	  }
	}
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
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    $tab[$key]["site{$i}Semaine"]=array_key_exists("site{$i}",$tab[$key])?number_format($tab[$key]["site{$i}"]/$nbSemaines,2,'.',' '):"-";
    $tab[$key]["site{$i}"]=array_key_exists("site{$i}",$tab[$key])?number_format($tab[$key]["site{$i}"],2,'.',' '):"-";
  }
  $tab[$key]['total']=number_format($tab[$key]['total'],2,'.',' ');
  $tab[$key]['semaine']=number_format($tab[$key]['total']/$nbSemaines,2,'.',' ');		// ajout la moyenne par semaine

  if(!array_key_exists($key,$moyennesSP) or !is_numeric($moyennesSP[$key])){
    $tab[$key]['heuresHebdo']="Erreur";
  }elseif($moyennesSP[$key]>0){
    $tab[$key]['heuresHebdo']=number_format($moyennesSP[$key],2,'.',' ');
  }else{
    $tab[$key]['heuresHebdo']="-";
  }
  
  if(!array_key_exists($key,$totalSP) or !is_numeric($totalSP[$key])){
    $tab[$key]['max']="Erreur";
  }elseif($totalSP[$key]>0){
    $tab[$key]['max']=number_format($totalSP[$key],2,'.',' ');
  }else{
    $tab[$key]['max']="-";
  }

  foreach($dates as $d){
    $tab[$key][$d[0]]=$tab[$key][$d[0]]!=0?number_format($tab[$key][$d[0]],2,'.',' '):"-";
  }
}

foreach($dates as $d){
  if(array_key_exists($d[0],$heures)){
    $heures[$d[0]]=$heures[$d[0]]!=0?number_format($heures[$d[0]],2,'.',' '):"-";
  }else{
    $heures[$d[0]]="-";
  }
  if(array_key_exists($d[0],$nbAgents)){
    $nbAgents[$d[0]]=$nbAgents[$d[0]]!=0?$nbAgents[$d[0]]:"-";
  }else{
    $nbAgents[$d[0]]="-";
  }
}
$totalHeures=$totalHeures!=0?number_format($totalHeures,2,'.',' '):"-";

for($i=1;$i<=$config['Multisites-nombre'];$i++){
  if(array_key_exists($i,$siteHeures) and $siteHeures[$i]!=0){
    $siteHeures[$i]=number_format($siteHeures[$i],2,'.',' ');
  }else{
    $siteHeures[$i]="-";
  }
  if(array_key_exists($i,$siteAgents) and $siteAgents[$i]!=0){
    $siteAgents[$i]=$siteAgents[$i];
  }else{
    $siteAgents[$i]="-";
  }
}


//			-------------		Affichage du tableau		---------------------//
echo <<<EOD
<table>
<tr><td style='width:350px;'><b>Du $debutFr au $finFr</b></td>
<td>
<form name='form' method='get' action='index.php'>
<input type='hidden' name='page' value='statistiques/temps.php' />
Début : <input type='text' name='debut' class='datepicker' value='$debutFr' />&nbsp;
Fin : <input type='text' name='fin' class='datepicker' value='$finFr' />&nbsp;
<input type='submit' value='OK' id='submit' class='ui-button'/></form>
</td></tr></table>
<br/>
EOD;

// S'il y a des éléments, affiche le tableau
if(is_array($tab)){
  echo <<<EOD
  <table id='tableStatTemps' class='CJDataTable' data-fixedColumns='2'>
  <thead>
  <tr>
  <th>Agents</th>
  <th>Statut</th>
EOD;
  foreach($dates as $d){
    echo "<th>{$d[1]}</th>\n";
  }

  // Total par site
  if($config['Multisites-nombre']>1){
    for($i=1;$i<=$config['Multisites-nombre'];$i++){
      echo "<th>".$config["Multisites-site$i"]."</th>\n";
      if($nbSemaines!=1){
	echo "<th>Moyenne Hebdo.</th>\n";
      }
    }
  }

  // Total, moyenne, max
  echo "<th>Total</th>\n";
  echo "<th>Max.</th>\n";

  //Si nbSemaine == 1, le total=moyenne : on ne l'affiche pas
  $colspan=1;
  if($nbSemaines!=1){
    $colspan=3;
    echo "<th>Moyenne<br/>Hebdo.</th>\n";
    echo "<th>Max. Hebdo.</th>\n";
  }

  echo "</tr>\n";
  echo "</thead>\n";
  echo "<tbody>\n";

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

    if($config['Multisites-nombre']>1){
      for($i=1;$i<=$config['Multisites-nombre'];$i++){
	echo "<td>{$elem["site$i"]}</td>\n";
	if($nbSemaines!=1){
	  echo "<td>{$elem["site{$i}Semaine"]}</td>\n";
	}
      }
    }

    if($nbSemaines!=1){
      echo "<td $color>{$elem['total']}</td>\n";
      echo "<td>{$elem['max']}</td>\n";
    }
    echo "<td $color>{$elem['semaine']}</td>\n";
    echo "<td>{$elem['heuresHebdo']}</td>\n";
    echo "</tr>\n";
  }
  echo "</tbody>\n";

  // Affichage de la ligne "Nombre d'heures"
  echo "<tfoot><tr style='background:#DDDDDD;'><th colspan='2'>Nombre d'heures</th>\n";

  foreach($dates as $d){
    echo "<th>{$heures[$d[0]]}</th>\n";
  }

  if($config['Multisites-nombre']>1){
    for($i=1;$i<=$config['Multisites-nombre'];$i++){
      echo "<th>{$siteHeures[$i]}</th>\n";
      if($nbSemaines!=1){
	echo "<th>&nbsp;</th>\n";
      }
    }
  }

  echo "<th>$totalHeures</th><th colspan='$colspan'>&nbsp;</th>\n";
  echo "</tr>\n";


  // Affichage de la ligne "Nombre d'agents"
  echo "<tr style='background:#DDDDDD;'><th colspan='2'>Nombre d'agents</th>\n";
  foreach($dates as $d){
    echo "<th>{$nbAgents[$d[0]]}</th>\n";
  }

  if($config['Multisites-nombre']>1){
    for($i=1;$i<=$config['Multisites-nombre'];$i++){
      echo "<th>{$siteAgents[$i]}</th>\n";
      if($nbSemaines!=1){
	echo "<th>&nbsp;</th>\n";
      }
    }
  }

  echo "<th>$totalAgents</th><th colspan='$colspan'>&nbsp;</th>\n";
  echo "</tr>\n";

  echo "</tfoot>\n";
  echo "</table>\n";
  echo "<br/>Exporter \n";
  echo "<a href='javascript:export_stat(\"temps\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
  echo "<a href='javascript:export_stat(\"temps\",\"xls\");'>XLS</a>\n";
}
else{			// Si pas d'élément
  echo "Les plannings de la période choisie sont vides.<br/><br/><br/><br/><br/><br/>";
}
?>