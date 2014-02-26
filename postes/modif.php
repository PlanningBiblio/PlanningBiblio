<?php
/*
Planning Biblio, Version 1.7.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : postes/modif.php
Création : mai 2011
Dernière modification : 26 février 2014
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche le formulaire permettant d'ajouter ou de modifier un poste.

Page appelée par le fichier index.php. Accessible à partir des icônes de modification et du bouton "Ajouter" de la page 
postes/index.php
Soumission des formulaires à la page postes/valid.php
*/

require_once "class.postes.php";

$actList=new db();
$actList->query("SELECT * FROM `{$dbprefix}activites` ORDER BY `nom`;");

//	Modification d'un poste
if(isset($_GET['id'])){
  $id=$_GET['id'];
  echo "<h3>Modification du poste</h3>\n";
  $db=new db();
  $db->select("postes",null,"id='$id'");
  $nom=$db->result[0]['nom'];
  $etage=$db->result[0]['etage'];
  $categories=is_serialized($db->result[0]['categories'])?unserialize($db->result[0]['categories']):array();
  $site=$db->result[0]['site'];
  $activites=unserialize($db->result[0]['activites']);
  $obligatoire=$db->result[0]['obligatoire']=="Obligatoire"?"checked='checked'":"";
  $renfort=$db->result[0]['obligatoire']=="Renfort"?"checked='checked'":"";
  $stat1=$db->result[0]['statistiques']?"checked='checked'":"";
  $stat2=!$db->result[0]['statistiques']?"checked='checked'":"";
  $bloq1=$db->result[0]['bloquant']?"checked='checked'":"";
  $bloq2=!$db->result[0]['bloquant']?"checked='checked'":"";
  $action="modif";
}

//	Ajout d'un poste
else{
  echo "<h3>Ajout d'un poste</h3>\n";
  $action="ajout";
  $id=null;
  $nom=null;
  $etage=null;
  $categorie=null;
  $obligatoire="checked='checked'";
  $renfort=null;
  $stat1="checked='checked'";
  $stat2=null;
  $bloq1="checked='checked'";
  $bloq2=null;
  $activites=array();
  $site=0;
}

$checked=null;

// Recherche des étages
$db=new db();
$db->select("select_etages",null,null,"order by rang");
$etages=$db->result;

// Recherche des catégories
$db=new db();
$db->select("select_categories",null,null,"order by rang");
$categories_list=$db->result;

echo "<form method='get' action='#' name='form'>";
echo "<input type='hidden' name='page' value='postes/valid.php' />\n";
echo "<table style='width:100%'>";
echo "<tr style='vertical-align:top;'><td style='width:50%'>\n";
echo "<table>\n";
echo "<tr><td style='width:160px'>";
echo "Nom du poste :";
echo "</td><td>";
echo "<input type='text' value='$nom' name='nom' style='width:250px'/>";
echo "</td></tr>";

if($config['Multisites-nombre']>1){
  echo "<tr><td>Site</td>\n";
  echo "<td><select name='site' style='width:255px'>";
  echo "<option value='0'>&nbsp;</option>\n";
  for($i=1;$i<count($config['Multisites-nombre'])+2;$i++){
    $selected=$site==$i?"selected='selected'":null;
    echo "<option value='$i' $selected >".$config["Multisites-site{$i}"]."</option>\n";
  }
  echo "</select>";
  echo "</td></tr>\n";
}

echo "<tr><td>";
echo "Etage :";
echo "</td><td>";
echo "<select name='etage' style='width:255px'>";
echo "<option value=''>&nbsp;</option>\n";
foreach($etages as $elem){
  $selected=$etage==$elem['valeur']?"selected='selected'":null;
  echo "<option value='{$elem['valeur']}' $selected >{$elem['valeur']}</option>\n";
}
echo "</select>\n";
echo "<a href='javascript:popup(\"include/ajoutSelect.php&amp;table=select_etages&amp;terme=&eacute;tage\",400,400);'>\n";
echo "<img src='img/add.gif' alt='*' style=width:15px;'/></a>\n";
echo "</td></tr>";

echo "<tr><td style='padding-top:20px;'>";
echo "Obligatoire / renfort :";
echo "</td><td style='padding-top:20px;'>";
echo "<input type='radio' name='obligatoire' value='Obligatoire' $obligatoire/> Obligatoire\n";
echo "<input type='radio' name='obligatoire' value='Renfort' $renfort/> Renfort\n";
echo "</td></tr>";

echo "<tr><td>";
echo "Bloquant :";
echo "</td><td>";
echo "<input type='radio' name='bloquant' value='1' $bloq1/> Oui\n";
echo "<input type='radio' name='bloquant' value='0' $bloq2/> Non\n";
echo "</td></tr>";

echo "<tr><td>";
echo "Statistiques :";
echo "</td><td>";
echo "<input type='radio' name='statistiques' value='1' $stat1/> Oui\n";
echo "<input type='radio' name='statistiques' value='0' $stat2/> Non\n";
echo "</td></tr>";

echo "</table>\n";
echo "</td><td>\n";
echo "<table>\n";

echo "<tr style='vertical-align:top;'><td>";
echo "Activités :";
echo "</td><td>";
if(is_array($actList->result)){
  foreach($actList->result as $elem){
    if($activites){
      $checked=in_array($elem['id'],$activites)?"checked='checked'":"";
    }
    echo "<input type='checkbox' name='activites[]' value='{$elem['id']}' $checked/> {$elem['nom']}<br/>\n";
  }
}
echo "</td></tr>";

if(is_array($categories_list) and !empty($categories_list)){
  echo "<tr style='vertical-align:top;'><td style='padding-top:20px;'>";
  echo "Cat&eacute;gories<sup>*</sup> :";
  echo "</td><td style='padding-top:26px;'>";
  foreach($categories_list as $elem){
    $checked=in_array($elem['id'],$categories)?"checked='checked'":"";
    echo "<input type='checkbox' name='categories[]' value='{$elem['id']}' $checked/> {$elem['valeur']}<br/>\n";
  }
  echo "</td></tr>";
}

echo "</table>\n";

echo "</td></tr>\n";
echo "<tr><td colspan='2' style='text-align:center;'>\n";
echo "<br/><br/>";
echo "<input type='hidden' value='$action' name='action'/>";
echo "<input type='hidden' value='$id' name='id'/>\n";
echo "<input type='button' value='Annuler' onclick='history.go(-1);' class='ui-button'/>\n";
echo "&nbsp;&nbsp;&nbsp;\n";
echo "<input type='submit' value='Valider' class='ui-button'/>\n";
echo "</td></tr>\n";

echo "<tr><td colspan='2' class='noteBasDePage'>\n";
echo "* Si aucune cat&eacute;gorie n&apos;est s&eacute;lectionn&eacute;e, les agents de toutes les cat&eacute;gories pourront &ecirc;tre plac&eacute;s sur ce poste.";
echo "</td></tr>\n";
echo "</table>\n";
echo "</form>\n";