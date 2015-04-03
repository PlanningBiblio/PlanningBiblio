<?php
/*
Planning Biblio, Version 1.9.4
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : absences/modif.php
Création : mai 2011
Dernière modification : 2 avril 2015
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Formulaire permettant de modifier

Page appelée par la page index.php
*/

require_once "class.absences.php";
require_once "motifs.php";

//	Initialisation des variables
$display=null;
$checked=null;
$admin=in_array(1,$droits)?true:false;
$adminN2=in_array(8,$droits)?true:false;
$quartDHeure=$config['heuresPrecision']=="quart d&apos;heure"?true:false;
$id=$_GET['id'];

$db=new db();
$db->selectInnerJoin(array("absences","perso_id"),array("personnel","id"),
  array("id","debut","fin","nbjours","motif","motif_autre","valide","validation","valideN1","validationN1","pj1","pj2","so","commentaires","demande"),
  array(array("name"=>"id","as"=>"perso_id"),"nom","prenom","sites"),
  array("id"=>$id));

$perso_id=$db->result[0]['perso_id'];
$motif=$db->result[0]['motif'];
$motif_autre=$db->result[0]['motif_autre'];
$commentaires=$db->result[0]['commentaires'];
$demande=dateFr($db->result[0]['demande'],true);
$debutSQL=$db->result[0]['debut'];
$finSQL=$db->result[0]['fin'];
$debut=dateFr3($debutSQL);
$fin=dateFr3($finSQL);
$sitesAgent=unserialize($db->result[0]['sites']);
$valide=$db->result[0]['valide'];
$validation=$db->result[0]['validation'];
$valideN1=$db->result[0]['valideN1'];
$validationN1=$db->result[0]['validationN1'];
$hre_debut=substr($debut,-8);
$hre_fin=substr($fin,-8);
$debut=substr($debut,0,10);
$fin=substr($fin,0,10);
if($hre_debut=="00:00:00" and $hre_fin=="23:59:59"){
  $checked="checked='checked'";
  $display="style='display:none;'";
}
$select1=$valide==0?"selected='selected'":null;
$select2=$valide>0?"selected='selected'":null;
$select3=$valide<0?"selected='selected'":null;
$select4=null;
$select5=null;
if($valide==0 and $valideN1!=0){
  $select4=$valideN1>0?"selected='selected'":null;
  $select5=$valideN1<0?"selected='selected'":null;
}
$validation_texte=$valide>0?"Valid&eacute;e":"&nbsp;";
$validation_texte=$valide<0?"Refus&eacute;e":$validation_texte;
$validation_texte=$valide==0?"Demand&eacute;e":$validation_texte;
if($valide==0 and $valideN1!=0){
  $validation_texte=$valideN1>0?"Valid&eacute;e (en attente de validation hi&eacute;rarchique)":$validation_texte;
  $validation_texte=$valideN1<0?"Refus&eacute;e (en attente de validation hi&eacute;rarchique)":$validation_texte;
}

$display_autre=in_array(strtolower($motif),array("autre","other"))?null:"style='display:none;'";

// Pièces justificatives
$pj1Checked=$db->result[0]['pj1']?"checked='checked'":null;
$pj2Checked=$db->result[0]['pj2']?"checked='checked'":null;
$soChecked=$db->result[0]['so']?"checked='checked'":null;


// Sécurité
// Droit 1 = modification de toutes les absences
// Droit 6 = modification de ses propres absences
// Les admins ont toujours accès à cette page
$acces=in_array(1,$droits)?true:false;
if(!$acces){
  // Les non admin ayant le droits de modifier leurs absences ont accès si l'absence les concerne
  $acces=(in_array(6,$droits) and $perso_id==$_SESSION['login_id'])?true:false;
}
// Si config Absences-adminSeulement, seuls les admins ont accès à cette page
if($config['Absences-adminSeulement'] and !in_array(1,$droits)){
  $acces=false;
}
if(!$acces){
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
  include "include/footer.php";
  exit;
}

// Multisites, ne pas afficher les absences des agents d'un site non géré
if($config['Multisites-nombre']>1){
  $sites=array();
  for($i=1;$i<=$config['Multisites-nombre'];$i++){
    if(in_array((200+$i),$droits)){
      $sites[]=$i;
    }
  }

  $admin=false;
  if(is_array($sitesAgent)){
    foreach($sitesAgent as $site){
      if(in_array($site,$sites)){
	$admin=true;
      }
    }
  }
  if(!$admin){
    echo "<h3>Modification de l'absence</h3>\n";
    echo "Vous n'êtes pas autorisé(e) à modifier cette absence.<br/><br/>\n";
    echo "<a href='index.php?page=absences/voir.php'>Retour à la liste des absences</a><br/><br/>\n";
    include "include/footer.php";
    exit;
  }
}

