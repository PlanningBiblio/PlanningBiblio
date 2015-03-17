<?php
/*
Planning Biblio, Version 1.8.8
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
Copyright (C) 2011-2015 - Jérôme Combes

Fichier : activites/config.php
Création : mai 2011
Dernière modification : 16 décembre 2014
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

// Enregistrement des paramètres
if($_POST){
  // Si les checkboxes ne sont pas cochées, elles ne sont pas transmises donc pas réinitialisées. Donc on les réinitialise ici.
  $db=new db();
  $db->select("config","nom","type='checkboxes'");
  if($db->result){
    foreach($db->result as $elem){
      if(!array_key_exists($elem['nom'],$_POST)){
	$_POST[$elem['nom']]=array();
      }
    }
  }

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
      
      // Checkboxes
      if(is_array($_POST[$elem])){
	$_POST[$elem]=serialize($_POST[$elem]);
      }
	
      $db->execute(array(":nom"=>$elem,":valeur"=>$_POST[$elem]));
    }
  }

  if($erreur){
    echo <<<EOD
      <script type='text/JavaScript'>
      information('Il y a eu des erreurs pendant la modification.<br/>Veuillez vérifier la configuration.','error');
      </script>
EOD;
  }
  else{
    echo <<<EOD
      <script type='text/JavaScript'>
      information('Les modifications ont été enregistrées.','highlight');
      </script>
EOD;
  }
}


// Affichage des paramètres
$last_category=null;
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}config` ORDER BY `categorie`,`ordre`,`id`;");

echo "<form name='form' action='index.php' method='post'>\n";
echo "<input type='hidden' name='page' value='admin/config.php' />\n";
echo "<div id='accordion' class='ui-accordion'>\n";

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

    // Checkboxes
    // Valeurs proposées (champ valeurs) = tableau PHP à 2 dimensions
    // Valeurs choisies (champ valeur) =  tableau PHP à 1 dimension
    case "checkboxes" :
      $valeurs=unserialize($elem['valeurs']);
      $choisies=unserialize($elem['valeur']);
      if(is_array($valeurs)){
	foreach($valeurs as $val){
	  $checked=in_array($val[0],$choisies)?"checked='checked'":null;
	  echo "<input type='checkbox' name='{$elem['nom']}[]' value='{$val[0]}' $checked /> {$val[1]}<br/>\n";
	}
      }
      break;

    // Select avec valeurs séparées par des virgules
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

    // Select avec valeurs dans un tableau PHP à 2 dimensions
    case "enum2" :
      echo "<select name='{$elem['nom']}' style='width:305px;'>\n";
      $options=unserialize($elem['valeurs']);
      foreach($options as $option){
	$selected=$option[0]==$elem['valeur']?"selected='selected'":null;
	echo "<option value='{$option[0]}' $selected >{$option[1]}</option>\n";
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
echo "<input type='button' value='Annuler' onclick='document.location.href=\"index.php\";' class='ui-button'/>\n";
echo "&nbsp;&nbsp;&nbsp;\n";
echo "<input type='submit' value='Valider' class='ui-button' />\n";
echo "</div>\n";
echo "</form>\n";
?>