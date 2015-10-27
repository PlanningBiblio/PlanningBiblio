<?php
/*
Planning Biblio, Version 1.9.6
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : planning/postes_cfg/horaires.php
Création : mai 2011
Dernière modification : 24 avril 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Permet de modifier les horaires d'un tableau. Affichage d'un formulaire avec des menus déroulant pour le choix des plages
horaires. Validation de ce formulaire.

Page incluse dans le fichier "planning/postes_cfg/modif.php"
*/

require_once "class.tableaux.php";

//	Mise à jour du tableau (après validation)
if(isset($_POST['action'])){
  $db=new db();
  $db->delete2("pl_poste_horaires",array("numero"=>$tableauNumero));

  $keys=array_keys($_POST);

  foreach($keys as $key){
    if($key!="page" and $key!="action" and $key!="numero"){
      $tmp=explode("_",$key);				// debut_1_22
      if(array_key_exists(1,$tmp) and array_key_exists(2,$tmp)){
	if(empty($tab[$tmp[1]."_".$tmp[2]]))
	    $tab[$tmp[1]."_".$tmp[2]]=array($tmp[1]);	// tab[0]=tableau
	if($tmp[0]=="debut")				// tab[1]=debut
	    $tab[$tmp[1]."_".$tmp[2]][1]=$_POST[$key];
	if($tmp[0]=="fin")				// tab[2]=fin
	    $tab[$tmp[1]."_".$tmp[2]][2]=$_POST[$key];
      }
    }
  }
  $values=array();
  foreach($tab as $elem){
    if($elem[1] and $elem[2]){
      $values[]=array("debut"=>$elem[1], "fin"=>$elem[2], "tableau"=>$elem[0], "numero"=>$tableauNumero);

    }
  }
  $db=new db();
  $db->insert2("pl_poste_horaires",$values);
  if(!$db->error){
    echo "<script type='text/JavaScript'>CJInfo(\"Les horaires ont été modifiés avec succès\",\"success\");</script>\n";
  }else{
    echo "<script type='text/JavaScript'>CJInfo(\"Une erreur est survenue lors de l'enregistrement des horaires\",\"error\");</script>\n";
  }
}

//	Liste des horaires
$db=new db();
$db->select("pl_poste_horaires","*","`numero` ='$tableauNumero'","ORDER BY `tableau`,`debut`,`fin`");
$horaires=$db->result;

// Liste des tableaux
$tableaux=array();
if($horaires){
  foreach($horaires as $elem){
    if(!array_key_exists($elem['tableau'],$tableaux)){
      $tableaux[$elem['tableau']]=array('tableau'=>$elem['tableau'], 'horaires'=>array());
    }
    $tableaux[$elem['tableau']]['horaires'][]=array("id"=>$elem["id"], "debut"=>$elem["debut"],"fin"=>$elem["fin"]);
  }
}

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
$quart=$config['heuresPrecision']=="quart-heure"?true:false;

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

$numero=0;	// Numéro du tableau (numéro affiché)
if(!empty($tableaux)){
  foreach($tableaux as $t){
    $tableau=$t['tableau'];
    $numero++;

    echo "<div id='div_horaires_{$tableau}' style='display:inline-block; width:200px; vertical-align:top; padding-bottom:30px;'>\n";
    echo "<table id='tab_horaires_{$tableau}'>\n";
    echo "<tr><td colspan='2' ><strong>Tableau $numero</strong></td></tr>\n";

    $i=0;
    foreach($t['horaires'] as $elem){
      // Affichage des horaires existants
      echo "<tr><td>\n";
      echo "<select name='debut_{$tableau}_{$i}' style='width:75px;' >\n";
      selectHeure(6,23,true,$quart,$elem['debut']);
      echo "</select>\n";
      echo "</td><td>\n";
      echo "<select name='fin_{$tableau}_{$i}' style='width:75px;' onchange='change_horaires(this);'>\n";
      selectHeure(6,23,true,$quart,$elem['fin']);
      echo "</select>\n";
      echo "<span class='pl-icon pl-icon-drop' title='Supprimer' style='margin-left:5px;cursor:pointer;' onclick='document.form2.debut_{$tableau}_{$i}.value=\"\";document.form2.fin_{$tableau}_{$i}.value=\"\";'></span>\n";
      echo "</td>\n";
      echo "</tr>\n";
      $i++;
    }

    // Affichage des select cachés pour les ajouts
    for($j=0;$j<25;$j++){				
      echo "<tr id='tr_{$tableau}_$j' style='display:none;'><td>\n";
      echo "<select name='debut_{$tableau}_{$i}' style='width:75px;'>\n";
      selectHeure(6,23,true,$quart);
      echo "</select>\n";
      echo "</td><td>\n";
      echo "<select name='fin_{$tableau}_{$i}' style='width:75px;' onchange='change_horaires(this);'>\n";
      selectHeure(6,23,true,$quart);
      echo "</select>\n";
      echo "<span class='pl-icon pl-icon-drop' title='Supprimer' style='margin-left:5px;cursor:pointer;' onclick='document.form2.debut_{$tableau}_{$i}.value=\"\";document.form2.fin_{$tableau}_{$i}.value=\"\";'></span>\n";
      echo "</td>\n";
      echo "</tr>\n";
      $i++;
    }

    // Affichage des boutons ajouter
    echo "<tr><td><span class='pl-icon pl-icon-add' title='Ajouter' style='cursor:pointer' onclick='add_horaires(\"{$tableau}\");'></span></td></tr>\n";

    // Fin des tableaux
    echo "</table></div> <!-- tab_horaires_{$tableau} &amp; div_horaires_{$tableau} -->\n";
  }
}

echo "</form>\n";
?>