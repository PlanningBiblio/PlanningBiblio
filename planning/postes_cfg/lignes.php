<?php
/*
Planning Biblio, Version 1.6.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/lignes.php
Création : mai 2011
Dernière modification : 22 janvier 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de modifier les lignes d'un tableau. Affichage d'un tableau avec les horaires en colonnes, les postes dans des menus 
déroulant en lignes. Permet également de griser des cellules avec les cases à cocher "G"
Affichage et validation

Page incluse dans le fichier "planning/postes_cfg/modif.php"
*/

require_once "class.tableaux.php";

//	Si validation, enregistrement des infos
if(isset($_POST['valid'])){	
  $keys=array_keys($_POST);
  $values=array();
  $db=new db();			//		Suppression des infos concernant ce tableau dans la table pl_poste_lignes
  $db->query("DELETE FROM `{$dbprefix}pl_poste_lignes` WHERE `numero`='$tableauNumero';");
  foreach($keys as $key){	//		Insertion des données dans la table pl_poste_lignes
    if($_POST[$key] and substr($key,0,6)=="select"){
      $tab=explode("_",$key);  //1: tableau ; 2 lignes	/////////////////////////
      if(substr($tab[1],-5)=="Titre"){
	$type="titre";
	$tab[1]=substr($tab[1],0,-5);
      }
      elseif(substr($_POST[$key],-5)=="Ligne"){
	$type="ligne";
	$_POST[$key]=substr($_POST[$key],0,-5);
      }
      else{
	$type="poste";					////////////////////////*/
      }
      $values[]="('$tableauNumero','{$tab[1]}','{$tab[2]}','{$_POST[$key]}','$type')";
    }
  }
  if($values[0]){
    $sql="INSERT INTO `{$dbprefix}pl_poste_lignes` (`numero`,`tableau`,`ligne`,`poste`,`type`) VALUES ";
    $sql.=join($values,",").";";
    $db=new db();
    $db->query($sql);
  }

  $values=array();
  $db=new db();			//		Suppression des infos concernant ce tableau dans la table pl_poste_cellules
  $db->query("DELETE FROM `{$dbprefix}pl_poste_cellules` WHERE `numero`='$tableauNumero';");
  foreach($keys as $key){	//		Insertion des données dans la table pl_poste_cellules
    if($_POST[$key] and substr($key,0,8)=="checkbox"){
      $tab=explode("_",$key);  //1: tableau ; 2 lignes ; 3 colonnes
      $values[]="('$tableauNumero','{$tab[1]}','{$tab[2]}','{$tab[3]}')";
    }
  }
  if(!empty($values)){
    $sql="INSERT INTO `{$dbprefix}pl_poste_cellules` (`numero`,`tableau`,`ligne`,`colonne`) VALUES ";
    $sql.=join($values,",").";";
    $db=new db();
    $db->query($sql);
  }
}

//	Liste des horaires
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}pl_poste_horaires` WHERE `numero` ='$tableauNumero' ORDER BY `tableau`,`debut`,`fin`;");
$horaires=$db->result;

//	Liste des postes
$reqSite=null;
if($config['Multisites-nombre']>1){
  $reqSite="WHERE `site`='$site'";
}

$db=new db();
$db->query("SELECT * FROM `{$dbprefix}postes` $reqSite ORDER BY `nom`;");
$postes=$db->result;

//	Liste des lignes de séparation
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}lignes` ORDER BY `nom`;");
$lignes_sep=$db->result;

//	Liste des lignes enregistrées
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}pl_poste_lignes` WHERE `numero`='$tableauNumero' ORDER BY `tableau`,`ligne`;");
$lignes=$db->result;

//	Liste des cellules grises
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}pl_poste_cellules` WHERE `numero`='$tableauNumero' ORDER BY `tableau`,`ligne`,`colonne`;");
$cellules_grises=array();
if($db->result){
  foreach($db->result as $elem){
    $cellules_grises[]="{$elem['tableau']}_{$elem['ligne']}_{$elem['colonne']}";
  }
}

//		Tableau $tab [nom,horaire1[debut,fin],horaire2[debut,fin],horaire3[debut,fin] ... ]
//		Tri des horaires
$tabs=array();
if(is_array($horaires)){
  foreach($horaires as $elem){
    if(!in_array(array($elem['tableau']),$tabs)){
      $tabs[]=array($elem['tableau']);
    }
  }
}

if(is_array($horaires)){
  foreach($horaires as $elem){
    $tabs[$elem['tableau']-1][]=$elem;
  }
}

// affichage du tableau :
// affichage de la lignes des horaires
echo "<form name='form4' action='index.php' method='post' >\n";
echo "<input type='hidden' name='page' value='planning/postes_cfg/modif.php' />\n";
echo "<input type='hidden' name='cfg-type' value='lignes' />\n";
echo "<input type='hidden' name='numero' value='$tableauNumero' />\n";
echo "<table style='width:100%;'><tr><td>";
echo "<h3>Configuration des lignes</h3>\n";
echo "</td><td style='text-align:right;'>\n";
echo "<input type='button' value='Retour' class='ui-button retour'/>\n";
echo "<input type='submit' name='valid' value='Valider' class='ui-button'/>\n";
echo "</td></tr></table>\n";

