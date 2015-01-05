<?php
/*
Planning Biblio, Version 1.8.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : statistiques/absences.php
Création : 15 mai 2014
Dernière modification : 13 juin 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche des statistiques sur les absences

Page appelée par le fichier index.php, accessible par le menu statistiques / Absences
*/

require_once "class.statistiques.php";
require_once "absences/class.absences.php";

//	Initialisation des variables
$afficheHeures=in_array("planningHebdo",$plugins)?true:false;
$colspan=$afficheHeures?2:1;
$rowspan=$afficheHeures?2:1;

if(isset($_GET['debut'])){
  $debut=$_GET['debut'];
  $fin=$_GET['fin']?$_GET['fin']:$debut;
  $site=isset($_GET['site'])?$_GET['site']:0;
}
elseif(array_key_exists("stat_absences_debut",$_SESSION['oups'])){
  $debut=$_SESSION['oups']['stat_absences_debut'];
  $fin=$_SESSION['oups']['stat_absences_fin'];
  $site=isset($_SESSION['oups']['stat_absences_site'])?$_SESSION['oups']['stat_absences_site']:0;
}
else{
  $date=$_SESSION['PLdate'];
  $d=new datePl($date);
  $debut=dateFr($d->dates[0]);
  $fin=$config['Dimanche']?dateFr($d->dates[6]):dateFr($d->dates[5]);
  $site=0;
}
$_SESSION['oups']['stat_absences_debut']=$debut;
$_SESSION['oups']['stat_absences_fin']=$fin;

$debutSQL=dateSQL($debut);
$finSQL=dateSQL($fin);

$sites=null;
if($config['Multisites-nombre']>1){
  $sites=array();
  if($site==0){
    for($i=1;$i<=$config['Multisites-nombre'];$i++){
      $sites[]=$i;
    }
  }
  else{
    $sites=array($site);
  }
}

// Recherche des absences
$a=new absences();
$a->valide=true;
$a->fetch("`debut`,`fin`,`nom`,`prenom`",null,null,$debutSQL,$finSQL,$sites);
$absences=$a->elements;

// Recherche des motifs d'absences
$motifs=array();
if(is_array($absences) and !empty($absences)){
  foreach($absences as $elem){
    if(!in_array($elem['motif'],$motifs)){
      $motifs[]=$elem['motif'];
    }
  }
}
sort($motifs);

// Regroupe les absences par agent et par motif
// Et ajoute les heures correspondantes
$tab=array();
$totaux=array("_general"=>0,"_generalHeures"=>0);
foreach($absences as $elem){
  if(!array_key_exists($elem['perso_id'],$tab)){
    $tab[$elem['perso_id']]=array("nom"=>$elem['nom'],"prenom"=>$elem['prenom'],"total"=>0,"totalHeures"=>0);
  }
  if(!array_key_exists($elem['motif'],$tab[$elem['perso_id']])){
    $tab[$elem['perso_id']][$elem['motif']]=array("total"=>0,"heures"=>0);
  }
  if(!array_key_exists($elem['motif'],$totaux)){
    $totaux[$elem['motif']]=array("frequence"=>0,"heures"=>0);
  }
  
  // Total agent
  $tab[$elem['perso_id']]['total']++;
  // Totaux généraux
  $totaux['_general']++;
  // Total agent pour le motif courant
  $tab[$elem['perso_id']][$elem['motif']]['total']++;
  // Total pour ce motif
  $totaux[$elem['motif']]['frequence']++;

  // Ajout des heures d'absences
  if($afficheHeures){
    $a=new absences();
    $a->calculTemps($elem['debut'],$elem['fin'],$elem['perso_id']);
    $heures=$a->heures;

    // heures agent pour le motif courant
    if($a->error){
      $tab[$elem['perso_id']][$elem['motif']]['heures']="N/A";
    }
    elseif(is_numeric($tab[$elem['perso_id']][$elem['motif']]['heures'])){
      $tab[$elem['perso_id']][$elem['motif']]['heures']+=$heures;
    }
    // Total heures pour ce motif
    if($a->error){
      $totaux[$elem['motif']]['heures']="N/A";
    }
    elseif(is_numeric($totaux[$elem['motif']]['heures'])){
      $totaux[$elem['motif']]['heures']+=$heures;
    }

    if($a->error){
      // Total heures agent
      $tab[$elem['perso_id']]['totalHeures']="N/A";
      // Totaux heures généraux
      $totaux['_generalHeures']="N/A";
    }
    else{
      // Total heures agent
      if(is_numeric($tab[$elem['perso_id']]['totalHeures'])){
	$tab[$elem['perso_id']]['totalHeures']+=$heures;
      }
      // Totaux heures généraux
      if(is_numeric($totaux['_generalHeures'])){
	$totaux['_generalHeures']+=$heures;
      }
    }
  }
}

