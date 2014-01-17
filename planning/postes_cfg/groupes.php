<?php
/*
Planning Biblio, Version 1.6.5
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/postes_cfg/groupes.php
Création : 18 septembre 2012
Dernière modification : 7 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet de créer et de modifier des groupes de tableaux. Permet de grouper différents tableaux par semaine 
(du lundi au dimanche). Affiche un formulaire demandant le nom du groupe et un menu déroulant par jour demandant le tableau
à affecter.

Page appelée par le fichier index.php lors du click sur "Nouveau tableau" ou sur l'icône "modifier" de la page 
planning/postes_cfg/index.php
Validation assurée par le fichier planning/postes_cfg/groupes2.php
*/

require_once "class.tableaux.php";

//	Recherche des tableaux
$t=new tableau();
$t->fetchAll();
$tableaux=$t->elements;

//	Recherche des groupes
$t=new tableau();
$t->fetchAllGroups();
$groupes=$t->elements;

//	Modification d'un groupe
if(isset($_GET['id'])){
  $id=$_GET['id'];
  //	Recherche du groupe
  $t=new tableau();
  $t->fetchGroup($id);
  $groupe=$t->elements;
  $titre="Modification du groupe";
  //	Supprime le nom actuel de la liste des noms deja utilises
  $key=array_keys($groupes,$groupe);
  unset($groupes[$key[0]]);	
}
//	Ajout d'un groupe
else{
  $id=null;
  $titre="Nouveau groupe";
  $groupe=array("nom"=>null);
}

$semaine=array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
if($config['Dimanche'])
  $semaine[]="Dimanche";
$champs='"Nom,'.join(',',$semaine).'"';		//	Pour ctrl_form

//	Control du nom (verifie s'il n'est pas utilise en JavaScript
echo "<script type='text/JavaScript'>\n";
echo "grp_nom=new Array();\n";
foreach($groupes as $elem){
  echo "grp_nom.push(\"".strtolower($elem['nom'])."\");\n";
}
echo "</script>\n";

//	Affichage
echo <<<EOD
<h3>$titre</h3>
<form name='form' method='post' action='index.php' onsubmit='return ctrl_form($champs);'>
<input type='hidden' name='page' value='planning/postes_cfg/groupes2.php' />
<input type='hidden' name='id' value='$id' />
<table style='width:900px;'>
<tr><td style='width:200px;'>Nom du groupe</td>
  <td style='width:300px;'><input type='text' name='nom' id='Nom' value='{$groupe['nom']}' style='width:100%;' onkeyup='ctrl_nom(this);'/></td>
  <td style='padding-left:30px;color:red;'><font id='nom_utilise' style='display:none;'>
    Ce nom est d&eacute;j&agrave; utilis&eacute;</font></td></tr>
EOD;

if($config['Multisites-nombre']>1){
  echo "<tr><td>Site</td>\n";
  echo "<td><select name='site' id='selectSite' style='width:100%;'>\n";
  echo "<option value=''>&nbsp;</option>\n";
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    $selected=$groupe['site']==$i?"selected='selected'":null;
    echo "<option value='$i' $selected >".$config["Multisites-site$i"]."</option>\n";
  }
  echo "</select></td></tr>\n";
}

echo "<tr><td colspan='2' style='padding-top:20px;text-align:justify;'>Choisissez les tableaux que vous souhaitez affecter &agrave; chacun des jours de la semaine</td></tr>\n";
foreach($semaine as $jour){
  echo <<<EOD
  <tr><td style='padding-left:20px;'>$jour</td>
    <td><select name='$jour' id='$jour' class='selectTableaux' style='width:100%;'>
    <option value=''>&nbsp;</option>
EOD;
    foreach($tableaux as $tab){
      $selected=$tab['tableau']==$groupe[$jour]?"selected='selected'":null;
      echo "<option value='{$tab['tableau']}' $selected class='optionSite{$tab['site']}'>{$tab['nom']}</option>\n";
    }
  echo "</select></td></tr>\n";
}

echo <<<EOD
<tr><td colspan='2' style='text-align:center;padding-top:20px;'>
  <input type='button' value='Annuler' onclick='history.back();' />
  <input type='submit' value='Valider' style='margin-left:30px;' id='submit'/>
</table>
</form>
EOD;

if($config['Multisites-nombre']>1){
  echo <<<EOD
  <script type='text/JavaScript'>
  $(document).ready(function(){
    $(".optionSite1").hide();
    $(".optionSite2").hide();
    $(".optionSite"+$("#selectSite").val()).show();
  });
  $("#selectSite").change(function(){
    $(".optionSite1").hide();
    $(".optionSite2").hide();
    $(".optionSite"+$("#selectSite").val()).show();
    $(".selectTableaux").val("");
  });
  </script>
EOD;
}
?>