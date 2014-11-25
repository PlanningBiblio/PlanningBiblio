<?php
/*
Planning Biblio, Version 1.8.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : statistiques/export.php
Création : mai 2011
Dernière modification : 4 novembre 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'exporter les différentes statistiques. Recherche les informations dans la bases de données voulues ($_GET['nom']),
les place dans les tableaux $cellules et $lignes, puis les écrit dans un fichier (data/stat_$_GET['nom'])

Page appelée par la fonction JavaScript "export_stat" lors du clique sur les liens "exporter" des pages de statistiques
*/

session_start();
require_once "../include/config.php";
require_once "../include/function.php";
require_once "class.statistiques.php";

 // Compter les jours ouvrables (ou ouvrés) entre début et fin
$debut=$_SESSION['stat_debut'];
$fin=$_SESSION['stat_fin'];
$debutSQL=dateFr($debut);
$finSQL=dateFr($fin);

$db=new db();
$db->query("SELECT `date` FROM `{$dbprefix}pl_poste` WHERE `date` BETWEEN '$debutSQL' AND '$finSQL' GROUP BY `date`;");
$nbJours=$db->nb;

$joursParSemaine=$config['Dimanche']?7:6;
$Fnm = "stat_{$_GET['nom']}_".date("YmdHis");

if($_GET['type']=="csv"){
  $separateur="';'";
  $Fnm.=".csv";
}
else{
  $separateur="\t";
  $Fnm.=".xls";
}

echo $Fnm;	// Retour AJAX du nom de fichier
$Fnm = "../data/$Fnm";

$tab=$_SESSION['stat_tab'];

$debut=dateAlpha($debutSQL);
$fin=dateAlpha($finSQL);

$lignes=Array();

