<?php
/*
Planning Biblio, Version 1.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2013 - Jérôme Combes

Fichier : statistiques/postes_synthese.php
Création : mai 2011
Dernière modification : 16 septembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le nombre total d'heures d'ouverture de chaque poste, la moyen par jour et par semaine

Page appelée par le fichier index.php, accessible par le menu statistiques / Par poste (Synthèse)
*/

require_once "class.statistiques.php";

echo "<h3>Statistiques par poste</h3>\n";
//	Variables :
$joursParSemaine=$config['Dimanche']?7:6;

include "include/horaires.php";
//		--------------		Initialisation  des variables 'debut','fin' et 'poste'		-------------------
if(!array_key_exists('stat_poste_postes',$_SESSION)){
  $_SESSION['stat_poste_postes']=null;
  $_SESSION['stat_poste_tri']=null;
}
if(!array_key_exists('stat_debut',$_SESSION)){
  $_SESSION['stat_debut']=null;
  $_SESSION['stat_fin']=null;
}
$debut=isset($_GET['debut'])?$_GET['debut']:$_SESSION['stat_debut'];
$fin=isset($_GET['fin'])?$_GET['fin']:$_SESSION['stat_fin'];
$postes=isset($_GET['postes'])?$_GET['postes']:$_SESSION['stat_poste_postes'];
$tri=isset($_GET['tri'])?$_GET['tri']:$_SESSION['stat_poste_tri'];
if(!$debut)
  $debut=date("Y")."-01-01";
$_SESSION['stat_debut']=$debut;
if(!$fin)
  $fin=date("Y-m-d");
$_SESSION['stat_fin']=$fin;
$_SESSION['stat_poste_postes']=$postes;
if(!$tri)
  $tri="cmp_01";
$_SESSION['stat_poste_tri']=$tri;

// Filtre les sites
if(!array_key_exists('stat_poste_sites',$_SESSION)){
  $_SESSION['stat_poste_sites']=null;
}
$selectedSites=isset($_GET['selectedSites'])?$_GET['selectedSites']:$_SESSION['stat_poste_sites'];
if($config['Multisites-nombre']>1 and !$selectedSites){
  $selectedSites=array();
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    $selectedSites[]=$i;
  }
}
$_SESSION['stat_poste_sites']=$selectedSites;

// Filtre les sites dans les requêtes SQL
if($config['Multisites-nombre']>1 and is_array($selectedSites)){
  $reqSites="AND `{$dbprefix}pl_poste`.`site` IN (0,".join(",",$selectedSites).")";
}
else{
  $reqSites=null;
}

$tab=array();

$total_heures=0;
$total_jour=0;
$total_hebdo=0;
$selected=null;

//		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}postes` WHERE `statistiques`='1' ORDER BY `etage`,`nom`;");
$postes_list=$db->result;

