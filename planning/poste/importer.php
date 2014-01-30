<?php
/*
Planning Biblio, Version 1.6.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : planning/poste/importer.php
Création : mai 2011
Dernière modification : 22 novembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Permet d'importer un modèle de planning.
Affiche les modèles disponibles, copie le tableau du modèle choisi et ses données dans la base de données

Cette page est appelée par la fonction JavaScript Popup qui l'affiche dans un cadre flottant
*/

require_once "class.planning.php";

// Initialisation des variables
$date=$_SESSION['PLdate'];
$site=$_SESSION['oups']['site'];
$attention="<span style='color:red;'>Attention, le planning actuel sera remplacé par le modèle<br/><br/></span>\n";

// Sécurité
// Refuser l'accès aux agents n'ayant pas les droits de modifier le planning
$access=true;
$droit=($config['Multisites-nombre']>1)?(300+$site):12;
if(!in_array($droit,$droits)){
  echo "<div id='acces_refuse'>Accès refusé</div>";
  echo "<a href='javascript:popup_closed();'>Fermer</a>\n";
  exit;
}

echo <<<EOD
  <div style='text-align:center'>
  <br/>
  <b>Importation d'un modèle</b>
  <br/><br/>
EOD;

if(!isset($_GET['nom'])){		// Etape 1 : Choix du modèle à importer
  $db=new db();
  $db->query("SELECT `nom`,`jour` FROM `{$dbprefix}pl_poste_modeles` WHERE `site`='$site' GROUP BY `nom`;");
  if(!$db->result){			// Aucun modèle enregistré
    echo "Aucun modèle enregistré<br/><br/><a href='javascript:popup_closed();'>Fermer</a>\n";
  }
  elseif($db->nb==1){			// Si un seul modèle est enregistré
    echo $attention;
    $semaine=$db->result[0]['jour']?"(semaine) ":null;
    $sem=$db->result[0]['jour']?"###semaine":null;
    $nom=$db->result[0]['nom'].$sem;
    echo "<form name='form' method='get' action='index.php' onsubmit='return ctrl_form(\"nom\");'>\n";
    echo "<input type='hidden' name='page' value='planning/poste/importer.php' />\n";
    echo "<input type='hidden' name='menu' value='off' />\n";
    echo "<input type='hidden' name='nom' value='$nom' />\n";
    echo "Importer le modèle \"{$db->result[0]['nom']}\" $semaine?<br/><br/>\n";
    echo "Importer les absents ?&nbsp;&nbsp;";
    echo "<input type='checkbox' name='absents' /><br/><br/>\n";
    echo "<a href='#' onclick='document.form.submit();'>Oui</a>";
    echo "&nbsp;&nbsp;\n";
    echo "<a href='javascript:popup_closed();'>Non</a>\n";
    echo "</form>\n";
  }
  else{					// Si plusieurs modèles sont enregistrés : menu déroulant
    echo $attention;
    echo "Sélectionnez le modèle à importer<br/><br/>\n";
    echo "<form name='form' method='get' action='index.php' onsubmit='return ctrl_form(\"nom\");'>\n";
    echo "<input type='hidden' name='page' value='planning/poste/importer.php' />\n";
    echo "<input type='hidden' name='menu' value='off' />\n";
    echo "<select name='nom' id='nom'>\n";
    echo "<option value=''>&nbsp;</option>\n";
    foreach($db->result as $elem){
      $semaine=$elem['jour']?"&nbsp;&nbsp;&nbsp;(semaine)":null;
      $sem=$elem['jour']?"###semaine":null;
      echo "<option value='{$elem['nom']}$sem'>{$elem['nom']} $semaine</option>\n";
    }
    echo "</select><br/>\n";
    echo "Importer les absents ?&nbsp;&nbsp;";
    echo "<input type='checkbox' name='absents' /><br/><br/>\n";
    echo "<input type='button' value='Annuler' onclick='popup_closed();' />\n";
    echo "&nbsp;&nbsp;\n";
    echo "<input type='submit' value='Valider'/>\n";
    echo "</form>\n";
  }
}
else{					// Etape 2 : Insertion des données
  $semaine=false;
  $dates=array();
  if(substr($_GET['nom'],-10)=="###semaine"){	// S'il s'agit d'un modèle sur une semaine
    $semaine=true;
    $d=new datePl($date);
    foreach($d->dates as $elem){	// Recherche de toute les dates de la semaine en cours pour insérer les données
      $dates[]=$elem;
    }
  }
  else{
    $dates[0]=$date;			// S'il ne s'agit pas d'un modèle semaine, insertion seulement pour le jour en cours
  }
  $nom=str_replace("###semaine","",$_GET['nom']);
  $nom=htmlentities($nom,ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
  
  $i=0;
  foreach($dates as $elem){
    $i++;				// utilisé pour la colone jour du modèle (1=lundi, 2=mardi ...) : on commence à 1
    $sql=null;
    $values=Array();
    $absents=Array();
    $jour=$semaine?"AND `jour`='$i'":null;

					// Importation du tableau
    $db=new db();
    $db->query("SELECT * FROM `{$dbprefix}pl_poste_modeles_tab` WHERE `nom`='$nom' AND `site`='$site' $jour;");
    $tableau=$db->result[0]['tableau'];
    $db=new db();
    $db->query("DELETE FROM `{$dbprefix}pl_poste_tab_affect` WHERE `date`='$elem' AND `site`='$site';");
    $db=new db();
    $db->query("INSERT INTO `{$dbprefix}pl_poste_tab_affect` (`date`,`tableau`,`site`) VALUES ('$elem','$tableau','$site');");


    $db=new db();
    $db->query("SELECT * FROM `{$dbprefix}pl_poste_modeles` WHERE `nom`='$nom' AND `site`='$site' $jour;");
    $filter=$config['Absences-validation']?"AND `valide`>0":null;
    if($db->result){
      if(isset($_GET['absents'])){	// on marque les absents
	foreach($db->result as $elem2){
	  $debut=$elem." ".$elem2['debut'];
	  $fin=$elem." ".$elem2['fin'];
	  $db2=new db();
	  $db2->select("absences","*","`debut`<'$fin' AND `fin`>'$debut' AND `perso_id`='{$elem2['perso_id']}' $filter ");
	  $absent=$db2->result?1:0;
	  $values[]="('{$elem}','{$elem2['perso_id']}','{$elem2['poste']}','{$elem2['debut']}','{$elem2['fin']}','$absent','$site')";
	}
      }
      else{
	foreach($db->result as $elem2){
	  $debut=$elem." ".$elem2['debut'];
	  $fin=$elem." ".$elem2['fin'];
	  $db2=new db();
	  $db2->select("absences","*","`debut`<'$fin' AND `fin`>'$debut' AND `perso_id`='{$elem2['perso_id']}' $filter ");
	  if($db2->nb==0){
	    $values[]="('{$elem}','{$elem2['perso_id']}','{$elem2['poste']}','{$elem2['debut']}','{$elem2['fin']}','0','$site')";
	  }
	}
      }
      
      if($values){			// insertion des données dans le planning du jour
	$sql="INSERT INTO `{$dbprefix}pl_poste` (`date`,`perso_id`,`poste`,`debut`,`fin`,`absent`,`site`) VALUES ";
	$sql.=join($values,",").";";
	$delete=new db();
	$delete->query("DELETE FROM `{$dbprefix}pl_poste` WHERE `date`='$elem' AND `site`='$site';");
	$insert=new db();
	$insert->query($sql);
      }
    }
  }
  echo "<script type='text/JavaScript'>top.document.location.href=\"index.php?date={$_SESSION['PLdate']}\";</script>\n";
}
?>