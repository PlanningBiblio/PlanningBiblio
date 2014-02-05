<?php
/*
Planning Biblio, Version 1.7.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/horaires.php
Création : mai 2011
Dernière modification : 3 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de modifier les horaires d'un tableau. Affichage d'un formulaire avec des menus déroulant pour le choix des plages
horaires. Validation de ce formulaire.

Page incluse dans le fichier "planning/postes_cfg/modif.php"
*/

require_once "class.tableaux.php";

$horaires=Array();
$tableau=null;
$disabled=null;

//	Mise à jour du tableau (après validation)
if(isset($_POST['action'])){
  $db=new db();
  $db->query("DELETE FROM `{$dbprefix}pl_poste_horaires` WHERE `numero`='$tableauNumero';");

  $keys=array_keys($_POST);

  foreach($keys as $key){
    if($key!="page" and $key!="action" and $key!="numero"){
      $tmp=explode("_",$key);				// debut_general_22
      if(empty($tab[$tmp[1]."_".$tmp[2]]))
	  $tab[$tmp[1]."_".$tmp[2]]=array($tmp[1]);	// tab[0]=tableau
      if($tmp[0]=="debut")				// tab[1]=debut
	  $tab[$tmp[1]."_".$tmp[2]][1]=$_POST[$key];
      if($tmp[0]=="fin")				// tab[2]=fin
	  $tab[$tmp[1]."_".$tmp[2]][2]=$_POST[$key];
    }
  }
  $values=array();
  foreach($tab as $elem){
    if($elem[1]){
      $values[]="('{$elem[1]}','{$elem[2]}','{$elem[0]}','$tableauNumero')";
    }
  }
  $req="INSERT INTO `{$dbprefix}pl_poste_horaires` (`debut`,`fin`,`tableau`,`numero`) VALUES ".join($values,",").";";
  $db->query($req);
}

//	Liste des horaires
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}pl_poste_horaires` WHERE `numero` ='$tableauNumero' ORDER BY `tableau`,`debut`,`fin`;");
$horaires=$db->result;

//	Liste des tableaux utilisés
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


//	Affichage des horaires
$quart=$config['heuresPrecision']=="quart d&apos;heure"?true:false;

echo "<form name='form2' action='index.php' method='post'>\n";
echo "<input type='hidden' name='page' value='planning/postes_cfg/modif.php' />\n";
echo "<input type='hidden' name='cfg-type' value='horaires' />\n";
echo "<input type='hidden' name='numero' value='$tableauNumero' />\n";
echo "<input type='hidden' name='action' value='modif' />\n";
echo "<table><tr><td style='width:600px;'>";
echo "<h3>Configuration des horaires</h3>\n";
echo "</td><td style='text-align:right;'>\n";
echo "<input type='button' value='Retour' class='ui-button retour'/>\n";
echo "<input type='submit' value='Valider' class='ui-button'/>\n";
echo "</td></tr></table>\n";

if($horaires[0]){
  echo "<table><tr style='vertical-align:top;'>";

  $numero=1;
  foreach($horaires as $elem){
    if($tableau and $elem['tableau']!=$tableau){	// Affichage de la fin des tableaux (sauf dernier tableau)
      for($j=0;$j<25;$j++){				// Affichage des select cachés pour les ajouts (sauf dernier tableau)
	echo "<tr id='tr_{$tableau}_$j' style='display:none;'><td>\n";
	echo "<select name='debut_{$tableau}_new$j' style='width:75px;'>\n";
	selectHeure(6,23,true,$quart);
	echo "</select>\n";
	echo "</td><td>\n";
	echo "<select name='fin_{$tableau}_new$j' style='width:75px;'>\n";
	selectHeure(6,23,true,$quart);
	echo "</select>\n";
	echo "<img src='img/drop.gif' alt='supprimer' style='cursor:pointer;' onclick='document.form2.debut_{$elem['tableau']}_new$j.value=\"\";document.form2.fin_{$elem['tableau']}_new$j.value=\"\";'/>\n";
	echo "</td>\n";
	echo "</tr>\n";
      }
							// Affichage des boutons ajouter
      echo "<tr><td><img src='img/add.gif' alt='ajouter' style='cursor:pointer' onclick='add_horaires(\"{$tableau}\");'/></td></tr>\n";
      echo "</table></td>";

      // Affichage des tableaux sur plusieurs lignes de 4
      if(!(($numero-1)%4)){
	echo "</tr><tr style='vertical-align:top;'>";
      }
    }

    if($elem['tableau']!=$tableau){			// Affichage du début des tableaux
      $padding=$numero>4?"style='padding-top:40px;'":null;
      echo "<td style='width:200px;'><table><tr><td colspan='2' $padding ><b>Tableau $numero</b></td></tr>\n";
      $numero++;
    }

    $tableau=$elem['tableau'];
    echo "<tr><td>\n";
    echo "<select name='debut_{$elem['tableau']}_{$elem['id']}' style='width:75px;' $disabled>\n";
    selectHeure(6,23,true,$quart);
    echo "</select>\n";
    echo "<script type='text/JavaScript'>document.form2.debut_{$elem['tableau']}_{$elem['id']}.value='{$elem['debut']}';</script>\n";	
    echo "</td><td>\n";
    echo "<select name='fin_{$elem['tableau']}_{$elem['id']}' style='width:75px;' onchange='change_horaires(this);'>\n";
    selectHeure(6,23,true,$quart);
    echo "</select>\n";
    echo "<img src='img/drop.gif' alt='supprimer' style='cursor:pointer;' onclick='document.form2.debut_{$elem['tableau']}_{$elem['id']}.value=\"\";document.form2.fin_{$elem['tableau']}_{$elem['id']}.value=\"\";'/>\n";
    echo "<script type='text/JavaScript'>document.form2.fin_{$elem['tableau']}_{$elem['id']}.value='{$elem['fin']}';</script>\n";	
    echo "</td>\n";
    echo "</tr>\n";
  }
							// Affichage de la fin du dernier tableau
  for($j=0;$j<25;$j++){					// Affichage des select cachés pour les ajouts du dernier tableau
    echo "<tr id='tr_{$tableau}_$j' style='display:none;'><td>\n";
    echo "<select name='debut_{$tableau}_new$j' style='width:75px;'>\n";
    selectHeure(6,23,true,$quart);
    echo "</select>\n";
    echo "</td><td>\n";
    echo "<select name='fin_{$tableau}_new$j' style='width:75px;'>\n";
    selectHeure(6,23,true,$quart);
    echo "</select>\n";
    echo "<img src='img/drop.gif' alt='supprimer' style='cursor:pointer;' onclick='document.form2.debut_{$elem['tableau']}_new$j.value=\"\";document.form2.fin_{$elem['tableau']}_new$j.value=\"\";'/>\n";
    echo "</td>\n";
    echo "</tr>\n";
  }
							// Affichage du bouton ajouter du dernier tableau
  echo "<tr><td><img src='img/add.gif' alt='ajouter' style='cursor:pointer' onclick='add_horaires(\"{$elem['tableau']}\");'/></td></tr>\n";
  echo "</table></td>\n";
  echo "</tr></table>\n";
}
echo "</form>\n";
?>