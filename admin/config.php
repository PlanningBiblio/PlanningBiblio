<?php
/*
Planning Biblio, Version 1.7
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.txt et COPYING.txt
Copyright (C) 2011-2014 - Jérôme Combes

Fichier : activites/config.php
Création : mai 2011
Dernière modification : 26 décembre 2013
Auteur : Jérôme Combes, jerome@planningbilbio.fr

Description :
Affiche et modifie les paramètres de configuration (Serveur Mail, autres options) : Formulaire et validation

Page appelée par la page index.php
*/

// pas de $version=acces direct  => redirection vers la page index.php
if(!$version){
  header("Location: ../index.php");
}

echo "<h3>Configuration</h3>\n";

if(!$_POST){			//		Affichage des paramètres
  $last_category=null;
  $db=new db();
  $db->query("SELECT * FROM `{$dbprefix}config` ORDER BY `categorie`,`ordre`,`id`;");

  echo "<form name='form' action='index.php' method='post'>\n";
  echo "<input type='hidden' name='page' value='admin/config.php' />\n";
  echo "<div id='accordion'>\n";

  foreach($db->result as $elem){
    if(substr($elem['nom'],-9)=="-Password"){
      $elem['valeur']=decrypt($elem['valeur']);
    }

    if(!$last_category){
      echo "<h3>{$elem['categorie']}</h3>\n";
      echo "<div>";
      echo "<table cellspacing='0' cellpadding='5' style='width:100%;'>\n";
      echo "<tr><td class='ui-widget-header ui-corner-left' style='width:200px;border-right:0px;'>Nom</td><td style='width:400px;border-left:0px;border-right:0px;' class='ui-widget-header'>Valeur</td><td class='ui-widget-header ui-corner-right' style='border-left:0px;'>Commentaires</td></tr>\n";
    }
    elseif($elem['categorie']!=$last_category){
      echo "</table>\n";
      echo "</div>\n";
      echo "<h3>{$elem['categorie']}</h3>\n";
      echo "<div>";
      echo "<table cellspacing='0' cellpadding='5' style='width:100%;'>\n";
      echo "<tr><td class='ui-widget-header ui-corner-left' style='width:200px;border-right:0px;'>Nom</td><td style='width:400px;border-left:0px;border-right:0px;' class='ui-widget-header'>Valeur</td><td class='ui-widget-header ui-corner-right' style='border-left:0px;'>Commentaires</td></tr>\n";
    }

    $last_category=$elem['categorie'];
    echo "<tr style='vertical-align:top;'><td style='width:180px;'>{$elem['nom']}</td><td>\n";
    switch($elem['type']){
      case "boolean" :
	$selected=$elem['valeur']?"selected='selected'":null;
	echo "<select name='{$elem['nom']}' style='width:305px;'>\n";
	echo "<option value='0'>0</option>\n";
	echo "<option value='1' $selected>1</option>\n";
	echo "</select>\n";
	break;

      case "enum" :
	echo "<select name='{$elem['nom']}' style='width:305px;'>\n";
	$options=explode(",",$elem['valeurs']);
	foreach($options as $option){
	  $selected=$option==$elem['valeur']?"selected='selected'":null;
	  $selected=$option==htmlentities($elem['valeur'],ENT_QUOTES|ENT_IGNORE,"UTF-8",false)?"selected='selected'":$selected;
	  echo "<option value='$option' $selected >$option</option>\n";
	}
	echo "</select>\n";
	break;

      case "password" :
	echo "<input type='password' name='{$elem['nom']}' value='{$elem['valeur']}' style='width:300px;'/>\n";
	break;

      case "info" :
	echo $elem['valeur'];
	break;                		

      case "textarea" :
	$valeur=str_replace("<br/>","\n",$elem['valeur']);
	echo "<textarea name='{$elem['nom']}' style='width:300px;height:100px;' rows='1' cols='1'>$valeur</textarea>\n";
	break;

      case "date" :
	echo "<input type='text' name='{$elem['nom']}' value='".dateFr($elem['valeur'])."' style='width:300px;' class='datepicker'/>\n";
	break;

      default :
	echo "<input type='text' name='{$elem['nom']}' value='{$elem['valeur']}' style='width:300px;'/>\n";
	break;
    }

    echo "</td><td>{$elem['commentaires']}</td>\n";
    echo "</tr>\n";
    
  }
  echo "</table>\n";
  echo "</div>\n";
  echo "</div>\n";
  echo "<div style='text-align:center;margin:20px;'>\n";
  echo "<input type='button' value='Annuler' onclick='document.location.href=\"index.php\";' />\n";
  echo "&nbsp;&nbsp;&nbsp;\n";
  echo "<input type='submit' value='Valider' />\n";
  echo "</div>\n";
  echo "</form>\n";
}
else{			// enregistrement des paramètres
  $keys=array_keys($_POST);
  $erreur=false;
  $db=new dbh();
  $db->prepare("UPDATE `{$dbprefix}config` SET `valeur`=:valeur WHERE `nom`=:nom");
  foreach($keys as $elem){
    if(!in_array($elem,array("page","Valider","Annuler"))){
      $_POST[$elem]=str_replace("'","&apos;",$_POST[$elem]);
      if(substr($elem,-9)=="-Password"){
	$_POST[$elem]=encrypt($_POST[$elem]);
      }
      // Si date au format JJ/MM/AAAA, conversion en AAAA-MM-JJ
      if(preg_match("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}/",$_POST[$elem])){
	$_POST[$elem]=dateFr($_POST[$elem]);
      }
      $db->execute(array(":nom"=>$elem,":valeur"=>$_POST[$elem]));
    }
  }

  if($erreur){
    echo "<div style='color:red;font-weight:bold;'>Il y a eu des erreurs pendant la modification.<br/>
    Veuillez vérifier la configuration.</div>\n";
  }
  else{
    echo "<b>Les modifications ont été enregistrées.</b>\n";
    echo "<br/><br/><a href='index.php?page=admin/config.php'>Retour</a>\n";
  }
}
?>
<script type='text/JavaScript'>
$("#accordion").accordion({
  heightStyle: "content"
});

$("input[type='button']").button();
$("input[type='submit']").button();
</script>