switch($_GET['nom']){
  case "postes" : 									// Postes
    $lignes=array("Statistiques par poste du $debut au $fin",null);
    $lignes[]="Les agents";
    $lignes[]=join(array("Poste","Etage","Obligatoire/renfort","Heures","Moyenne jour","Moyenne hebdo","Nom de l'agent","Prénom de l'agent","Heures par agent"),$separateur);
    foreach($tab as $elem){
      $jour=$elem[2]/$nbJours;
      $hebdo=$jour*$joursParSemaine;
      foreach($elem[1] as $agent){
	$cellules=Array();
	$cellules[]=$elem[0][1];			// nom du poste
	$cellules[]=$elem[0][2];			// Etage
	$cellules[]=$elem[0][3];			// Obligatoire
	$cellules[]=number_format($elem[2],2,',',' ');	// Nombre d'heures
	$cellules[]=number_format($jour,2,',',' ');	// moyenne jour
	$cellules[]=number_format($hebdo,2,',',' ');	// moyenne hebdo
	$cellules[]=$agent[1];				// Nom de l'agent
	$cellules[]=$agent[2];				// Prénom
	$cellules[]=number_format($agent[3],2,',',' ');	// Heures par agent
	$lignes[]=join($cellules,$separateur);
      }
    }
    $lignes[]=null;
    $lignes[]="Les services";
    foreach($tab as $elem){
      $jour=$elem[2]/$nbJours;
      $hebdo=$jour*$joursParSemaine;
      foreach($elem["services"] as $service){
	$cellules=Array();
	$cellules[]=$elem[0][1];				// nom du poste
	$cellules[]=$elem[0][2];				// Etage
	$cellules[]=$elem[0][3];				// Obligatoire
	$cellules[]=number_format($elem[2],2,',',' ');		// Nombre d'heures
	$cellules[]=number_format($jour,2,',',' ');		// moyenne jour
	$cellules[]=number_format($hebdo,2,',',' ');		// moyenne hebdo
	$cellules[]=str_replace("ZZZ_",null,$service["nom"]);	// Nom du service
	$cellules[]=number_format($service["heures"],2,',',' ');// Heures par agent
	$lignes[]=join($cellules,$separateur);
      }
    }
    $lignes[]=null;
    $lignes[]="Les statuts";
    foreach($tab as $elem){
      $jour=$elem[2]/$nbJours;
      $hebdo=$jour*$joursParSemaine;
      foreach($elem["statuts"] as $statut){
	$cellules=Array();
	$cellules[]=$elem[0][1];				// nom du poste
	$cellules[]=$elem[0][2];				// Etage
	$cellules[]=$elem[0][3];				// Obligatoire
	$cellules[]=number_format($elem[2],2,',',' ');		// Nombre d'heures
	$cellules[]=number_format($jour,2,',',' ');		// moyenne jour
	$cellules[]=number_format($hebdo,2,',',' ');		// moyenne hebdo
	$cellules[]=str_replace("ZZZ_",null,$statut["nom"]);	// Nom du statut
	$cellules[]=number_format($statut["heures"],2,',',' ');	// Heures par agent
	$lignes[]=join($cellules,$separateur);
      }
    }
    break;

  case "postes_synthese" : 							// Postes (synthèse)
    $lignes=array("Statistiques par poste (synthèse) du $debut au $fin",null);
    $lignes[]=join(array("Poste","Etage","Obligatoire/renfort","Heures","Moyenne jour","Moyenne hebdo"),$separateur);
    foreach($tab as $elem){
      $jour=$elem[2]/$nbJours;
      $hebdo=$jour*$joursParSemaine;
      $total_heures+=$elem[2];
      $total_jour+=$jour;
      $total_hebdo+=$hebdo;
      $cellules=Array();
      $cellules[]=$elem[0][1];									// nom du poste
      $cellules[]=$elem[0][2];									// Etage
      $cellules[]=$elem[0][3];									// Obligatoire
      $cellules[]=number_format($elem[2],2,',',' ');				// Nombre d'heures
      $cellules[]=number_format(round($jour,2),2,',',' ');		// moyenne jour
      $cellules[]=number_format(round($hebdo,2),2,',',' ');		// moyenne hebdo
      $lignes[]=join($cellules,$separateur);
    }
    $lignes[]=join(array("Total","","",number_format($total_heures,1,',',' '),number_format(round($total_jour,2),2,',',' '),number_format(round($total_hebdo,2),2,',',' ')),$separateur);
    break;

  case "postes_renfort" : 							// Postes de renfort
    $lignes=array("Poste de renfort du $debut au $fin",null);
    $lignes[]=join(array("Poste","Etage","Heures","Moyenne jour","Moyenne hebdo","Jours","Heures par jour","Début","Fin","Heures"),$separateur);
    foreach($tab as $elem){
      $jour=$elem[2]/$nbJours;
      $hebdo=$jour*$joursParSemaine;
      foreach($elem[1] as $date){
	foreach($date[1] as $horaires){
	  $cellules=Array();
	  $cellules[]=$elem[0][1];									// nom du poste
	  $cellules[]=$elem[0][2];									// Etage
	  $cellules[]=number_format($elem[2],2,',',' ');				// Nombre d'heures
	  $cellules[]=number_format(round($jour,2),2,',',' ');		// moyenne jour
	  $cellules[]=number_format(round($hebdo,2),2,',',' ');		// moyenne hebdo
	  $cellules[]=dateFr($date[0]);								// date
	  $cellules[]=number_format($date[2],2,',',' ');				// heures par jour
	  $cellules[]=$horaires[0];									// debut
	  $cellules[]=$horaires[1];									// fin
	  $cellules[]=number_format($horaires[2],2,',',' ');			// heures
	  $lignes[]=join($cellules,$separateur);
	}
      }
    }
    break;

  // Absences
  case "absences" :
  $debut=date($_SESSION['oups']['stat_absences_debut']);
  $fin=date($_SESSION['oups']['stat_absences_fin']);
  $motifs=$_SESSION['oups']['stat_absences_motifs'];
  $totaux=$_SESSION['oups']['stat_absences_totaux'];

  // Première ligne et entête du tableau
  $lignes[]="Statistiques sur les absences du $debut au $fin";		// Affichage du nom des colonnes
  $tmp=array("Nom","Prénom","Total","Total d'heures");
  foreach($motifs as $m){
    $tmp[]=str_replace("<br/>"," ",$m)." Nombre";
    $tmp[]=str_replace("<br/>"," ",$m)." Heures";
  }
  $lignes[]=join($tmp,$separateur);

  // Contenu
  foreach($tab as $elem){
    $cellules=Array();
    $cellules[]=html_entity_decode($elem['nom'],ENT_QUOTES|ENT_IGNORE,"UTF-8");		// Nom et prénom
    $cellules[]=html_entity_decode($elem['prenom'],ENT_QUOTES|ENT_IGNORE,"UTF-8");
    $cellules[]=$elem['total'];								// Total
    $cellules[]=$elem['totalHeures'];							// Total d'heures
    foreach($motifs as $m){								
      $cellules[]=$elem[$m]['total'];							// Nombre d'absences par motif
      $cellules[]=$elem[$m]['heures'];							// Heures d'absences par motif
    }
    $lignes[]=join($cellules,$separateur);
  }

  // Totaux (dernière ligne)
  $cellules=Array("Totaux","",$totaux['_general'],$totaux['_generalHeures']);
  foreach($motifs as $m){								// Nombre d'absences par motif
    $cellules[]=$totaux[$m]['frequence'];
    $cellules[]=$totaux[$m]['heures'];
  }
  $lignes[]=join($cellules,$separateur);
  break;

  case "temps" :									// Feuille de temps
  $debutFr=dateFr($_SESSION['oups']['stat_temps_debut']);
  $finFr=dateFr($_SESSION['oups']['stat_temps_fin']);
  $dates=$_SESSION['stat_dates'];
  $heures=$_SESSION['stat_heures'];
  $agents=$_SESSION['stat_agents'];
  $lignes[]="Du $debutFr au $finFr";		// Affichage du nom des colonnes
  $tmp=array("Nom","Prénom","Statut");

  foreach($dates as $d){
    $tmp[]=str_replace("<br/>"," ",$d[1]);
  }
  $tmp[]="Total";
  $tmp[]="Max";
  $tmp[]="Moyenne Hebdo.";
  $tmp[]=" Max. Hebdo.";
  $lignes[]=join($tmp,$separateur);
  foreach($tab as $elem){
    $cellules=Array();
    $cellules[]=html_entity_decode($elem['nom'],ENT_QUOTES|ENT_IGNORE,"UTF-8");		// Nom et prénom
    $cellules[]=html_entity_decode($elem['prenom'],ENT_QUOTES|ENT_IGNORE,"UTF-8");
    $cellules[]=html_entity_decode($elem['statut'],ENT_QUOTES|ENT_IGNORE,"UTF-8");	// Statut
    foreach($dates as $d){								// Heures de chaque jour
      $cellules[]=number_format($elem[$d[0]],2,',',' ');
    }
    $cellules[]=number_format($elem['total'],2,',',' ');				// Total d'heures sur la période
    $cellules[]=number_format($elem['max'],2,',',' ');					// Nombre d'heures maximum sur la période
    $cellules[]=number_format($elem['semaine'],2,',',' ');				// Moyenne d'heures par semaine
    $cellules[]=number_format($elem['heuresHebdo'],2,',',' ');				// Quota
    $lignes[]=join($cellules,$separateur);
  }
  $cellules=Array("Nombre d'heures","","");						// ligne "Nombre d'heures"
  foreach($dates as $d){
    $cellules[]=number_format($heures[$d[0]],2,',',' ');
  }
  $cellules[]=$_SESSION['oups']['stat_totalHeures'];
  $lignes[]=join($cellules,$separateur);
  $cellules=Array("Nombre d'agents","","");						// Lignes "Nombres d'agents
  foreach($dates as $d){
    $cellules[]=$_SESSION['oups']['stat_nbAgents'][$d[0]];
  }
  $total=0;
  foreach($_SESSION['oups']['stat_nbAgents'] as $elem){
    $total+=$elem;
  }
  $cellules[]=$total; //$agents[7];
  $lignes[]=join($cellules,$separateur);
  break;

  case "samedis" : // Samedis
    $lignes=statistiquesSamedis($tab,$debut,$fin,$separateur,$nbJours,$jour,$joursParSemaine);
    break;

  default :
    $lignes=statistiques1($_GET['nom'],$tab,$debut,$fin,$separateur,$nbJours,$jour,$joursParSemaine);
    break;
}

$inF = fopen($Fnm,"w\n");

$lignes=array_map("utf8_decode",$lignes);
$lignes=array_map("html_entity_decode_latin1",$lignes);

foreach($lignes as $elem){
  if($_GET['type']=="csv"){
    fputs($inF,"'$elem'\n");
  }
  else{
    fputs($inF,$elem."\n");
  }
}
fclose($inF);
?>