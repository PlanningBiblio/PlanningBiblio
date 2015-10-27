<?php
/*
Planning Biblio, Version 2.0.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2013-2015 - Jérôme Combes

Fichier : planningHebdo/modif.php
Création : 23 juillet 2013
Dernière modification : 30 juillet 2015
Auteur : Jérôme Combes, jerome@planningbiblio.fr

Description :
Affiche la liste des plannings de présence pour l'administrateur
Page accessible à partir du menu administration/planning de présence
*/

require_once "class.planningHebdo.php";

// Recherche de la config
$p=new planningHebdo();
$p->getConfig();
$configHebdo=$p->config;

// Initialisation des variables
$copy=filter_input(INPUT_GET,"copy",FILTER_SANITIZE_NUMBER_INT);
$id=filter_input(INPUT_GET,"id",FILTER_SANITIZE_NUMBER_INT);
$retour=filter_input(INPUT_GET,"retour",FILTER_SANITIZE_STRING);

// Période définies = 0 pour le moment. Option plus utilisée par la BUA. Développements complexes.
$config['PlanningHebdo-PeriodesDefinies']=0;

if($copy){
  $id=$copy;
}

// Sécurité
$admin=in_array(24,$droits)?true:false;

if($id){
  $p=new planningHebdo();
  $p->id=$id;
  $p->fetch();
  $debut1=$p->elements[0]['debut'];
  $fin1=$p->elements[0]['fin'];
  $debut1Fr=dateFr($debut1);
  $fin1Fr=dateFr($fin1);

  $perso_id=$p->elements[0]['perso_id'];
  $temps=$p->elements[0]['temps'];
  $valide=$p->elements[0]['valide'];
  $remplace=$p->elements[0]['remplace'];

  // Informations sur l'agents
  $p=new personnel();
  $p->fetchById($perso_id);
  $sites=$p->elements[0]['sites'];

  // Modif autorisée si n'est pas validé ou si validé avec des périodes non définies (BSB).
  // Dans le 2eme cas copie du planning avec modification des dates
  $action="modif";
  $modifAutorisee=true;
  if(!$admin and $valide and $config['PlanningHebdo-PeriodesDefinies']){
    $modifAutorisee=false;
  }
  if(!$admin and !$config['PlanningHebdo-Agents']){
    $modifAutorisee=false;
  }

  if(!$admin and $valide){
    $action="copie";
  }

  if($copy){
    $action="ajout";
  }

}else{
  $action="ajout";
  $modifAutorisee=true;
  $debut1=null;
  $fin1=null;
  $debut1Fr=null;
  $fin1Fr=null;
  $perso_id=$_SESSION['login_id'];
  $temps=null;
  $valide=null;
  $remplace=null;
  $sites=array();
  for($i=1;$i<$config['Multisites-nombre']+1;$i++){
    $sites[]=$i;
  }
}

// Sécurité
if(!$admin and $id and $perso_id!=$_SESSION['login_id']){
  echo "<div id='acces_refuse'>Accès refusé</div>\n";
  include "include/footer.php";
  exit;
}
?>

<!-- Formulaire Planning-->
<h3>Planning de présence</h3>
<?php
if($id and !$copy){
  echo "<h3>Planning de ".nom($perso_id,"prenom nom")." du $debut1Fr au $fin1Fr</h3>";
}
?>
<div id='planning'>
<?php
if(!$config['PlanningHebdo-PeriodesDefinies']){
  echo "<form name='form1' method='post' action='index.php' onsubmit='return plHebdoVerifForm();'>\n";
}else{
  echo "<form name='form1' method='post' action='index.php' onsubmit='return verif_form(\"debut=date1;fin=date2Obligatoire\",\"form1\");'>\n";
}

// Modification
if($id and !$copy){
  echo "<input type='hidden' name='perso_id' value='$perso_id' id='perso_id' />\n";
// Ajout ou copie
}else{
  $db=new db();
  $db->select2("personnel","*",array("supprime"=>0),"order by nom,prenom");

  // Non admin
  if(!$admin){
    echo "<h3>Nouveau planning pour ".nom($perso_id,"prenom nom")."</h3>\n";
  }
  // Copie
  elseif($copy){
    echo "<h3>Copie du planning de ".nom($perso_id,"prenom nom")." du $debut1Fr au $fin1Fr</h3>\n";
  // Ajout par un admin
  } else {
    echo "<h3>Nouveau planning</h3>\n";
  }
  echo "<div id='plHebdo-perso-id'>\n";
  if($admin){
    echo "<label for='perso_id'>Pour l'agent</label>\n";
    echo "<select name='perso_id' class='ui-widget-content ui-corner-all' id='perso_id' style='position:absolute; left:200px; width:200px; text-align:center;' >\n";
    echo "<option value=''>&nbsp;</option>\n";
    foreach($db->result as $elem){
      $selected=$perso_id==$elem['id']?"selected='selected'":null;
      echo "<option value='{$elem['id']}' $selected >{$elem['nom']} {$elem['prenom']}</option>\n";
    }
    echo "</select>\n";
  }else{
    echo "<input type='hidden' name='perso_id' value='$perso_id' id='perso_id' />\n";
  }
  echo "</div>\n";
}

