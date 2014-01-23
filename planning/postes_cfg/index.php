<?php
/*
Planning Biblio, Version 1.6.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/index.php
Création : mai 2011
Dernière modification : 7 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Page d'index de gestion des tableaux. Affiche la liste des tableaux, des groupes de tableaux et les lignes de séparation.

Page appelée par le fichier index.php, accessible via le menu administration / Les tableaux
*/

require_once "class.tableaux.php";

echo "<h3>Gestion des tableaux</h3>\n";

//	1. 	Tableaux
//	1.1 	Liste des tableaux utilises
$used=array();
$db=new db();
$db->select("pl_poste_tab_affect","tableau",null,"group by tableau");
if($db->result){
  foreach($db->result as $elem){
    $used[]=$elem['tableau'];
  }
}
$db=new db();
$db->select("pl_poste_modeles_tab","tableau",null,"group by tableau");
if($db->result){
  foreach($db->result as $elem){
    $used[]=$elem['tableau'];
  }
}

$db=new db();
$db->select("pl_poste_tab",null,null,"group by nom");


//	Affichage
echo "<table style='width:1200px;' ><tr style='vertical-align:top;'><td style='width:600px;'>\n";
//	1. 	Tableaux
echo "<b>Liste des tableaux</b>\n";

if(!$db->result){
  echo "Aucun tableau";
}
else{
  echo <<<EOD
  <form name='form' method='get' action='index.php'>
  <table cellspacing='0' style='width:90%;'> 
  <tr class='th'>
  <td style='width:90px;'><input type='checkbox' onclick='checkall("form",this);' /></td>
EOD;
  if(in_array(13,$droits))
    echo "<td style='width:25px;'>ID</td>\n";
  echo "<td style='width:250px;'>Nom</td>\n";
  if($config['Multisites-nombre']>1){
    echo "<td >Site</td>\n";
  }
  echo "</tr>\n";

  $class="tr1";
  $i=0;
  foreach($db->result as $elem){
    $class=$class=="tr1"?"tr2":"tr1";
    $site="Multisites-site{$elem['site']}";
    echo "<tr class='$class'><td>\n";
    echo "<input type='checkbox' name='chk$i' value='{$elem['tableau']}'/>\n";
    echo "<a href='index.php?page=planning/postes_cfg/modif.php&amp;numero={$elem['tableau']}'>\n";
    echo "<img src='img/modif.png' alt='Modification' /></a>\n";
    echo "<a href='javascript:popup(\"planning/postes_cfg/copie.php&amp;retour=index.php&amp;numero={$elem['tableau']}\",400,200);'>\n";
    echo "<img src='img/copy.png' alt='Copie'/></a>\n";
    if(!in_array($elem['tableau'],$used)){
      echo "<a href='javascript:popup(\"planning/postes_cfg/suppression.php&amp;numero={$elem['tableau']}\",400,130);'>\n";
      echo "<img src='img/suppr.png' alt='Suppression' /></a>\n";
    }
    echo "</td>\n";
    echo "<td>{$elem['tableau']}</td>\n";
    echo "<td>{$elem['nom']}</td>\n";
    if($config['Multisites-nombre']>1){
      echo "<td>{$config[$site]}</td>\n";
    }
    echo "</tr>\n";
    $i++;
  }
  echo "</table></form>\n";
  $used=join($used,",");
  echo "<br/><input type='button' value='Supprimer la s&eacute;lection' onclick='supprime_select(\"planning/postes_cfg/suppression.php\",\"$used\");'>\n";
}

echo "</td><td>\n";

//		Groupes
$t=new tableau();
$t->fetchAllGroups();
$groupes=$t->elements;

echo <<<EOD
<b>Groupes</b>
<table cellspacing='0' style='width:90%;'>
<tr class='th'><td>&nbsp;</td>
EOD;
if(in_array(13,$droits)){
  echo "<td>ID</td>\n";
}
echo "<td>Nom</td>\n";
if($config['Multisites-nombre']>1){
  echo "<td >Site</td>\n";
}
echo "</tr>\n";

if(is_array($groupes)){
  foreach($groupes as $elem){
    $id=in_array(13,$droits)?"<td>{$elem['id']}</td>":null;
    echo "<tr><td><a href='index.php?page=planning/postes_cfg/groupes.php&amp;id={$elem['id']}'>\n";
    echo "<img src='img/modif.png' border='0' alt='modif' /></a>\n";
    echo "<a href='javascript:supprime_groupe(\"{$elem['id']}\",\"".addslashes(html_entity_decode($elem['nom'],ENT_QUOTES|ENT_IGNORE,"UTF-8"))."\");'><img src='img/drop.gif' border='0' alt='Suppression' /></a>\n";
    echo "</td>\n";
    echo "$id<td>{$elem['nom']}</td>\n";
    if($config['Multisites-nombre']>1){
      echo "<td>".$config["Multisites-site{$elem['site']}"]."</td>\n";
    }
    echo "</tr>\n";
  }
}
echo "</table>\n";

echo <<<EOD
<br/><input type='button' value='Nouveau groupe' onclick='location.href="index.php?page=planning/postes_cfg/groupes.php";' />

</td></tr>
<tr><td style='padding-top:80px;'>
EOD;

//	2.	Lignes de separation

$db=new db();
$db->select("lignes",null,null,"order by nom");

echo "<b>Lignes de s&eacute;paration</b>\n";
echo "<table cellspacing='0' style='width:90%;'>\n";
echo "<tr class='th'><td>&nbsp;</td>\n";
if(in_array(13,$droits)){
  echo "<td>ID</td>\n";
}
echo "<td>Nom</td></tr>\n";
$class="tr2";
foreach($db->result as $elem){
  $db2=new db();
  $db2->select("pl_poste_lignes","*","poste='{$elem['id']}' AND type='ligne'");
  $delete=$db2->result?"style='display:none;'":null;

  $class=$class=="tr2"?"tr1":"tr2";
  echo "<tr class='$class'>\n";
  echo "<td><a href='index.php?page=planning/postes_cfg/lignes_sep.php&amp;action=modif&amp;id={$elem['id']}'><img src='img/modif.png' border='0' alt='modif' /></a>\n";
  echo "<a $delete href='javascript:supprime_ligne(\"{$elem['id']}\",\"".addslashes(html_entity_decode($elem['nom'],ENT_QUOTES|ENT_IGNORE,"UTF-8"))."\");'><img src='img/drop.gif' border='0' alt='Suppression' /></a>\n";
  echo "</td>\n";
  if(in_array(13,$droits)){
    echo "<td>{$elem['id']}</td>\n";
  }
  echo "<td>{$elem['nom']}</td></tr>\n";
}

echo <<<EOD
</table>
<form method='get' action='index.php'>
<input type='hidden' name='page' value='planning/postes_cfg/lignes_sep.php' />
<input type='hidden' name='action' value='ajout' />
<input type='hidden' name='cfg-type' value='lignes_sep' />
<br/><input type='submit' value='Nouvelle ligne' />
</form>
</td></tr>

</td></tr></table>
EOD;
?>