// Pour les exports
$_SESSION['oups']['stat_absences_motifs']=$motifs;
$_SESSION['oups']['stat_absences_totaux']=$totaux;
$_SESSION['stat_tab']=$tab;

// Affichage du tableau
echo <<<EOD
<h3>Statistiques sur les absences</h3>

<table>
<tr><td style='width:350px;'><b>Du $debut au $fin</b></td>
<td>
<form name='form' method='get' action='index.php'>
<input type='hidden' name='page' value='statistiques/absences.php' />
<input type='hidden' id='afficheHeures' value='$afficheHeures' />
D&eacute;but <input type='text' name='debut' class='datepicker' value='$debut' />&nbsp;
Fin <input type='text' name='fin' class='datepicker' value='$fin' />&nbsp;
EOD;
if($config['Multisites-nombre']>1){
  echo "Sites : ";
  echo "<select name='site'>\n";
  echo "<option value='0'>Tous</option>\n";
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    $selected=$i==$site?"selected='selected'":null;
    echo "<option value='$i' $selected>{$config["Multisites-site$i"]}</option>\n";
  }
  echo "</select>&nbsp;\n";
}
echo <<<EOD
<input type='submit' value='OK' id='submit' class='ui-button'/></form>
</td></tr></table>
<br/>
EOD;

echo <<<EOD
<table id='dataTableStatAbsences'>
<thead><tr>
  <th rowspan='$rowspan'>Agents</th>
  <th rowspan='$rowspan'>Total<br/>d'absences</th>
EOD;
if($afficheHeures){
  echo "<th rowspan='$rowspan'>Total<br/>d'heures</th>\n";
}

foreach($motifs as $elem){
  echo "<th colspan='$colspan'>$elem</th>\n";
}

if($afficheHeures){
  echo "</tr>\n<tr>";
  foreach($motifs as $elem){
    echo "<th>Nombre</th>";
    echo "<th>Heures</th>";
  }
}

echo "</tr></thead>\n";
echo "<tbody>\n";

foreach($tab as $elem){
  echo "<tr><td>{$elem['nom']} {$elem['prenom']}</td>\n";
  echo "<td class='center nowrap'>{$elem['total']}</td>\n";
  if($afficheHeures){
    echo "<td class='center nowrap'>".heure4($elem['totalHeures'])."</td>\n";
  }
  foreach($motifs as $motif){
    $nb=array_key_exists($motif,$elem)?$elem[$motif]['total']:"-";
    $heures=null;
    if(array_key_exists($motif,$elem) and $afficheHeures){
      if(is_numeric($elem[$motif]['heures'])){
	$heures=heure4($elem[$motif]['heures']);
      }else{
	$heures="N/A";
      }
    }
    $class=array_key_exists($motif,$elem)?"bg-yellow":null;
    echo "<td class='center nowrap $class'>$nb</td>\n";
    if($afficheHeures){
      echo "<td class='center nowrap $class'>$heures</td>\n";
    }
  }
  echo "</tr>\n";
}
echo "</tbody>\n";

// Affichage de la ligne "Totaux"
echo "<tfoot><tr style='background:#DDDDDD;'><th>Totaux</th>\n";
echo "<th class='nowrap'>{$totaux['_general']}</th>\n";
if($afficheHeures){
  $heures=is_numeric($totaux['_generalHeures'])?heure4($totaux['_generalHeures']):"N/A";
  echo "<th class='nowrap'>$heures</th>\n";
}
foreach($motifs as $motif){
  echo "<th class='nowrap'>{$totaux[$motif]['frequence']}</th>\n";
  if($afficheHeures){
    $heures=is_numeric($totaux[$motif]['heures'])?heure4($totaux[$motif]['heures']):"N/A";
    echo "<th class='nowrap'>$heures</th>\n";
  }
}
echo "</tr></tfoot>\n";

echo "</table>\n";

echo "<br/>Exporter \n";
echo "<a href='javascript:export_stat(\"absences\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
echo "<a href='javascript:export_stat(\"absences\",\"xls\");'>XLS</a>\n";