// Choix de la période d'utilisation et validation
echo "<div id='periode'>\n";
if(!$config['PlanningHebdo-PeriodesDefinies']){
  echo <<<EOD
    <p><label for='debut'>Début d'utilisation</label>
    <input type='text' name='debut' value='$debut1Fr' class='datepicker' style='position:absolute; left:200px; width:200px;' /></p>
    <p><label for='fin'>Fin d'utilisation</label>
    <input type='text' name='fin' value='$fin1Fr' class='datepicker' style='position:absolute; left:200px; width:200px;' /></p>
EOD;
}
else{
  echo "<input type='hidden' name='debut' value='$debut1'/>\n";
  echo "<input type='hidden' name='fin' value='$fin1'/>\n";
}
echo "</div> <!-- id=periode -->\n";

?>
<input type='hidden' name='page' value='planningHebdo/valid.php' />
<input type='hidden' name='action' value='<?php echo $action; ?>' />
<input type='hidden' name='validation' value='0' />
<input type='hidden' name='retour' value='<?php echo $retour; ?>' />
<input type='hidden' name='id' value='<?php echo $id; ?>' />
<input type='hidden' name='valide' value='<?php echo $valide; ?>' />
<input type='hidden' name='remplace' value='<?php echo $remplace; ?>' />

<!-- Affichage des tableaux avec la sélection des horaires -->
<?php
switch($config['nb_semaine']){
  case 2	: $cellule=array("Semaine Impaire","Semaine Paire");		break;
  case 3	: $cellule=array("Semaine 1","Semaine 2","Semaine 3");		break;
  default 	: $cellule=array("Jour");					break;
}
$fin=$config['Dimanche']?array(8,15,22):array(7,14,21);
$debut=array(1,8,15);
$jours=Array("Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche");
?>

<?php
for($j=0;$j<$config['nb_semaine'];$j++){

  echo "<h3>{$cellule[$j]}</h3>\n";

  // Affichage de la case à cocher "Même planning qu'en semaine 1"
  if($j>0){
    if($modifAutorisee){
      echo "<p><input type='checkbox' name='memePlanning$j' class='memePlanning' data-id='$j' id='memePlanning$j' />";
      echo "<label for='memePlanning$j' >Même planning qu'en {$cellule[0]}</label></p>\n";
    }else{
      echo "<p style='display:none;' id='memePlanning$j' ><strong>Même planning qu'en {$cellule[0]}</strong></p>\n";
    }
  }

  echo "<div id='div$j'>\n";
  echo "<table border='1' cellspacing='0' id='tableau{$j}' class='tableau' data-id='$j' >\n";
  echo "<tr style='text-align:center;'><td style='width:135px;'>{$cellule[$j]}</td><td style='width:135px;'>Heure d'arrivée</td>";
  echo "<td style='width:135px;'>Début de pause</td><td style='width:135px;'>Fin de pause</td>";
  echo "<td style='width:135px;'>Heure de départ</td>";
  if($config['Multisites-nombre']>1){
    echo "<td style='width:135px;'>Site</td>";
  }
  echo "<td style='width:135px;'>Temps</td>";
  echo "</tr>\n";
  for($i=$debut[$j];$i<$fin[$j];$i++){
    $k=$i-($j*7)-1;
    echo "<tr style='text-align:center;'><td>{$jours[$k]}</td>";
    if($modifAutorisee){
      echo "<td>".selectTemps($i-1,0,null,"select")."</td><td>".selectTemps($i-1,1,null,"select")."</td>";
      echo "<td>".selectTemps($i-1,2,null,"select")."</td><td>".selectTemps($i-1,3,null,"select")."</td>";
    }
    else{
      echo "<td id='temps_".($i-1)."_0' class='td_heures'>".heure2($temps[$i-1][0])."</td><td id='temps_".($i-1)."_1' class='td_heures'>".heure2($temps[$i-1][1])."</td>";
      echo "<td id='temps_".($i-1)."_2' class='td_heures'>".heure2($temps[$i-1][2])."</td><td id='temps_".($i-1)."_3' class='td_heures'>".heure2($temps[$i-1][3])."</td>";
    }
    if($config['Multisites-nombre']>1){
      if($modifAutorisee){
	echo "<td><select name='temps[".($i-1)."][4]' class='select selectSite'>\n";
	if(count($sites)>1){
	  echo "<option value=''>&nbsp;</option>\n";
	}
	foreach($sites as $site){
	  $selected=$temps[$i-1][4]==$site?"selected='selected'":null;
	  echo "<option value='$site' $selected >{$config["Multisites-site{$site}"]}</option>\n";
	}
	echo "</select></td>";
      }else{
	$site=$temps[$i-1][4];
	$site=$site?$config["Multisites-site{$site}"]:"&nbsp;";
	echo "<td class='td_heures'>$site</td>\n";
      }
    }
    echo "<td id='heures_{$j}_$i'></td>\n";
    echo "</tr>\n";
  }
  echo "</table>\n";
  echo "Nombre d'heures : <font id='heures_{$j}' style='font-weight:bold;'>&nbsp;</font><br/>\n";
  echo "</div>\n";
}

