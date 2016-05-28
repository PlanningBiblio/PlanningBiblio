<?php
/**
Planning Biblio, Version 2.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : planning/postes_cfg/index.php
Création : mai 2011
Dernière modification : 18 mars 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Page d'index de gestion des tableaux. Affiche la liste des tableaux, des groupes de tableaux et les lignes de séparation.

Page appelée par le fichier index.php, accessible via le menu administration / Les tableaux
*/

require_once "class.tableaux.php";

echo "<h2>Gestion des tableaux</h2>\n";

// Tableaux
$t=new tableau();
$t->fetchAll();
$tableaux=$t->elements;

// Tableaux supprimés
$t=new tableau();
$t->supprime=true;
$t->fetchAll();
$tableauxSupprimes=$t->elements;

// Dernières utilisations des tableaux
$tabAffect=array();
$db=new db();
$db->select2("pl_poste_tab_affect",null,null,"order by `date` asc");
if($db->result){
  foreach($db->result as $elem){
    $tabAffect[$elem['tableau']]=$elem['date'];
  }
}


//	Affichage

//	1. 	Tableaux
echo "<div id='tableaux-listes' class='tableaux-cfg'>\n";
echo "<h3>Liste des tableaux</h3>\n";
echo "<p><a href='index.php?page=planning/postes_cfg/modif.php' class='ui-button'>Nouveau tableau</a></p>\n";

echo <<<EOD
<form name='form' method='get' action='index.php'>
<table class='CJDataTable' id='table-list' data-noExport='1' data-sort='[[1,"asc"]]'>
<thead>
<tr>
<th class='dataTableNoSort'><input type='checkbox' class='CJCheckAll' /></th>
EOD;
if(in_array(13,$droits)){
  echo "<th>ID</th>\n";
}
echo "<th>Nom</th>\n";
if($config['Multisites-nombre']>1){
  echo "<th>Site</th>\n";
}
echo "<th class='dataTableDateFR'>Derni&egrave;re utilisation</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

$i=0;
foreach($tableaux as $elem){
  $site="Multisites-site{$elem['site']}";
  
  if(array_key_exists($elem['tableau'],$tabAffect)){
    $utilisation=dateFr($tabAffect[$elem['tableau']]);
  }else{
    $utilisation="Jamais";
  }
  
  echo "<tr id='tr-tableau-{$elem['tableau']}' ><td style='white-space:nowrap;'>\n";
  echo "<input type='checkbox' name='chk$i' value='{$elem['tableau']}' class='chk1'/>\n";
  echo "<a href='index.php?page=planning/postes_cfg/modif.php&amp;numero={$elem['tableau']}'>\n";
  echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
  echo "<a href='javascript:popup(\"planning/postes_cfg/copie.php&amp;retour=index.php&amp;numero={$elem['tableau']}\",400,200);'>\n";
  echo "<span class='pl-icon pl-icon-copy' title='Copier'></span></a>\n";
  echo "<a href='javascript:supprimeTableau({$elem['tableau']});'>\n";
  echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>\n";
  echo "</td>\n";
  if(in_array(13,$droits)){
    echo "<td>{$elem['tableau']}</td>\n";
  }
  echo "<td id='td-tableau-{$elem['tableau']}-nom'>{$elem['nom']}</td>\n";
  if($config['Multisites-nombre']>1){
    echo "<td>{$config[$site]}</td>\n";
  }
  echo "<td>$utilisation</td>\n";
  echo "</tr>\n";
  $i++;
}
echo "</tbody>\n";
echo "</table></form>\n";
echo "<p><input type='button' value='Supprimer la s&eacute;lection' class='ui-button' onclick='supprime_select(\"chk1\",\"planning/postes_cfg/ajax.suppression.php\");'></p>\n";

