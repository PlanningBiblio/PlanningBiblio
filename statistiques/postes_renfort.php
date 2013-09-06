<?php
/********************************************************************************************************************************
* Planning Biblio, Version 1.5.5													*
* Licence GNU/GPL (version 2 et au dela)											*
* Voir les fichiers README.txt et COPYING.txt											*
* Copyright (C) 2011-2013 - Jérôme Combes											*
*																*
* Fichier : statistiques/postes_renfort.php											*
* Création : mai 2011														*
* Dernière modification : 17 décembre 2012											*
* Auteur : Jérôme Combes, jerome@planningbilbio.fr										*
*																*
* Description :															*
* Affiche les statistiques sur les postes de renfort : nombre d'heures d'ouverture, moyen par jour et par semaine, jours et 	*
* heures d'ouverture														*
*																*
* Page appelée par le fichier index.php, accessible par le menu statistiques / Poste de renfort					*
*********************************************************************************************************************************/

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
if(!$debut)
  $debut=date("Y")."-01-01";
$_SESSION['stat_debut']=$debut;
if(!$fin)
  $fin=date("Y-m-d");
$_SESSION['stat_fin']=$fin;
$_SESSION['stat_postes_r']=$postes;
if(!$tri)
  $tri="cmp_01";
$_SESSION['stat_poste_tri']=$tri;
$tab=array();
$selected=null;

//		--------------		Récupération de la liste des postes pour le menu déroulant		------------------------
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}postes` WHERE `obligatoire`='Renfort' AND `statistiques`='1' ORDER BY `etage`,`nom`;");
$postes_list=$db->result;

if(is_array($postes)){
  //	Recherche du nombre de jours concernés
  $db=new db();
  $db->query("SELECT `date` FROM `{$dbprefix}pl_poste` WHERE `date` BETWEEN '$debut' AND '$fin' GROUP BY `date`;");
  $nbJours=$db->nb;
  $nbSemaines=$nbJours>0?$nbJours/$joursParSemaine:1;
  
  //	Recherche des infos dans pl_poste et personnel pour tous les postes sélectionnés
  //	On stock le tout dans le tableau $resultat
  $postes_select=join($postes,",");
  $db=new db();
  $req="SELECT `{$dbprefix}pl_poste`.`debut` as `debut`, `{$dbprefix}pl_poste`.`fin` as `fin`, 
    `{$dbprefix}pl_poste`.`date` as `date`,  `{$dbprefix}pl_poste`.`poste` as `poste`, 
    `{$dbprefix}personnel`.`nom` as `nom`, `{$dbprefix}personnel`.`prenom` as `prenom`, 
    `{$dbprefix}personnel`.`id` as `perso_id` FROM `{$dbprefix}pl_poste` 
    INNER JOIN `{$dbprefix}personnel` ON `{$dbprefix}pl_poste`.`perso_id`=`{$dbprefix}personnel`.`id` 
    WHERE `{$dbprefix}pl_poste`.`date`>='$debut' AND `{$dbprefix}pl_poste`.`date`<='$fin' 
    AND `{$dbprefix}pl_poste`.`poste` IN ($postes_select) AND `{$dbprefix}pl_poste`.`absent`<>'1' 
    AND `{$dbprefix}pl_poste`.`supprime`<>'1' ORDER BY `poste`,`date`,`debut`,`fin`;";
  $db->query($req);
  $resultat=$db->result;
  
  //	Recherche des infos dans le tableau $resultat (issu de pl_poste et personnel)
  //	pour chaques postes sélectionnés
  foreach($postes as $poste){
    if(array_key_exists($poste,$tab))
      $heures=$tab[$poste][2];
    else
      $heures=0;
	    
    $agents=Array();
    $dates=Array();
    if(is_array($resultat)){
      foreach($resultat as $elem){
	if($poste==$elem['poste']){
	  // on créé un tableau par date
	  if(!array_key_exists($elem['date'],$dates)){
	    $dates[$elem['date']]=Array($elem['date'],Array(),0);
	  }
	  $dates[$elem['date']][1][]=Array($elem['debut'],$elem['fin'],diff_heures($elem['debut'],$elem['fin'],"decimal"));
	  $dates[$elem['date']][2]+=diff_heures($elem['debut'],$elem['fin'],"decimal");
	  $heures+=diff_heures($elem['debut'],$elem['fin'],"decimal");
	  foreach($postes_list as $elem2){
	    if($elem2['id']==$poste){	// on créé un tableau avec le nom et l'étage du poste.
	      $poste_tab=array($poste,$elem2['nom'],$elem2['etage'],$elem2['obligatoire']);
	      break;
	    }
	  }
	  //	On met dans tab tous les éléments (infos postes + agents + heures du poste)
	  $tab[$poste]=array($poste_tab,$dates,$heures);
	}
      }
    }
  }
}
//		-------------		Tri du tableau		------------------------------
//	$tab[poste_id]=Array(Array(poste_id,poste_nom,etage,obligatoire),Array[perso_id]=Array(perso_id,nom,prenom,heures),heures)
usort($tab,$tri);

//	Passage en session du tableau pour le fichier export.php
$_SESSION['stat_tab']=$tab;
	
//		--------------		Affichage en 2 partie : formulaire à gauche, résultat à droite
echo "<table><tr style='vertical-align:top;'><td style='width:300px;'>\n";
//		--------------		Affichage du formulaire permettant de sélectionner les dates et les postes		-------------
echo "<form name='form' action='index.php' method='get'>\n";
echo "<input type='hidden' name='page' value='statistiques/postes_renfort.php' />\n";
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
    $color=$elem['obligatoire']=="Obligatoire"?"#00FA92":"#FFFFFF";
    echo "<option value='{$elem['id']}' $selected style='background:$color;'>{$elem['nom']} ({$elem['etage']})</option>\n";
  }
}
echo "</select></td></tr>\n";
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
  echo "<b>Statistiques par poste de renfort du ".dateFr($debut)." au ".dateFr($fin)."</b><br/>\n";
  echo $nbJours>1?"$nbJours jours, ":"$nbJours jour, ";
  echo $nbSemaines>1?number_format($nbSemaines,1,',',' ')." semaines":number_format($nbSemaines,1,',',' ')." semaine";
  echo "<table border='1' cellspacing='0' cellpadding='0'>\n";
  echo "<tr class='th'>\n";
  echo "<td style='width:200px; padding-left:8px;'>Postes</td>\n";
  echo "<td style='width:300px; padding-left:8px;'>Horaires</td></tr>\n";
  foreach($tab as $elem){
    $color=$elem[0][3]=="Obligatoire"?"#00FA92":"#FFFFFF";
    echo "<tr style='vertical-align:top; background:$color;'>\n";
    //	Affichage du nom du poste dans la 1ère colonne
    echo "<td style='padding-left:8px;'><b>{$elem[0][1]} ({$elem[0][2]})</b><br/><br/>\n";
    echo "Total : ".number_format($elem[2],2,',',' ')." heures<br/>\n";
    $jour=$elem[2]/$nbJours;
    $hebdo=$jour*$joursParSemaine;
    echo "Moyenne jour. : ".number_format(round($jour,2),2,',',' ')." heures<br/>\n";
    echo "Moyenne hebdo. : ".number_format(round($hebdo,2),2,',',' ')." heures<br/>\n";
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