echo "<div id='informations' style='margin-top:30px;' >\n";

if(!$modifAutorisee){
  echo "<p><b class='important'>Vos horaires ont été validés.</b><br/>Pour les modifier, contactez votre chef de service.</p>\n";
}
elseif($valide and !$admin){
  echo "<p><b class='important'>Vos horaires ont été validés.</b><br/>Si vous souhaitez les changer, modifiez la date de début et/ou de fin d'effet.<br/>";
  echo "Vos nouveaux horaires seront enregistrés et devront être validés par un administrateur.<br/>";
  echo "Les anciens horaires seront conservés en attendant la validation des nouveaux.</p>\n";
}
elseif($valide and $admin and !$config['PlanningHebdo-PeriodesDefinies'] and !$copy){
  echo "<p style='width:850px;text-align:justify;margin-top:30px;'><b class='important'>Ces horaires ont été validés.</b><br/>";
  echo "En tant qu'administrateur, vous pouvez les modifier et les enregistrer en tant que copie.<br/>";
  echo "Dans ce cas, modifiez la date de début et/ou de fin d'effet. ";
  echo "Les nouveaux horaires seront enregistrés et devront ensuite être validés. ";
  echo "Les anciens horaires seront conservés en attendant la validation des nouveaux.<br/>";
  echo "Vous pouvez également les enregistrer directement mais dans ce cas, vous ne conserverez pas les anciens horaires.</p>\n";
}
elseif($valide and $admin and $config['PlanningHebdo-PeriodesDefinies'] and !$copy){
  echo "<p style='width:850px;text-align:justify;'><b class='important'>Ces horaires ont été validés.</b><br/>";
  echo "En tant qu'administrateur, vous avez toujours la possibilité de les modifier et de les valider.</p>\n";
}

if($copy and $config['Multisites-nombre']>1){
  echo <<<EOD
  <p id='info_copie' style='display:none;' class='important'><strong>
    Attention : Veuillez vérifier les affectations aux sites avant d'enregistrer.
  </strong></p>
EOD;
}
echo "</div> <!-- id=informations -->\n";

echo "<div id='boutons' style='padding-top:20px;'>\n";
echo "<input type='button' value='Retour' onclick='location.href=\"index.php?page=planningHebdo/$retour\";' class='ui-button' />\n";

if($admin){
  echo "<input type='submit' value='Enregistrer SANS valider' style='margin-left:30px;' class='ui-button' />\n";
  if(!$config['PlanningHebdo-PeriodesDefinies']){
    echo "<input type='button' value='Enregistrer et VALIDER'  style='margin-left:30px;' onclick='document.forms[\"form1\"].validation.value=1;if(plHebdoVerifForm()){document.forms[\"form1\"].submit();}' class='ui-button' />";
  }else{
    echo "<input type='button' value='Enregistrer et VALIDER'  style='margin-left:30px;' onclick='document.forms[\"form1\"].validation.value=1;document.forms[\"form1\"].submit();' class='ui-button' />";
  }
  if($valide and !$config['PlanningHebdo-PeriodesDefinies'] and !$copy){
    echo "<input type='button' value='Enregistrer une copie' style='margin-left:30px;' onclick='$(\"input[name=action]\").val(\"copie\");$(\"form[name=form1]\").submit();' class='ui-button' />\n";
  }
}
elseif($modifAutorisee){
  echo "<input type='submit' value='Enregistrer les modifications' style='margin-left:30px;' class='ui-button' />\n";
}

?>
</div> <!-- id=boutons -->

</form>
<script type='text/JavaScript'>
$("document").ready(function(){
  plHebdoCalculHeures2();
  plHebdoMemePlanning();
});
$(".select").change(function(){
  plHebdoCalculHeures($(this),"");
  plHebdoChangeHiddenSelect();
});
$("#perso_id").change(function(){
  $("#info_copie").show();
});
</script>

</div> <!-- Planning -->