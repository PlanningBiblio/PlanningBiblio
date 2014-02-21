<?php
/*
Planning Biblio, Version 1.7.2
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : statistiques/postes_renfort.php
Création : mai 2011
Dernière modification : 20 janvier 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche les statistiques sur les postes de renfort : nombre d'heures d'ouverture, moyen par jour et par semaine, jours et 
heures d'ouverture

Page appelée par le fichier index.php, accessible par le menu statistiques / Poste de renfort
*/

require_once "class.statistiques.php";

echo "<h3>Statistiques par poste de renfort</h3>\n";

//	Variables :
$joursParSemaine=$config['Dimanche']?7:6;

include "include/horaires.php";
//		--------------		Initialisation  des variables 'debut','fin' et 'poste'		-------------------
if(!array_key_exists('stat_poste_tri',$_SESSION))
  $_SESSION['stat_poste_tri']=null;
if(!array_key_exists('stat_postes_r',$_SESSION))
  $_SESSION['stat_postes_r']=null;
if(!array_key_exists('stat_debut',$_SESSION)){
  $_SESSION['stat_debut']=null;
  $_SESSION['stat_fin']=null;
}
$debut=isset($_GET['debut'])?$_GET['debut']:$_SESSION['stat_debut'];
$fin=isset($_GET['fin'])?$_GET['fin']:$_SESSION['stat_fin'];
$postes=isset($_GET['postes'])?$_GET['postes']:$_SESSION['stat_postes_r'];
$tri=isset($_GET['tri'])?$_GET['tri']:$_SESSION['stat_poste_tri'];
$debutSQL=dateFr($debut);
$finSQL=dateFr($fin);

if(!$debut)
  $debut="01/01/".date("Y");
$_SESSION['stat_debut']=$debut;
if(!$fin)
  $fin=date("d/m/Y");
$_SESSION['stat_fin']=$fin;
$_SESSION['stat_postes_r']=$postes;
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
$selected=null;

//		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}postes` WHERE `obligatoire`='Renfort' AND `statistiques`='1' ORDER BY `etage`,`nom`;");
$postes_list=$db->result;

