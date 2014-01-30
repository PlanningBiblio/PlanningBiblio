<?php
/*
Planning Biblio, Version 1.6.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/lignes.php
Création : mai 2011
Dernière modification : 30 janvier 2014
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
      $tab=explode("_",$key);  //1: tableau ; 2 lignes
      if(substr($tab[1],-5)=="Titre"){
	$type="titre";
	$tab[1]=substr($tab[1],0,-5);
      }
      elseif(substr($_POST[$key],-5)=="Ligne"){
	$type="ligne";
	$_POST[$key]=substr($_POST[$key],0,-5);
      }
      else{
	$type="poste";
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

// Liste des postes
$reqSite=null;
if($config['Multisites-nombre']>1){
  $reqSite="`site`='$site'";
}

$db=new db();
$db->select("postes",null,$reqSite,"ORDER BY nom");
$postes=$db->result;

// Liste des lignes de séparation
$db=new db();
$db->select("lignes",null,null,"ORDER BY nom");
$lignes_sep=$db->result;


// Le tableau (contenant les sous-tableaux)
$t=new tableau();
$t->id=$tableauNumero;
$t->get();
$tabs=$t->elements;

// affichage du tableau :
// affichage de la lignes des horaires
echo "<form name='form4' action='index.php' method='post' >\n";
echo "<input type='hidden' name='page' value='planning/postes_cfg/modif.php' />\n";
echo "<input type='hidden' name='cfg-type' value='lignes' />\n";
echo "<input type='hidden' name='numero' value='$tableauNumero' />\n";
echo "<table><tr><td style='width:600px;'>";
echo "<h3>Configuration des lignes</h3>\n";
echo "</td><td style='text-align:right;'>\n";
echo "<input type='button' value='Retour' class='ui-button retour'/>\n";
echo "<input type='submit' name='valid' value='Valider' class='ui-button'/>\n";
echo "</td></tr></table>\n";

if($tableauNumero){
  echo "<table style='width:1250px;' cellspacing='0' cellpadding='0' border='1' >\n";
  foreach($tabs as $tab){
    // Lignes Titre et Horaires
    echo "<tr class='tr_horaires' style='text-align:center;'>\n";
    echo "<td style='width:260px;white-space:nowrap;'>\n";
    echo "<input type='text' name='select_{$tab['nom']}Titre_0' class='tr_horaires' style='text-align:center;width:220px;' value='{$tab['titre']}'/>\n";
    echo "<img src='img/add.gif' border='0' alt='Ajouter' style='cursor:pointer;' onclick='ajout(\"select_{$tab["nom"]}_\",-1);'/></td>\n";
    $colspan=0;
    foreach($tab['horaires'] as $horaire){
      echo "<td colspan='".nb30($horaire['debut'],$horaire['fin'])."'>".heure3($horaire['debut'])."-".heure3($horaire['fin'])."</td>";
      $colspan+=nb30($horaire['debut'],$horaire['fin']);
    }
    echo "</tr>\n";
    
    // Lignes Postes et Lignes de séparation
    for($i=0;$i<100;$i++){
      $display=array_key_exists($i,$tab['lignes'])?null:"style='display:none;'";
      echo "<tr id='tr_select_{$tab['nom']}_$i' $display>\n";
      // Première colonne
      echo "<td id='td_select_{$tab['nom']}_{$i}_0' >\n";
      // Sélection des postes et des lignes de séparation
      echo "<select name='select_{$tab['nom']}_$i' style='width:200px;color:black;font-weight:normal;' class='tab_select'>\n";
      echo "<option value=''>&nbsp;</option>\n";
      // Les postes
      if(is_array($postes)){
	foreach($postes as $poste){
	  $class=$poste['obligatoire']=="Obligatoire"?"td_obligatoire":"td_renfort";
	  $selected=($tab['lignes'][$i] and $tab['lignes'][$i]['type']=="poste" and $poste['id']==$tab['lignes'][$i]['poste'])?"selected='selected'":null;
	  echo "<option value='{$poste['id']}' $selected class='$class'>{$poste['nom']} ({$poste['etage']})</option>\n";
	}
      }
      // Les lignes de séparation
      foreach($lignes_sep as $ligne_sep){
	$selected=($tab['lignes'][$i]['type']=="ligne" and $ligne_sep['id']==$tab['lignes'][$i]['poste'])?"selected='selected'":null;
	echo "<option value='{$ligne_sep['id']}Ligne' class='tr_horaires' $selected style='font-weight:normal;'>{$ligne_sep['nom']}</option>\n";
      }
      echo "</select>&nbsp;&nbsp;\n";
      // Boutons ajout et suppression
      echo "<img src='img/add.gif' border='0' alt='Ajouter' style='cursor:pointer;' onclick='ajout(\"select_{$tab["nom"]}_\",$i);'/>\n";
      echo "<img src='img/drop.gif' border='0' alt='Supprimer' style='cursor:pointer;' onclick='supprime_tab(\"{$tab["nom"]}_\",$i);' />\n";
      echo "</td>\n";

      // Cellules (grises ou non)
      $j=1;
      foreach($tab['horaires'] as $horaire){
	$class=null;
	$checked=null;
	if(in_array("{$i}_{$j}",$tab['cellules_grises'])){
	  $class="class='cellule_grise'";
	  $checked="checked='checked'";
	}
	echo "<td id='td_select_{$tab['nom']}_{$i}_$j' $class colspan='".nb30($horaire['debut'],$horaire['fin'])."' style='text-align:center;'>\n";
	echo "<input type='checkbox' name='checkbox_{$tab['nom']}_{$i}_$j' $checked onclick='couleur2(this,\"td_select_{$tab['nom']}_{$i}_$j\");'/> G\n";
	echo "</td>\n";
	$j++;
      }
      echo "<td id='td_select_{$tab['nom']}_$i' colspan='$colspan' style='display:none;'>\n";
      echo "</tr>\n"; 
    }
  }
  echo "</table>\n";
}
echo "</form>\n";
?>

<script type='text/JavaScript'>
$("document").ready(function(){
  // Applique la même class que l'option selectionnée au select et au td pour chaque select poste lors du chargement
  $(".tab_select").each(function(){
    var myClass=$(this).find(":selected").attr("class");
    $(this).removeClass();
    $(this).addClass(myClass);
    $(this).closest("td").removeClass();
    $(this).closest("td").addClass(myClass);
  });
});

// Change la class du select et du td lorsque l'on change d'option dans les listes des postes
$(".tab_select").change(function(){
  var myClass=$(this).find(":selected").attr("class");
  $(this).removeClass();
  $(this).addClass(myClass);
  $(this).closest("td").removeClass();
  $(this).closest("td").addClass(myClass);
});
</script>