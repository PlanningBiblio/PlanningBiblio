<?php
/**
Planning Biblio, Version 2.4.3
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2017 Jérôme Combes

Fichier : planning/poste/importer.php
Création : mai 2011
Dernière modification : 3 octobre 2016
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Permet d'importer un modèle de planning.
Affiche les modèles disponibles, copie le tableau du modèle choisi et ses données dans la base de données

Cette page est appelée par la fonction JavaScript Popup qui l'affiche dans un cadre flottant
*/

require_once "class.planning.php";

// Initialisation des variables
$date=filter_input(INPUT_GET,"date",FILTER_SANITIZE_STRING);
$get_absents=filter_input(INPUT_GET,"absents",FILTER_SANITIZE_STRING);
$get_nom=filter_input(INPUT_GET,"nom",FILTER_SANITIZE_STRING);
$site=filter_input(INPUT_GET,"site",FILTER_SANITIZE_NUMBER_INT);

// Contrôle sanitize en 2 temps pour éviter les erreurs CheckMarx
$date=filter_var($date,FILTER_CALLBACK,array("options"=>"sanitize_dateSQL"));
$get_absents=filter_var($get_absents,FILTER_CALLBACK,array("options"=>"sanitize_on"));

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
if(!$get_nom){		// Etape 1 : Choix du modèle à importer
  $db=new db();
  $db->select2("pl_poste_modeles",array("nom","jour"),array("site"=>$site),"GROUP BY `nom`");
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
    echo "<input type='hidden' name='date' value='$date' />\n";
    echo "<input type='hidden' name='site' value='$site' />\n";
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
    echo "<input type='hidden' name='date' value='$date' />\n";
    echo "<input type='hidden' name='site' value='$site' />\n";
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
  if(substr($get_nom,-10)=="###semaine"){	// S'il s'agit d'un modèle sur une semaine
    $semaine=true;
    $d=new datePl($date);
    foreach($d->dates as $elem){	// Recherche de toute les dates de la semaine en cours pour insérer les données
      $dates[]=$elem;
    }
  }
  else{
    $dates[0]=$date;			// S'il ne s'agit pas d'un modèle semaine, insertion seulement pour le jour en cours
  }
  $nom=str_replace("###semaine","",$get_nom);
  $nom=htmlentities($nom,ENT_QUOTES|ENT_IGNORE,"UTF-8",false);
  
  $i=0;
  foreach($dates as $elem){
    $i++;				// utilisé pour la colone jour du modèle (1=lundi, 2=mardi ...) : on commence à 1
    $sql=null;
    $values=array();
    $absents=array();

    // Importation du tableau
    // S'il s'agit d'un modèle pour une semaine
    if($semaine){
      $db=new db();
      $db->select2("pl_poste_modeles_tab","*",array("nom"=>$nom, "site"=>$site, "jour"=>$i));
    // S'il s'agit d'un modèle pour un seul jour
    }else{
      $db=new db();
      $db->select2("pl_poste_modeles_tab","*",array("nom"=>$nom, "site"=>$site));
    }

    $tableau=$db->result[0]['tableau'];
    $db=new db();
    $db->delete2("pl_poste_tab_affect",array("date"=>$elem, "site"=>$site));
    $db=new db();
    $db->insert2("pl_poste_tab_affect", array("date"=>$elem ,"tableau"=>$tableau ,"site"=>$site ));

    // Importation des agents
    // S'il s'agit d'un modèle pour une semaine
    if($semaine){
      $db=new db();
      $db->select2("pl_poste_modeles","*", array("nom"=>$nom, "site"=>$site, "jour"=>$i));
    // S'il s'agit d'un modèle pour un seul jour
    }else{
      $db=new db();
      $db->select2("pl_poste_modeles","*", array("nom"=>$nom, "site"=>$site));
    }

    $filter=$config['Absences-validation']?"AND `valide`>0":null;
    if($db->result){
      if($get_absents){	// on marque les absents
	foreach($db->result as $elem2){
	  $debut=$elem." ".$elem2['debut'];
	  $fin=$elem." ".$elem2['fin'];
	  $db2=new db();
	  $db2->select("absences","*","`debut`<'$fin' AND `fin`>'$debut' AND `perso_id`='{$elem2['perso_id']}' $filter ");
	  $absent=$db2->result?"1":"0";
	  $values[]=array(":date"=>$elem, ":perso_id"=>$elem2['perso_id'], ":poste"=>$elem2['poste'], 
	    ":debut"=>$elem2['debut'], ":fin"=>$elem2['fin'], ":absent"=>$absent, ":site"=>$site);
	}
      }
      else{
	foreach($db->result as $elem2){
	  $debut=$elem." ".$elem2['debut'];
	  $fin=$elem." ".$elem2['fin'];
	  $db2=new db();
	  $db2->select("absences","*","`debut`<'$fin' AND `fin`>'$debut' AND `perso_id`='{$elem2['perso_id']}' $filter ");
	  if($db2->nb==0){
	    $values[]=array(":date"=>$elem, ":perso_id"=>$elem2['perso_id'], ":poste"=>$elem2['poste'], 
	      ":debut"=>$elem2['debut'], ":fin"=>$elem2['fin'], ":absent"=>"0", ":site"=>$site);
	  }
	}
      }
      
      // insertion des données dans le planning du jour
      if(!empty($values)){
	// Suppression des anciennes données
	$db=new db();
	$db->delete2("pl_poste", array("date"=>$elem, "site"=>$site));

	// Insertion des nouvelles données
	$req="INSERT INTO `{$dbprefix}pl_poste` (`date`,`perso_id`,`poste`,`debut`,`fin`,`absent`,`site`) ";
	$req.="VALUES (:date, :perso_id, :poste, :debut, :fin, :absent, :site);";
	$dbh=new dbh();
	$dbh->prepare($req);
	foreach($values as $value){
	  $dbh->execute($value);
	}
      }
    }
  }
  echo "<script type='text/JavaScript'>top.document.location.href=\"index.php?date=$date\";</script>\n";
}
?>