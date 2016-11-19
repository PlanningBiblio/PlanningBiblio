<?php
/**
Planning Biblio, Version 2.5.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2016 Jérôme Combes

Fichier : activites/index.php
Création : mai 2011
Dernière modification : 19 novembre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche la liste des activités, avec filtre sur le nom de l'activité

Page appelée par la page index.php
*/

require_once "class.activites.php";

//		Recherche des activités
$a=new activites();
$a->fetch();
$activites=$a->elements;

// 		Contrôle si l'activité est attribuée à un poste pour en interdire la suppression
$activites_utilisees=array();
$tab=array();
$db=new db();
$db->select2("postes","activites",array("supprime"=>null),"GROUP BY `activites`");
if($db->result){
  foreach($db->result as $elem){
    $tab[]=json_decode(html_entity_decode($elem['activites'],ENT_QUOTES|ENT_IGNORE,'UTF-8'));
  }
}

// 		Contrôle si l'activité est attribuée à un agent pour en interdire la suppression
$db=new db();
$db->select2("personnel","postes",array("supprime"=>"<>2"),"GROUP BY `postes`");
if($db->result){
  foreach($db->result as $elem){
    $tab[]=json_decode(html_entity_decode($elem['postes'],ENT_QUOTES|ENT_IGNORE,'UTF-8'));
  }
}
if(!empty($tab)){
  foreach($tab as $elem){
    if(is_array($elem)){
      foreach($elem as $act){
	if(!in_array($act,$activites_utilisees)){
	  $activites_utilisees[]=$act;
	}
      }
    }
  }
}

?>
<br/>

<form name="form" action="index.php">
<input type='hidden' name='page' value='activites/index.php' />
<table><tr valign='top'>
<td style='width:270px'><h3 style='margin-top:0px;'>Liste des activités</h3></td>
<td><input type="button" value="Ajouter" onclick='location.href="index.php?page=activites/modif.php"' class='ui-button'/>
</td></tr></table>
</form>


<?php
// Tri par défaut du tableau
$sort=in_array(13,$droits)?'[[2,"asc"]]':'[[1,"asc"]]';

echo "<table id='tableActivites' class='CJDataTable' data-sort='$sort'>\n";
echo "<thead><tr>\n";
echo "<th class='dataTableNoSort'>&nbsp;</th>\n";
if(in_array(13,$droits)){
  echo "<th>ID</th>\n";
}
echo "<th>Nom de l'activité</th>\n";
echo "</tr></thead>\n";

echo "<tbody>\n";
foreach($activites as $elem){
  echo "<tr><td>\n";
  echo "<a href='index.php?page=activites/modif.php&amp;id={$elem['id']}'>\n";
  echo "<span class='pl-icon pl-icon-edit' title='Modifier'></span></a>\n";
  if(!in_array($elem['id'],$activites_utilisees)){
    echo "&nbsp;&nbsp;";
    echo "<a href='javascript:supprime(\"activites\",{$elem['id']});'>";
    echo "<span class='pl-icon pl-icon-drop' title='Supprimer'></span></a>\n";
  }
  echo "</td>\n";
  if(in_array(13,$droits))
    echo "<td>{$elem['id']}</td>\n";
  echo "<td>{$elem['nom']}</td>\n";
  echo "</tr>\n";
}
echo "</tbody></table>\n";
?>