if(is_array($postes)){
  //	Recherche du nombre de jours concernés
  $db=new db();
  $db->query("SELECT `date` FROM `{$dbprefix}pl_poste` WHERE `date` BETWEEN '$debutSQL' AND '$finSQL' $reqSites GROUP BY `date`;");
  $nbJours=$db->nb;

  //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
  //	On stock le tout dans le tableau $resultat
  $postes_select=join($postes,",");
  $db=new db();
  $req="SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
    `{$dbprefix}pl_poste`.`date` as `date`,  `{$dbprefix}pl_poste`.`poste` as `poste`, 
    `{$dbprefix}personnel`.`nom` as `nom`, `{$dbprefix}personnel`.`prenom` as `prenom`, 
    `{$dbprefix}personnel`.`id` as `perso_id`, `{$dbprefix}pl_poste`.site as `site` 
    FROM `{$dbprefix}pl_poste` 
    INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` 
    WHERE `{$dbprefix}pl_poste`.`date`>='$debutSQL' AND `{$dbprefix}pl_poste`.`date`<='$finSQL' 
    AND `{$dbprefix}pl_poste`.`poste` IN ($postes_select) AND `{$dbprefix}pl_poste`.`absent`<>'1' 
    AND `{$dbprefix}pl_poste`.`supprime`<>'1' $reqSites ORDER BY `poste`,`date`,`debut`,`fin`;";
  $db->query($req);
  $resultat=$db->result;
  
  //	Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel)
  //	pour chaques postes sélectionnés
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

	    
    $agents=Array();
    $dates=Array();
    if(is_array($resultat)){
      foreach($resultat as $elem){
	if($poste==$elem['poste']){
	  // on créé un tableau par date
	  if(!array_key_exists($elem['date'],$dates)){
	    $dates[$elem['date']]=Array($elem['date'],Array(),0,"site"=>$elem['site']);
	  }
	  $dates[$elem['date']][1][]=Array($elem['debut'],$elem['fin'],diff_heures($elem['debut'],$elem['fin'],"decimal"));
	  $dates[$elem['date']][2]+=diff_heures($elem['debut'],$elem['fin'],"decimal");
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
	  $tab[$poste]=array($poste_tab,$dates,$heures,"sites"=>$sites);
	}
      }
    }
  }
}

// Heures et jours d'ouverture au public
$s=new statistiques();
$s->debut=$debutSQL;
$s->fin=$finSQL;
$s->joursParSemaine=$joursParSemaine;
$s->selectedSites=$selectedSites;
$s->ouverture();
$ouverture=$s->ouvertureTexte;

//		-------------		Tri du tableau		------------------------------
//	$tab[poste_id]=Array(Array(poste_id,poste_nom,etage,obligatoire),Array[perso_id]=Array(perso_id,nom,prenom,heures),heures)
usort($tab,$tri);

//	Passage en session du tableau pour le fichier export.php
$_SESSION['stat_tab']=$tab;
	
//		--------------		Affichage en 2 partie : formulaire à gauche, résultat à droite
echo "<table><tr style='vertical-align:top;'><td id='stat-col1'>\n";
//		--------------		Affichage du formulaire permettant de sélectionner les dates et les postes		-------------
echo "<form name='form' action='index.php' method='get'>\n";
echo "<input type='hidden' name='page' value='statistiques/postes_renfort.php' />\n";
echo "<table>\n";
echo "<tr><td>Début : </td>\n";
echo "<td><input type='text' name='debut' value='$debut' class='datepicker' />\n";
echo "</td></tr>\n";
echo "<tr><td>Fin : </td>\n";
echo "<td><input type='text' name='fin' value='$fin' class='datepicker' />\n";
echo "</td></tr>\n";
echo "<tr><td>Tri : </td>\n";
echo "<td>\n";
echo "<select name='tri'>\n";
echo "<option value='cmp_01'>Nom du poste</option>\n";
echo "<option value='cmp_02'>Etage</option>\n";
echo "<option value='cmp_2'>Heures du - au +</option>\n";
echo "<option value='cmp_2desc'>Heures du + au -</option>\n";
echo "</select>\n";
echo "</td></tr>\n";
echo "<tr style='vertical-align:top'><td>Postes : </td>\n";
echo "<td><select name='postes[]' multiple='multiple' size='20' onchange='verif_select(\"postes\");'>\n";
if(is_array($postes_list)){
  echo "<option value='Tous'>Tous</option>\n";
  foreach($postes_list as $elem){
    if(is_array($postes)){
      $selected=in_array($elem['id'],$postes)?"selected='selected'":null;
    }
    echo "<option value='{$elem['id']}' $selected class='td_renfort'>{$elem['nom']} ({$elem['etage']})</option>\n";
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
echo "<input type='button' value='Effacer' onclick='location.href=\"index.php?page=statistiques/postes_renfort.php&amp;debut=&amp;fin=&amp;postes=\"' />\n";
echo "&nbsp;&nbsp;<input type='submit' value='OK' />\n";
echo "</td></tr>\n";
echo "<tr><td colspan='2'><hr/></td></tr>\n";
echo "<tr><td>Exporter </td>\n";
echo "<td><a href='javascript:export_stat(\"postes_renfort\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
echo "<a href='javascript:export_stat(\"postes_renfort\",\"xsl\");'>XLS</a></td></tr>\n";
echo "</table>\n";
echo "</form>\n";

//		--------------------------		2eme partie (colonne)		--------------------------
echo "</td><td>\n";

// 		--------------------------		Affichage du tableau de résultat		--------------------
if($tab){
  echo "<b>Statistiques par poste de renfort du $debut au $fin</b>\n";
  echo $ouverture;
  echo "<table border='1' cellspacing='0' cellpadding='0'>\n";
  echo "<tr class='th'>\n";
  echo "<td style='width:200px; padding-left:8px;'>Postes</td>\n";
  echo "<td style='width:300px; padding-left:8px;'>Horaires</td></tr>\n";
  foreach($tab as $elem){
    echo "<tr style='vertical-align:top;' class='td_renfort'><td>\n";
    //	Affichage du nom du poste dans la 1ère colonne
    // Sites
    $siteEtage=array();
    if($config['Multisites-nombre']>1){
      for($i=1;$i<=$config['Multisites-nombre'];$i++){
	if($elem["sites"][$i]==$elem[2]){
	  $siteEtage[]=$config["Multisites-site{$i}"];
	  continue;
	}
      }
    }
    // Etages
    if($elem[0][2]){
      $siteEtage[]=$elem[0][2];
    }
    if(!empty($siteEtage)){
      $siteEtage="(".join(" ",$siteEtage).")";
    }
    else{
      $siteEtage=null;
    }

    $jour=$elem[2]/$nbJours;
    $hebdo=$jour*$joursParSemaine;
    echo "<table><tr><td colspan='2'><b>{$elem[0][1]}</b></td></tr>";
    echo "<tr><td colspan='2'><i>$siteEtage</i></td></tr>\n";
    echo "<tr><td>Total</td>";
    echo "<td style='text-align:right;'>".number_format($elem[2],2,',',' ')."</td></tr>\n";
    echo "<tr><td>Moyenne jour</td>";
    echo "<td style='text-align:right;'>".number_format(round($jour,2),2,',',' ')."</td></tr>\n";
    echo "<tr><td>Moyenne hebdo";
    echo "<td style='text-align:right;'>".number_format(round($hebdo,2),2,',',' ')."</td></tr>\n";
    if($config['Multisites-nombre']>1){
      for($i=1;$i<=$config['Multisites-nombre'];$i++){
	if($elem["sites"][$i] and $elem["sites"][$i]!=$elem[2]){
	  // Calcul des moyennes
	  $jour=$elem["sites"][$i]/$nbJours;
	  $hebdo=$jour*$joursParSemaine;
	  echo "<tr><td colspan='2' style='padding-top:20px;'><u>".$config["Multisites-site{$i}"]."</u></td></tr>";
	  echo "<tr><td>Total</td>";
	  echo "<td style='text-align:right;'>".number_format($elem["sites"][$i],2,',',' ')."</td></tr>";;
	  echo "<tr><td>Moyenne</td>";
	  echo "<td style='text-align:right;'>".number_format($hebdo,2,',',' ')."</td></tr>";
	}
      }
    }
    echo "</table>\n";
    echo "</td>\n";
    //	Affichage des horaires d'ouverture
    echo "<td style='padding-left:8px;'>";
    foreach($elem[1] as $date){
      echo "<b>".dateAlpha($date[0])." : ".number_format($date[2],1,',',' ')."</b><br/>";
      foreach($date[1] as $horaires){
	echo heure2($horaires[0])." - ".heure2($horaires[1])." : ".number_format($horaires[2],1,',',' ')."<br/>\n";
      }
      echo "<br/>\n";
    }
    echo "</td>\n";
    echo "</tr>\n";
  }
  echo "</table>\n";
}
//		----------------------			Fin d'affichage		----------------------------
echo "</td></tr></table>\n";
echo "<script type='text/JavaScript'>document.form.tri.value='$tri';</script>\n";
?>