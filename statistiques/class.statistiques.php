<?php
/*
Planning Biblio, Version 1.5.9
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : statistiques/class.statistiques.php
Création : 16 janvier 2013
Dernière modification : 13 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Classe statistiques 

Utilisée par les fichiers du dossier "statistiques"
*/

// pas de $version=acces direct aux pages de ce dossier => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

// AJouter les html_entity_decode latin1
// AJouter les variables $nom, (agents,service,statut)

function statistiques1($nom,$tab,$debut,$fin,$separateur,$nbJours,$jour,$joursParSemaine){
  $titre="Statistiques par $nom du $debut au $fin";

  $lignes=array($titre,null,"Postes");
  if($nom=="agent"){
    $cellules=array("Nom","Prénom","Heures","Moyenne hebdo");
    if($GLOBALS['config']['Multisites-nombre']>1){
      for($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++){
	$cellules[]="Heures ".$GLOBALS['config']["Multisites-site{$i}"];
	$cellules[]="Moyenne ".$GLOBALS['config']["Multisites-site{$i}"];
      }
    }
    $cellules[]="Poste";
    if($GLOBALS['config']['Multisites-nombre']>1){
      $cellules[]="Site";
    }
    $cellules=array_merge($cellules,array("Etage","Heures par poste"));
    $lignes[]=join($cellules,$separateur);
  }
  else{
    $cellules=array(ucfirst($nom),"Heures","Moyenne hebdo");
    if($GLOBALS['config']['Multisites-nombre']>1){
      for($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++){
	$cellules[]="Heures ".$GLOBALS['config']["Multisites-site{$i}"];
	$cellules[]="Moyenne ".$GLOBALS['config']["Multisites-site{$i}"];
      }
    }
    $cellules[]="Poste";
    if($GLOBALS['config']['Multisites-nombre']>1){
      $cellules[]="Site";
    }
    $cellules=array_merge($cellules,array("Etage","Heures par poste"));
    $lignes[]=join($cellules,$separateur);

  }
  foreach($tab as $elem){
    $jour=$elem[2]/$nbJours;
    $hebdo=$jour*$joursParSemaine;
    foreach($elem[1] as $poste){
      $cellules=Array();
      if($nom=="agent"){
	$cellules[]=$elem[0][1];	// nom
	$cellules[]=$elem[0][2];	// prénom
      }
      else{
	$cellules[]=$elem[0];		// nom du service ou du statut
      }
      $cellules[]=number_format($elem[2],2,',',' ');			// Nombre d'heures
      $cellules[]=number_format($hebdo,2,',',' ');		// moyenne hebdo
      if($GLOBALS['config']['Multisites-nombre']>1){
	for($i=1;$i<=$GLOBALS['config']['Multisites-nombre'];$i++){
	  $jour=$elem["sites"][$i]/$nbJours;
	  $hebdo=$jour*$joursParSemaine;
	  $cellules[]=number_format($elem["sites"][$i],2,',',' ');
	  $cellules[]=number_format($hebdo,2,',',' ');
	}
      }
      $cellules[]=$poste[1];						// Nom du poste
      $site=null;
      if($poste["site"]>0 and $GLOBALS['config']['Multisites-nombre']>1){
	$site=$GLOBALS['config']["Multisites-site{$poste['site']}"]." ";
      }
      if($GLOBALS['config']['Multisites-nombre']>1){
	$cellules[]=$site;
      }
      $cellules[]=$poste[2];						// Etage
      $cellules[]=number_format($poste[3],2,',',' ');			// Heures par poste
      $lignes[]=join($cellules,$separateur);
    }
  }
  $lignes[]=null;
  $lignes[]="Samedis";
  
  if($nom=="agent"){
    $lignes[]=join(array("Nom","Prénom","Nombre de samedis","Dates","Heures"),$separateur);
  }
  else{
    $lignes[]=join(array(ucfirst($nom),"Nombre de samedis","Dates","Heures"),$separateur);
  }
  foreach($tab as $elem){
    foreach($elem[3] as $samedi){
      $cellules=Array();
      if($nom=="agent"){
	$cellules[]=$elem[0][1];	// nom
	$cellules[]=$elem[0][2];	// prénom
      }
      else{
	$cellules[]=$elem[0];		// nom du service ou du statut
      }
      $cellules[]=count($elem[3]);						// nombre de samedi
      $cellules[]=dateFr($samedi[0]);						// date
      $cellules[]=number_format($samedi[1],2,',',' ');	// heures
      $lignes[]=join($cellules,$separateur);
    }
  }
  if($config['Dimanche']){
    $lignes[]=null;
    $lignes[]="Dimanches";
    if($nom=="agent"){
      $lignes[]=join(array("Nom","Prénom","Nombre de dimanches","Dates","Heures"),$separateur);
    }
    else{
      $lignes[]=join(array(ucfirst($nom),"Nombre de dimanches","Dates","Heures"),$separateur);
    }
    foreach($tab as $elem){
      foreach($elem[6] as $dimanche){
	$cellules=Array();
	if($nom=="agent"){
	  $cellules[]=$elem[0][1];	// nom
	  $cellules[]=$elem[0][2];	// prénom
	}
	else{
	  $cellules[]=$elem[0];		// nom du service ou du statut
	}
	$cellules[]=count($elem[6]);						// nombre de dimanche
	$cellules[]=dateFr($dimanche[0]);						// date
	$cellules[]=number_format($dimanche[1],2,',',' ');	// heures
	$lignes[]=join($cellules,$separateur);
      }
    }
  }

  //		Affichage des jours feries
  $lignes[]=null;
  $lignes[]="Jours fériés";
  if($nom=="agent"){
    $lignes[]=join(array("Nom","Prénom","Nombre de jours feriés","Dates","Heures"),$separateur);
  }
  else{
    $lignes[]=join(array(ucfirst($nom),"Nombre de jours feriés","Dates","Heures"),$separateur);
  }
  foreach($tab as $elem){
    foreach($elem[9] as $ferie){
      $cellules=Array();
      if($nom=="agent"){
	$cellules[]=$elem[0][1];	// nom
	$cellules[]=$elem[0][2];	// prénom
      }
      else{
	$cellules[]=$elem[0];		// nom du service ou du statut
      }
      $cellules[]=count($elem[9]);						// nombre de J. Feriés
      $cellules[]=dateFr($ferie[0]);						// date
      $cellules[]=number_format($ferie[1],2,',',' ');	// heures
      $lignes[]=join($cellules,$separateur);
    }
  }

  //	Affichage des 19-20h
  $lignes[]=null;
  $lignes[]="19h-20h";
  if($nom=="agent"){
    $lignes[]=join(array("Nom","Prénom","Nombre de 19h-20h","Dates"),$separateur);
  }
  else{
    $lignes[]=join(array(ucfirst($nom),"Nombre de 19h-20h","Dates"),$separateur);
  }
  
  foreach($tab as $elem){
    foreach($elem[7] as $h19){
      $cellules=Array();
      if($nom=="agent"){
	$cellules[]=$elem[0][1];	// nom
	$cellules[]=$elem[0][2];	// prénom
      }
      else{
	$cellules[]=$elem[0];		// nom du service ou du statut
      }
      $cellules[]=count($elem[7]);						// nombre de dimanche
      $cellules[]=dateFr($h19);						// date
      $lignes[]=join($cellules,$separateur);
    }
  }

  //	Affichage des 20-22h
  $lignes[]=null;
  $lignes[]="20h-22h";
  if($nom=="agent"){
    $lignes[]=join(array("Nom","Prénom","Nombre de 20h-22h","Dates"),$separateur);
  }
  else{
    $lignes[]=join(array(ucfirst($nom),"Nombre de 20h-22h","Dates"),$separateur);
  }
  foreach($tab as $elem){
    foreach($elem[8] as $h20){
      $cellules=Array();
      if($nom=="agent"){
	$cellules[]=$elem[0][1];	// nom
	$cellules[]=$elem[0][2];	// prénom
      }
      else{
	$cellules[]=$elem[0];		// nom du service ou du statut
      }
      $cellules[]=count($elem[7]);						// nombre de dimanche
      $cellules[]=dateFr($h20);						// date
      $lignes[]=join($cellules,$separateur);
    }
  }

  $lignes[]=null;
  $lignes[]="Absences";
  if($nom=="agent"){
    $lignes[]=join(array("Nom","Prénom","Heures d'absences","Dates","Heures"),$separateur);
  }
  else{
    $lignes[]=join(array(ucfirst($nom),"Heures d'absences","Dates","Heures"),$separateur);
  }
  foreach($tab as $elem){
    $total_absences=$elem[5];
    foreach($elem[4] as $absences){
      $cellules=Array();
      if($nom=="agent"){
	$cellules[]=$elem[0][1];	// nom
	$cellules[]=$elem[0][2];	// prénom
      }
      else{
	$cellules[]=$elem[0];		// nom du service ou du statut
      }
      $cellules[]=number_format($total_absences,2,',',' ');;						// heures total d'absences
      $cellules[]=dateFr($absences[0]);					// date
      $cellules[]=number_format($absences[1],2,',',' ');	// heures
      $lignes[]=join($cellules,$separateur);
    }
  }
  return $lignes;
}

?>