// Récupération de tableaux supprimés dans l'année
if(!empty($tableauxSupprimes)){
  echo "<p style='margin-top:30px;'>\n";
  echo "R&eacute;cup&eacute;ration d&apos;un tableau supprim&eacute;&nbsp;\n";
  echo "<select id='tableauxSupprimes'>\n";
  echo "<option value=''>&nbsp;</option>\n";
  foreach($tableauxSupprimes as $elem){
    if(array_key_exists($elem['tableau'],$tabAffect)){
      $utilisation=dateFr($tabAffect[$elem['tableau']]);
    }else{
      $utilisation="Jamais";
    }

    echo "<option value='{$elem['tableau']}'>{$elem['nom']}&nbsp;(utilisation : $utilisation)</option>\n";
  }
  echo "</select>\n";
  echo "</p>\n";
}

echo "</div> <!-- tableaux-liste -->\n";

echo "<div id='tableaux-groupes' class='tableaux-cfg' >\n";
//		Groupes
$t=new tableau();
$t->fetchAllGroups();
$groupes=$t->elements;

echo <<<EOD
<h3>Groupes</h3>

<p><input type='button' value='Nouveau groupe' class='ui-button' onclick='location.href="index.php?page=planning/postes_cfg/groupes.php";' /></p>

<table class='CJDataTable' id='table-groups' data-noExport='1'  data-sort='[[1,"asc"]]'>
<thead>
<tr><th class='dataTableNoSort'>&nbsp;</th>
EOD;
if(in_array(13,$droits)){
  echo "<th>ID</th>\n";
}
echo "<th>Nom</th>\n";
if($config['Multisites-nombre']>1){
  echo "<th>Site</th>\n";
}
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

if(is_array($groupes)){
  foreach($groupes as $elem){
    $id=in_array(13,$droits)?"<td>{$elem['id']}</td>":null;
    echo "<tr id='tr-groupe-{$elem['id']}'><td><a href='index.php?page=planning/postes_cfg/groupes.php&amp;id={$elem['id']}'>\n";
    echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
    echo "<a href='javascript:supprimeGroupe({$elem['id']});'>\n";
    echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>\n";
    echo "</td>\n";
    echo "$id<td id='td-groupe-{$elem['id']}-nom'>{$elem['nom']}</td>\n";
    if($config['Multisites-nombre']>1){
      echo "<td>".$config["Multisites-site{$elem['site']}"]."</td>\n";
    }
    echo "</tr>\n";
  }
}
echo "</tbody>\n";
echo "</table>\n";

echo <<<EOD
</div> <!-- tableaux-groupes -->

<div id='tableaux-separations' class='tableaux-cfg'>
EOD;

//	2.	Lignes de separation

$db=new db();
$db->select("lignes",null,null,"order by nom");

echo "<h3>Lignes de s&eacute;paration</h3>\n";

echo "<p><input type='submit' value='Nouvelle ligne' class='ui-button'/></p>\n";

echo "<table class='CJDataTable' id='table-separations' data-noExport='1'  data-sort='[[1,\"asc\"]]'>\n";
echo "<thead>\n";
echo "<tr><th class='dataTableNoSort'>&nbsp;</th>\n";
if(in_array(13,$droits)){
  echo "<th>ID</th>\n";
}
echo "<th>Nom</th></tr>\n";
echo "</thead>\n";

echo "<tbody>\n";
foreach($db->result as $elem){
  $db2=new db();
  $db2->select("pl_poste_lignes","*","poste='{$elem['id']}' AND type='ligne'");
  $delete=$db2->result?false:true;

  echo "<tr id='tr-ligne-{$elem['id']}' >\n";
  echo "<td><a href='index.php?page=planning/postes_cfg/lignes_sep.php&amp;action=modif&amp;id={$elem['id']}'>\n";
  echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
  if($delete){
    echo "<a href='javascript:supprimeLigne({$elem['id']});'>\n";
    echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>\n";
  }
  echo "</td>\n";
  if(in_array(13,$droits)){
    echo "<td>{$elem['id']}</td>\n";
  }
  echo "<td id='td-ligne-{$elem['id']}-nom' >{$elem['nom']}</td></tr>\n";
}

echo <<<EOD
</tbody>
</table>

<form method='get' action='index.php'>
<input type='hidden' name='page' value='planning/postes_cfg/lignes_sep.php' />
<input type='hidden' name='action' value='ajout' />
<input type='hidden' name='cfg-type' value='lignes_sep' />
</form>
</div> <!-- tableaux-separations -->

EOD;
?>