if(is_array($postes)){
  //	Recherche du nombre de jours concernés
  $db=new db();
  $db->query("SELECT `date` FROM `{$dbprefix}pl_poste` WHERE `date` BETWEEN '$debut' AND '$fin' $reqSites GROUP BY `date`;");
  $nbJours=$db->nb;
  $nbSemaines=$nbJours>0?$nbJours/$joursParSemaine:1;
  
  //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
  //	On stock le tout dans le tableau $resultat
  $postes_select=join($postes,",");
  $db=new db();
  $req="SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
    `{$dbprefix}pl_poste`.`date` as `date`,  `{$dbprefix}pl_poste`.`poste` as `poste`, 
    `{$dbprefix}personnel`.`nom` as `nom`, `{$dbprefix}personnel`.`prenom` as `prenom`, 
    `{$dbprefix}personnel`.`id` as `perso_id`, `{$dbprefix}pl_poste`.site as `site` 
    FROM `{$dbprefix}pl_poste` INNER JOIN `{$dbprefix}personnel` 
    ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` 
    WHERE `{$dbprefix}pl_poste`.`date`>='$debut' AND `{$dbprefix}pl_poste`.`date`<='$fin' 
    AND `{$dbprefix}pl_poste`.`poste` IN ($postes_select) AND `{$dbprefix}pl_poste`.`absent`<>'1' 
    AND `{$dbprefix}pl_poste`.`supprime`<>'1' $reqSites ORDER BY `poste`,`nom`,`prenom`;";
  $db->query($req);
  $resultat=$db->result;
  
  //	Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel) pour chaque poste sélectionné
  foreach($postes as $poste){
    if(array_key_exists($poste,$tab)){
      $heures=$tab[$poste][2];
      $sites=$tab[$poste]["sites"];
    }
    else{
      $heures=0;
      for($i=1;$i<=$config['Multisites-nombre'];$i++){
	$sites[$i]=0;
      }
    }
    $agents=array();
    if(is_array($resultat)){
      foreach($resultat as $elem){
	if($poste==$elem['poste']){
	  //	On créé un tableau par agent avec son nom, prénom et la somme des heures faites par poste
	  if(!array_key_exists($elem['perso_id'],$agents)){
	    $agents[$elem['perso_id']]=Array($elem['perso_id'],$elem['nom'],$elem['prenom'],0,"site"=>$elem['site']);
	  }
	  $agents[$elem['perso_id']][3]+=diff_heures($elem['debut'],$elem['fin'],"decimal");
	  // On compte les heures de chaque site
	  if($config['Multisites-nombre']>1){
	    $sites[$elem['site']]+=diff_heures($elem['debut'],$elem['fin'],"decimal");
	  }
	  // On compte toutes les heures (globales)
	  $heures+=diff_heures($elem['debut'],$elem['fin'],"decimal");
	  
	  foreach($postes_list as $elem2){
	    if($elem2['id']==$poste){	// on créé un tableau avec le nom et l'étage du poste.
	      $poste_tab=array($poste,$elem2['nom'],$elem2['etage'],$elem2['obligatoire']);
	      break;
	    }
	  }
	//	On met dans tab tous les éléments (infos postes + agents + heures du poste)
	$tab[$poste]=array($poste_tab,$agents,$heures,"sites"=>$sites);
	}
      }
    }
  }
}

//		-------------		Tri du tableau		------------------------------
// $tab[poste_id]=Array(Array(poste_id,poste_nom,etage,obligatoire),Array[perso_id]=Array(perso_id,nom,prenom,heures),heures)
usort($tab,$tri);

// passage en session du tableau pour le fichier export.php
$_SESSION['stat_tab']=$tab;
	
//		--------------		Affichage en 2 partie : formulaire à gauche, résultat à droite
echo "<table><tr style='vertical-align:top;'><td style='width:300px;'>\n";
//		--------------		Affichage du formulaire permettant de sélectionner les dates et les postes		-------------
echo "<form name='form' action='index.php' method='get'>\n";
echo "<input type='hidden' name='page' value='statistiques/postes_synthese.php' />\n";
echo "<table>\n";
echo "<tr><td>Début : </td>\n";
echo "<td><input type='text' name='debut' value='$debut' />&nbsp;<img src='img/calendrier.gif' onclick='calendrier(\"debut\");' alt='calendrier' />\n";
echo "</td></tr>\n";
echo "<tr><td>Fin : </td>\n";
echo "<td><input type='text' name='fin' value='$fin' />&nbsp;<img src='img/calendrier.gif' onclick='calendrier(\"fin\");' alt='calendrier' />\n";
echo "</td></tr>\n";
echo "<tr><td>Tri : </td>\n";
echo "<td>\n";
echo "<select name='tri'>\n";
echo "<option value='cmp_01'>Nom du poste</option>\n";
echo "<option value='cmp_02'>Etage</option>\n";
echo "<option value='cmp_03'>Obligatoire</option>\n";
echo "<option value='cmp_03desc'>Renfort</option>\n";
echo "<option value='cmp_2'>Heures du - au +</option>\n";
echo "<option value='cmp_2desc'>Heures du + au -</option>\n";
echo "</select>\n";
echo "</td></tr>\n";
echo "<tr style='vertical-align:top'><td>Postes : </td>\n";
echo "<td><select name='postes[]' multiple='multiple' size='20' onchange='verif_select(\"postes\");'>\n";
if(is_array($postes_list)){
  echo "<option value='Tous'>Tous</option>\n";
  foreach($postes_list as $elem){
    if($postes){
      $selected=in_array($elem['id'],$postes)?"selected='selected'":null;
    }
    $color=$elem['obligatoire']=="Obligatoire"?"#00FA92":"#FFFFFF";
    echo "<option value='{$elem['id']}' $selected style='background:$color;'>{$elem['nom']} ({$elem['etage']})</option>\n";
  }
}
echo "</select></td></tr>\n";

if($config['Multisites-nombre']>1){
  $nbSites=$config['Multisites-nombre'];
  echo "<tr style='vertical-align:top'><td>Sites : </td>\n";
  echo "<td><select name='selectedSites[]' multiple='multiple' size='".($nbSites+1)."' onchange='verif_select(\"selectedSites\");'>\n";
  echo "<option value='Tous'>Tous</option>\n";
  for($i=1;$i<=$nbSites;$i++){
    $selected=in_array($i,$selectedSites)?"selected='selected'":null;
    echo "<option value='$i' $selected>{$config["Multisites-site$i"]}</option>\n";
  }
  echo "</select></td></tr>\n";
}

echo "<tr><td colspan='2' style='text-align:center;'>\n";
echo "<input type='button' value='Effacer' onclick='location.href=\"index.php?page=statistiques/postes_synthese.php&amp;debut=&amp;fin=&amp;postes=\"' />\n";
echo "&nbsp;&nbsp;<input type='submit' value='OK' />\n";
echo "</td></tr>\n";
echo "<tr><td colspan='2'><hr/></td></tr>\n";
echo "<tr><td>Exporter </td>\n";
echo "<td><a href='javascript:export_stat(\"postes_synthese\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
echo "<a href='javascript:export_stat(\"postes_synthese\",\"xsl\");'>XLS</a></td></tr>\n";
echo "</table>\n";
echo "</form>\n";

//		--------------------------		2eme partie (colonne)		--------------------------
echo "</td><td>\n";

// 		--------------------------		Affichage du tableau de résultat		--------------------
if($tab){
  echo "<b>Statistiques par poste (Synthèse) du ".dateFr($debut)." au ".dateFr($fin)."</b><br/>\n";
  echo $nbJours>1?"$nbJours jours, ":"$nbJours jour, ";
  echo $nbSemaines>1?number_format($nbSemaines,1,',',' ')." semaines":number_format($nbSemaines,1,',',' ')." semaine";

  echo "<table border='1' cellspacing='0' cellpadding='0'>\n";
  echo "<tr class='th'>\n";
  echo "<td style='width:200px; padding-left:8px;'>Postes</td>\n";
  echo "<td style='width:100px; padding-left:8px;'>Total d'heures</td>\n";
  echo "<td style='width:100px; padding-left:8px;'>Moyenne jour</td>\n";
  echo "<td style='width:180px; padding-left:8px;'>Moyenne hebdomadaire</td></tr>\n";
  foreach($tab as $elem){
    $color=$elem[0][3]=="Obligatoire"?"#00FA92":"#FFFFFF";
    $jour=$elem[2]/$nbJours;
    $hebdo=$jour*$joursParSemaine;
    $total_heures+=$elem[2];
    $total_jour+=$jour;
    $total_hebdo+=$hebdo;
    $siteEtage=array();
    if($config['Multisites-nombre']>1){
      for($i=1;$i<=$config['Multisites-nombre'];$i++){
	if($elem["sites"][$i]==$elem[2]){
	  $siteEtage[]=$config["Multisites-site{$i}"];
	  continue;
	}
      }
    }
    if($elem[0][2]){
      $siteEtage[]=$elem[0][2];
    }
    if(!empty($siteEtage)){
      $siteEtage="(".join(" ",$siteEtage).")";
    }
    echo "<tr style='vertical-align:top; background:$color;'>\n";
    echo "<td style='padding-left:8px;'><b>{$elem[0][1]}</b><br/><i>$siteEtage</i></td>\n";
    echo "<td style='padding-right:8px;text-align:right;'>".number_format(round($elem[2],2),2,',',' ')."</td>\n";
    echo "<td style='padding-right:8px;text-align:right;'>".number_format(round($jour,2),2,',',' ')."</td>\n";
    echo "<td style='padding-right:8px;text-align:right;'>".number_format(round($hebdo,2),2,',',' ')."</td></tr>\n";
  }
  echo "<tr><td style='padding-left:8px;'><b>Total</b></td>\n";
  echo "<td style='padding-right:8px;text-align:right;'>".number_format(round($total_heures,2),2,',',' ')."</td>\n";
  echo "<td style='padding-right:8px;text-align:right;'>".number_format(round($total_jour,2),2,',',' ')."</td>\n";
  echo "<td style='padding-right:8px;text-align:right;'>".number_format(round($total_hebdo,2),2,',',' ')."</td></tr>\n";
  echo "</table>\n";
}
//		----------------------			Fin d'affichage		----------------------------
echo "</td></tr></table>\n";
echo "<script type='text/JavaScript'>document.form.tri.value='$tri';</script>\n";
?>