echo "<h3>Modification de l'absence</h3>\n";
echo "<form name='form' method='get' action='index.php' onsubmit='return verif_absences(\"debut=date1;fin=date2;motif\");'>\n";
echo "<input type='hidden' name='page' value='absences/modif2.php' />\n";
echo "<input type='hidden' name='perso_id' value='$perso_id' />\n";		// nécessaire pour verif_absences
echo "<input type='hidden' id='admin' value='".($admin?1:0)."' />\n";
echo "<table class='tableauFiches'>\n";
echo "<tr><td><label class='intitule'>Nom, Prénom</label></td><td>";
echo $db->result[0]['nom'];
echo "&nbsp;";
echo $db->result[0]['prenom'];
echo "</td></tr>\n";
echo "<tr><td>\n";
echo "<label class='intitule'>Journée(s) entière(s)</label>\n";
echo "</td><td>\n";
echo "<input type='checkbox' name='allday' $checked onclick='all_day();'/>\n";
echo "</td></tr>\n";
echo "<tr><td>";
echo "<label class='intitule'>Date de début</label></td><td style='white-space:nowrap;'>";
echo "<input type='text' name='debut' value='$debut' style='width:100%;' class='datepicker'/>\n";
echo "</td></tr>\n";
echo "<tr id='hre_debut' $display ><td>\n";
echo "<label class='intitule'>Heure de début</label>\n";
echo "</td><td>\n";
echo "<select name='hre_debut' class='center ui-widget-content ui-corner-all'>\n";
selectHeure(7,23,true,$quartDHeure,$hre_debut);
echo "</select>\n";
echo "</td></tr>\n";

echo "<tr><td>";
echo "<label class='intitule'>Date de fin</label></td><td style='white-space:nowrap;'>";
echo "<input type='text' name='fin' value='$fin' style='width:100%;' class='datepicker'/>\n";
echo "</td></tr>\n";
echo "<tr id='hre_fin' $display ><td>\n";
echo "<label class='intitule'>Heure de fin</label>\n";
echo "</td><td>\n";
echo "<select name='hre_fin' class='center ui-widget-content ui-corner-all'>\n";
selectHeure(7,23,true,$quartDHeure,$hre_fin);
echo "</select>\n";
echo "</td></tr>\n";

echo "<tr><td><label class='intitule'>Motif</label></td>\n";
echo "<td style='white-space:nowrap;'>";

echo "<select name='motif' style='width:100%;' class='ui-widget-content ui-corner-all'>\n";
echo "<option value=''></option>\n";
foreach($motifs as $elem){
  $selected=$elem['valeur']==$motif?"selected='selected'":null;
  $class=$elem['type']==2?"padding20":"bold";
  $disabled=$elem['type']==1?"disabled='disabled'":null;
  echo "<option value='".$elem['valeur']."' $selected class='$class' $disabled >".$elem['valeur']."</option>\n";
}
echo "</select>\n";
if($admin){
  echo "<span class='pl-icon pl-icon-add' title='Ajouter' style='cursor:pointer;' id='add-motif-button'/>\n";
}
echo "</td></tr>\n";

echo "<tr $display_autre id='tr_motif_autre'><td><label class='intitule'>Motif (autre)</label></td>\n";
echo "<td><input type='text' name='motif_autre' style='width:100%;' value='$motif_autre' class='ui-widget-content ui-corner-all'/></td></tr>\n";

echo "<tr style='vertical-align:top;'><td>\n";
echo "<label class='intitule'>Commentaires</label></td><td>";
echo "<textarea name='commentaires' cols='25' rows='5' class='ui-widget-content ui-corner-all'>$commentaires</textarea>";
echo "</td></tr>";

if(in_array(701,$droits)){
  echo "<tr style='vertical-align:top;'><td>\n";
  echo "<label class='intitule'>Pi&egrave;ces justificatives</label></td><td>";
  echo "<div class='absences-pj-fiche'>PJ1 <input type='checkbox' name='pj1' id='pj1' $pj1Checked /></div>";
  echo "<div class='absences-pj-fiche'>PJ2 <input type='checkbox' name='pj2' id='pj2' $pj2Checked /></div>";
  echo "<div class='absences-pj-fiche'>SO <input type='checkbox' name='so' id='so' $soChecked /></div>";
  echo "</td>\n";
}
echo "</tr>\n";

if($config['Absences-validation']){
  echo "<tr><td><label class='intitule'>Validation</label></td><td>\n";
  if($admin){
    echo "<select name='valide' class='ui-widget-content ui-corner-all'>\n";
    echo "<option value='0' $select1>Demand&eacute;e</option>\n";
    echo "<option value='2' $select4>Accept&eacute;e (En attente de validation hi&eacute;rarchique)</option>\n";
    echo "<option value='-2' $select5>Refus&eacute;e (En attente de validation hi&eacute;rarchique)</option>\n";
    if($adminN2){
      echo "<option value='1' $select2>Accept&eacute;e</option>\n";
      echo "<option value='-1' $select3>Refus&eacute;e</option>\n";
    }
    echo "</select>\n";
  }
  else{
    echo $validation_texte;
    echo "<input type='hidden' name='valide' value='$valide' />\n";
  }
  echo "</td></tr>\n";
}

echo <<<EOD
  <tr><td><label>Demande</label></td>
  <td>$demande</td></tr>
EOD;

echo "<tr><td colspan='2'><br/>\n";
if($admin or ($valide==0 and $valideN1==0) or $config['Absences-validation']==0){
  echo "<input type='button' class='ui-button' value='Supprimer' onclick='document.location.href=\"index.php?page=absences/delete.php&amp;id=$id\";'/>";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='button' class='ui-button' value='Annuler' onclick='annuler(1);'/>\n";
  echo "&nbsp;&nbsp;\n";
  echo "<input type='submit' class='ui-button' value='Valider'/>\n";
}
else{
  echo "<a href='index.php?page=absences/voir.php' class='ui-button'>Retour</a>\n";
}
echo "</td></tr>\n";
echo "</table>\n";
echo "<input type='hidden' name='id' value='$id'/>";
echo "</form>\n";
?>