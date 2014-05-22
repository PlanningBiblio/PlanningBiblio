<?php
/*
Planning Biblio, Version 1.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : statistiques/absences.php
Création : 15 mai 2014
Dernière modification : 16 mai 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche des statistiques sur les absences

Page appelée par le fichier index.php, accessible par le menu statistiques / Absences
*/

require_once "class.statistiques.php";
require_once "absences/class.absences.php";

//	Initialisation des variables
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
$tab=array();
$totaux=array("_general"=>0);
foreach($absences as $elem){
  if(!array_key_exists($elem['perso_id'],$tab)){
    $tab[$elem['perso_id']]=array("nom"=>$elem['nom'],"prenom"=>$elem['prenom'],"total"=>0);
  }
  if(!array_key_exists($elem['motif'],$tab[$elem['perso_id']])){
    $tab[$elem['perso_id']][$elem['motif']]=array("total"=>0);
  }
  if(!array_key_exists($elem['motif'],$totaux)){
    $totaux[$elem['motif']]=0;
  }

  $tab[$elem['perso_id']]['total']++;
  $totaux['_general']++;
  $tab[$elem['perso_id']][$elem['motif']]['total']++;
  $totaux[$elem['motif']]++;
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
Début : <input type='text' name='debut' class='datepicker' value='$debut' />&nbsp;
Fin : <input type='text' name='fin' class='datepicker' value='$fin' />&nbsp;
EOD;
if($config['Multisites-nombre']>1){
  echo "Sites : ";
  echo "<select name='site'>\n";
  echo "<option value='0'>Tous'>\n";
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    $selected=$i==$site?"selected='selected'":null;
    echo "<option value='$i' $selected>{$config['Multisites-site$i']}</option>\n";
  }
  echo "</select>&nbsp;\n";
}
echo <<<EOD
<input type='submit' value='OK' id='submit' class='ui-button'/></form>
</td></tr></table>
<br/>
EOD;

echo "<table id='dataTableStatAbsences'>\n";
echo "<thead><tr><th>Agents</th><th>Nombre d'absences</th>\n";
foreach($motifs as $elem){
  echo "<th>$elem</th>\n";
}

echo "</tr></thead>\n";
echo "<tbody>\n";

foreach($tab as $elem){
  echo "<tr><td>{$elem['nom']} {$elem['prenom']}</td>\n";
  echo "<td class='center bold'>{$elem['total']}</td>\n";
  foreach($motifs as $motif){
    $nb=array_key_exists($motif,$elem)?$elem[$motif]['total']:"-";
    $class=array_key_exists($motif,$elem)?"bg-yellow":null;
    echo "<td class='center $class'>$nb</td>\n";
  }
  echo "</tr>\n";
}
echo "</tbody>\n";

// Affichage de la ligne "Totaux"
echo "<tfoot><tr style='background:#DDDDDD;'><th>Totaux</th>\n";
echo "<th>{$totaux['_general']}</th>\n";
foreach($motifs as $motif){
  echo "<th>{$totaux[$motif]}</th>\n";
}
echo "</tr></tfoot>\n";

echo "</table>\n";

echo "<br/>Exporter \n";
echo "<a href='javascript:export_stat(\"absences\",\"csv\");'>CSV</a>&nbsp;&nbsp;\n";
echo "<a href='javascript:export_stat(\"absences\",\"xls\");'>XLS</a>\n";