if($tableauNumero){
  echo "<table style='width:1250px;' cellspacing='0' cellpadding='0' border='1' >\n";

  foreach($tabs as $tab){
    //		Ligne horaires
    echo "<tr class='tr_horaires' style='text-align:center;'>\n";
    echo "<td style='width:260px'><input type='text' name='select_{$tab[0]}Titre_0' class='tr_horaires' style='text-align:center;width:100%;'/></td>\n";
    $colspan=0;
    for($i=1;$i<count($tab);$i++){
      echo "<td colspan='".nb30($tab[$i]['debut'],$tab[$i]['fin'])."'>".heure3($tab[$i]['debut'])."-".heure3($tab[$i]['fin'])."</td>";
      $colspan+=nb30($tab[$i]['debut'],$tab[$i]['fin']);
    }
    echo "</tr>\n";

    //	Lignes <select>
    for($i=0;$i<100;$i++){
      echo "<tr id='tr_select_{$tab[0]}_$i' style='display:none;'>\n";
      echo "<td id='td_select_{$tab[0]}_{$i}_0' >\n";
      echo "<select name='select_{$tab[0]}_$i' style='width:200px;' onchange='couleur(\"select_{$tab[0]}_\",$i);'>\n";
      echo "<option value=''>&nbsp;</option>\n";
      if(is_array($postes))
      foreach($postes as $poste){
	$background=$poste['obligatoire']=="Obligatoire"?"#00FA92":"#FFFFFF";
	echo "<option value='{$poste['id']}' style='background:$background;color:#7D3C25;'>{$poste['nom']} ({$poste['etage']})</option>\n";
      }
      foreach($lignes_sep as $ligne_sep){
	echo "<option value='{$ligne_sep['id']}Ligne' style='background:#7D3C25;color:#FFFFFF;'>{$ligne_sep['nom']}</option>\n";
      }
      echo "</select>&nbsp;&nbsp;<a href='javascript:ajout(\"select_{$tab[0]}_\",$i);' id='ajout_select_${tab[0]}_$i' >\n";
      echo "<img src='img/add.gif' border='0' alt='Ajouter' /></a>\n";
      echo "<a href='javascript:supprime_tab(\"{$tab[0]}_\",$i);' id='supprime_select_${tab[0]}_$i'>\n";
      echo "<img src='img/drop.gif' border='0' alt='Supprimer' /></a>\n";
      echo "</td>\n";
      for($j=1;$j<count($tab);$j++){
	$class=null;
	$checked=null;
	if(in_array("{$tab[0]}_{$i}_{$j}",$cellules_grises)){
	  $class="class='cellule_grise'";
	  $checked="checked='checked'";
	}
	echo "<td id='td_select_{$tab[0]}_{$i}_$j' $class colspan='".nb30($tab[$j]['debut'],$tab[$j]['fin'])."' style='text-align:center;'>\n";
	echo "<input type='checkbox' name='checkbox_{$tab[0]}_{$i}_$j' $checked onclick='couleur2(this,\"td_select_{$tab[0]}_{$i}_$j\");'/> G\n";
	echo "</td>\n";
      }
      echo "<td id='td_select_{$tab[0]}_$i' colspan='$colspan' style='display:none;'>\n";
	      
      echo "</tr>\n"; 
      }
  }
  echo "</table>\n";
}
echo "</form>\n";

//	Pour contrôler ensuite si les tableaux existent
$exist=array(1,2,3);

echo "<script type='text/JavaScript'>\n";
//	Affichage en JavaScript des lignes enregistrées
if(is_array($lignes)){
  for($i=0;$i<count($lignes);$i++){
    if($lignes[$i]['type']=="titre")
      $lignes[$i]['tableau'].="Titre";
    if($lignes[$i]['type']=="ligne")
      $lignes[$i]['poste'].="Ligne";
    echo "document.form4.select_{$lignes[$i]['tableau']}_{$lignes[$i]['ligne']}.value='".html_entity_decode($lignes[$i]['poste'],ENT_QUOTES|ENT_IGNORE,"UTF-8")."';\n";
    if($lignes[$i]['type']!="titre"){
      echo "document.getElementById('tr_select_{$lignes[$i]['tableau']}_{$lignes[$i]['ligne']}').style.display='';\n";
      echo "couleur('select_{$lignes[$i]['tableau']}_',{$lignes[$i]['ligne']});\n";
      if(array_key_exists($i+1,$lignes) and $lignes[$i+1]['tableau']!=$lignes[$i]['tableau'])
	echo "document.getElementById('supprime_select_{$lignes[$i]['tableau']}_{$lignes[$i]['ligne']}').style.display='none';\n";
    }
    //	Pour contrôler ensuite si les tableaux existent
    if($lignes[$i]['tableau']==1)
      $exist[0]=null;
    if($lignes[$i]['tableau']==2)
      $exist[1]=null;
    if($lignes[$i]['tableau']==3)
      $exist[2]=null;
  }
}
//	Affichage en JavaScript des premières lignes (si rien n'est enregistré)
foreach($exist as $elem){
  if($elem){
    echo "document.getElementById('tr_select_{$elem}_0').style.display='';\n";
    echo "document.getElementById('supprime_select_{$elem}_0').style.display='none';\n";
  }
}
echo "</script>\n";
?>