<?php
/**
Planning Biblio, Version 2.4.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planningHebdo/index.php
Création : 23 juillet 2013
Dernière modification : 3 octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche la liste des plannings de présence pour l'administrateur
Page accessible à partir du menu administration/planning de présence
*/

require_once "class.planningHebdo.php";

// Initialisation des variables
$debut=filter_input(INPUT_GET,"debut",FILTER_SANITIZE_STRING);
$fin=filter_input(INPUT_GET,"fin",FILTER_SANITIZE_STRING);
$reset=filter_input(INPUT_GET,"reset",FILTER_SANITIZE_STRING);

$debut=filter_var($debut,FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$fin=filter_var($fin,FILTER_CALLBACK,array("options"=>"sanitize_dateFr"));
$reset=filter_var($reset,FILTER_CALLBACK,array("options"=>"sanitize_on"));

if(!$debut){
  $debut=array_key_exists("planningHebdoDebut",$_SESSION['oups'])?$_SESSION['oups']['planningHebdoDebut']:null;
}

if(!$fin){
  $fin=array_key_exists("planningHebdoFin",$_SESSION['oups'])?$_SESSION['oups']['planningHebdoFin']:null;
}

if($reset){
  $debut=null;
  $fin=null;
}
$_SESSION['oups']['planningHebdoDebut']=$debut;
$_SESSION['oups']['planningHebdoFin']=$fin;
$message=null;

// Recherche des plannings
$p=new planningHebdo();
$p->debut=dateFr($debut);
$p->fin=dateFr($fin);
$p->fetch();

echo"<h3>Plannings de présence</h3>\n";

/*
// Période définies = 0 pour le moment. Option sans doute plus utilisée. Développements complexes.
if($config['PlanningHebdo-PeriodesDefinies']){
  echo "<div id='config' style='padding:10px; text-align:right;'>\n";
  echo "<a href='index.php?page=planningHebdo/configuration.php'>Configurer les p&eacute;riodes</a>\n";
  echo "</div>\n";
}
*/
echo <<<EOD
<div id='buttons'>
<form name='form' method='get' action='index.php'>
<input type='hidden' name='page' value='planningHebdo/index.php' />
Début : <input type='text' name='debut' class='datepicker' value='$debut' />
&nbsp;&nbsp;Fin : <input type='text' name='fin' class='datepicker' value='$fin' />
&nbsp;&nbsp;<input type='submit' value='OK' class='ui-button' />
&nbsp;&nbsp;<input type='button' value='Effacer' onclick='location.href="index.php?page=planningHebdo/index.php&amp;reset=on"' class='ui-button' />
<a class='ui-button' href='index.php?page=planningHebdo/modif.php&amp;retour=index.php' style='position:absolute; right:10px;'>Entrer un nouveau planning</a>
</div>
</form>

<br/>

<table id='tablePlanningHebdo' class='CJDataTable' data-sort='[[3],[4],[1]]'>
<thead>
  <tr>
    <th class='dataTableNoSort'>&nbsp;</th>
    <th>Agent</th>
    <th>Service</th>
    <th class='dataTableDateFR'>Début</th>
    <th class='dataTableDateFR'>Fin</th>
    <th class='dataTableDateFR'>Saisie</th>
    <th>Validation</th>
    <th>Actuel</th>
    <th>Commentaires</th>
  </tr>
</thead>
<tbody>
EOD;
foreach($p->elements as $elem){
  $actuel=$elem['actuel']?"Oui":null;
  $validation="<font style='display:none;'>En attente</font><b>En attente</b>";
  if($elem['valide']){
    $validation="<font style='display:none;'>Valid {$elem['validation']}</font>";
    $validation.=dateFr($elem['validation'],true);
    // 99999 : ID cron : donc pas de nom a afficher
    if($elem['valide'] != 99999){
      $validation.=", ".nom($elem['valide']);
    }
  }
  $planningRemplace=$elem['remplace']==0?dateFr($elem['saisie'],true):$planningRemplace;
  $commentaires=$elem['remplace']?"Remplace le planning <br/>du $planningRemplace":null;
  $arrow=$elem['remplace']?"<font style='font-size:20pt;'>&rdsh;</font>":null;

  echo "<tr id='tr_{$elem['id']}'>";
  echo "<td style='white-space:nowrap;'>$arrow \n";
    echo "<a href='index.php?page=planningHebdo/modif.php&amp;id={$elem['id']}&amp;retour=index.php'/>";
    echo "<span class='pl-icon pl-icon-edit' title='Voir'></span></a>";
    
    // Si le champ "key" est renseigné : importation automatique, donc on n'affiche pas les icônes copie et suppression
    if(!$elem['key']){
      echo "<a href='index.php?page=planningHebdo/modif.php&amp;copy={$elem['id']}&amp;retour=index.php'/>";
      echo "<span class='pl-icon pl-icon-copy' title='Copier'></span></a>";
      echo "<a href='javascript:plHebdoSupprime({$elem['id']});' style='margin-left:6px;'/>";
      echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a></td>";
    }
    
  echo "<td>{$elem['nom']}</td>";
  echo "<td>{$elem['service']}</td>";
  echo "<td>".dateFr($elem['debut'])."</td>";
  echo "<td>".dateFr($elem['fin'])."</td>";
  echo "<td>".dateFr($elem['saisie'],true)."</td>";
  echo "<td>$validation</td>";
  echo "<td>$actuel</td>";
  echo "<td>$commentaires</td>";
  echo "</tr>\n";
}
echo "</tbody></table>\n";
?>