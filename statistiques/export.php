<?php
/*
Planning Biblio, Version 1.5.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : statistiques/export.php
Création : mai 2011
Dernière modification : 22 août 2012
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'exporter les différentes statistiques. Recherche les informations dans la bases de données voulues ($_GET['nom']),
les place dans les tableaux $cellules et $lignes, puis les écrit dans un fichier (data/stat_$_GET['nom'])

Page appelée par la fonction JavaScript "export_stat" lors du clique sur les liens "exporter" des pages de statistiques
*/

require_once "class.statistiques.php";

 // Compter les jours ouvrables (ou ouvrés) entre début et fin
$nbJours=compte_jours($_SESSION['debut'],$_SESSION['fin'],"ouvrables");
$joursParSemaine=6;		// 5 jours ouvrés, 6 jours ouvrables
$Fnm = "data/stat_".$_GET['nom'];

if($_GET['type']=="csv"){
  $separateur="';'";
  $Fnm.=".csv";
}
else{
  $separateur="\t";
  $Fnm.=".xls";
}

$tab=$_SESSION['stat_tab'];

$debut=dateAlpha($_SESSION['stat_debut']);
$fin=dateAlpha($_SESSION['stat_fin']);

$lignes=Array();

switch($_GET['nom']){
  case "agents" : 									// Agents
    $lignes=array("Statistiques par agent du $debut au $fin",null,"Postes");
    $lignes[]=join(array("Nom","Prénom","Heures","Moyenne hebdo","Poste","Etage","Heures par poste"),$separateur);
    foreach($tab as $elem){
      $jour=$elem[2]/$nbJours;
      $hebdo=$jour*$joursParSemaine;
      foreach($elem[1] as $poste){
	$cellules=Array();
	$cellules[]=$elem[0][1];									// nom
	$cellules[]=$elem[0][2];									// prénom
	$cellules[]=number_format($elem[2],2,',',' ');				// Nombre d'heures
	// $cellules[]=number_format(round($jour,2),2,',',' ');		// moyenne jour
	$cellules[]=number_format(round($hebdo,2),2,',',' ');		// moyenne hebdo
	$cellules[]=$poste[1];										// Nom du poste
	$cellules[]=$poste[2];										// Etage
	$cellules[]=number_format($poste[3],2,',',' ');				// Heures par poste
	$lignes[]=join($cellules,$separateur);
      }
    }
    $lignes[]=null;
    $lignes[]="Samedis";
    $lignes[]=join(array("Nom","Prénom","Nombre de samedis","Dates","Heures"),$separateur);
    foreach($tab as $elem){
      foreach($elem[3] as $samedi){
	$cellules=Array();
	$cellules[]=$elem[0][1];							// nom
	$cellules[]=$elem[0][2];							// prénom
	$cellules[]=count($elem[3]);						// nombre de samedi
	$cellules[]=dateFr($samedi[0]);						// date
	$cellules[]=number_format($samedi[1],2,',',' ');	// heures
	$lignes[]=join($cellules,$separateur);
      }
    }
    if($config['Dimanche']){
      $lignes[]=null;
      $lignes[]="Dimanches";
      $lignes[]=join(array("Nom","Prénom","Nombre de dimanches","Dates","Heures"),$separateur);
      foreach($tab as $elem){
	foreach($elem[6] as $dimanche){
	  $cellules=Array();
	  $cellules[]=$elem[0][1];							// nom
	  $cellules[]=$elem[0][2];							// prénom
	  $cellules[]=count($elem[6]);						// nombre de dimanche
	  $cellules[]=dateFr($dimanche[0]);						// date
	  $cellules[]=number_format($dimanche[1],2,',',' ');	// heures
	  $lignes[]=join($cellules,$separateur);
	}
      }
    }

    //		Affichage des jours feries
    $lignes[]=null;
    $lignes[]="Jours feriés";
    $lignes[]=join(array("Nom","Prénom","Nombre de jours feriés","Dates","Heures"),$separateur);
    foreach($tab as $elem){
      foreach($elem[9] as $ferie){
	$cellules=Array();
	$cellules[]=$elem[0][1];							// nom
	$cellules[]=$elem[0][2];							// prénom
	$cellules[]=count($elem[9]);						// nombre de J. Feriés
	$cellules[]=dateFr($ferie[0]);						// date
	$cellules[]=number_format($ferie[1],2,',',' ');	// heures
	$lignes[]=join($cellules,$separateur);
      }
    }

    //	Affichage des 19-20h
    $lignes[]=null;
    $lignes[]="19h-20h";
    $lignes[]=join(array("Nom","Prénom","Nombre de 19h-20h","Dates"),$separateur);
    foreach($tab as $elem){
      foreach($elem[7] as $h19){
	$cellules=Array();
	$cellules[]=$elem[0][1];							// nom
	$cellules[]=$elem[0][2];							// prénom
	$cellules[]=count($elem[7]);						// nombre de dimanche
	$cellules[]=dateFr($h19);						// date
	$lignes[]=join($cellules,$separateur);
      }
    }

    //	Affichage des 20-22h
    $lignes[]=null;
    $lignes[]="20h-22h";
    $lignes[]=join(array("Nom","Prénom","Nombre de 20h-22h","Dates"),$separateur);
    foreach($tab as $elem){
      foreach($elem[8] as $h20){
	$cellules=Array();
	$cellules[]=$elem[0][1];							// nom
	$cellules[]=$elem[0][2];							// prénom
	$cellules[]=count($elem[7]);						// nombre de dimanche
	$cellules[]=dateFr($h20);						// date
	$lignes[]=join($cellules,$separateur);
      }
    }

    $lignes[]=null;
    $lignes[]="Absences";
    $lignes[]=join(array("Nom","Prénom","Heures d'absences","Dates","Heures"),$separateur);
    foreach($tab as $elem){
      $total_absences=$elem[5];
      foreach($elem[4] as $absences){
	$cellules=Array();
	$cellules[]=$elem[0][1];							// nom
	$cellules[]=$elem[0][2];							// prénom
	$cellules[]=number_format($total_absences,2,',',' ');;						// heures total d'absences
	$cellules[]=dateFr($absences[0]);					// date
	$cellules[]=number_format($absences[1],2,',',' ');	// heures
	$lignes[]=join($cellules,$separateur);
      }
    }
    break;

  case "postes" : 									// Postes
    $lignes=array("Statistiques par poste du $debut au $fin",null);
    $lignes[]=join(array("Poste","Etage","Obligatoire/renfort","Heures","Moyenne jour","Moyenne hebdo","Nom de l'agent","Prénom de l'agent","Heures par agent"),$separateur);
    foreach($tab as $elem){
    $jour=$elem[2]/$nbJours;
    $hebdo=$jour*$joursParSemaine;
    foreach($elem[1] as $agent){
	$cellules=Array();
	$cellules[]=$elem[0][1];									// nom du poste
	$cellules[]=$elem[0][2];									// Etage
	$cellules[]=$elem[0][3];									// Obligatoire
	$cellules[]=number_format($elem[2],2,',',' ');				// Nombre d'heures
	$cellules[]=number_format(round($jour,2),2,',',' ');		// moyenne jour
	$cellules[]=number_format(round($hebdo,2),2,',',' ');		// moyenne hebdo
	$cellules[]=$agent[1];										// Nom de l'agent
	$cellules[]=$agent[2];										// Prénom
	$cellules[]=number_format($agent[3],2,',',' ');				// Heures par agent
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
}

$inF = fopen($Fnm,"w\n");

$lignes=array_map("utf8_decode",$lignes);

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