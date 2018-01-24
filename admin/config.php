<?php
/**
Planning Biblio, Version 2.7.12
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : admin/config.php
Création : mai 2011
Dernière modification : 24 janvier 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche et modifie les paramètres de configuration (Serveur Mail, autres options) : Formulaire et validation

Page appelée par la page index.php
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if(__FILE__ == $_SERVER['SCRIPT_FILENAME']){
  include_once "../include/accessDenied.php";
  exit;
}

// Dossier temporaire
$tmp_dir=sys_get_temp_dir();

$url = createURL();

// Enregistrement des paramètres
if($_POST){

  // Initilisation des variables
  $post=array();
  foreach($_POST as $key => $value){
    $key=filter_var($key,FILTER_SANITIZE_STRING);
    if(is_array($value)){
      foreach($value as $v2){
	$post[$key][]=filter_var($v2,FILTER_SANITIZE_STRING);
      }
    }else{
      $post[$key]=filter_var($value,FILTER_SANITIZE_STRING);
    }
  }

  $CSRFToken = $post['CSRFToken'];
  unset($post['CSRFToken']);
  
  // Si les checkboxes ne sont pas cochées, elles ne sont pas transmises donc pas réinitialisées. Donc on les réinitialise ici.
  $db=new db();
  $db->select2("config","nom",array("type"=>"checkboxes"));
  if($db->result){
    foreach($db->result as $elem){
      if(!array_key_exists($elem['nom'],$post)){
	$post[$elem['nom']]=array();
      }
    }
  }

  $post['URL'] = $url;
  
  $erreur=false;
  $db=new dbh();
  $db->CSRFToken = $CSRFToken;
  $db->prepare("UPDATE `{$dbprefix}config` SET `valeur`=:valeur WHERE `nom`=:nom");
  foreach($post as $key => $value){
    if(!in_array($key,array("page","Valider","Annuler"))){
      $value=str_replace("'","&apos;",$value);
      if(substr($key,-9)=="-Password"){
	$value=encrypt($value);
      }
      
      // Checkboxes
      if(is_array($value)){
	$value=json_encode($value);
      }

      $db->execute(array(":nom"=>$key,":valeur"=>$value));
    }
  }

  if($erreur){
    echo <<<EOD
      <script type='text/JavaScript'>
      CJInfo('Il y a eu des erreurs pendant la modification.<br/>Veuillez vérifier la configuration.','error');
      </script>
EOD;
  }
  else{
    echo <<<EOD
      <script type='text/JavaScript'>
      CJInfo('Les modifications ont été enregistrées.','highlight');
      </script>
EOD;
  }
}


// Affichage des paramètres
$last_category=null;
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}config` ORDER BY `categorie`,`ordre`,`id`;");

echo "<h3>Configuration</h3>\n";
echo "<form name='form' action='index.php' method='post'>\n";
echo "<input type='hidden' name='page' value='admin/config.php' />\n";
echo "<input type='hidden' name='CSRFToken' value='$CSRFSession' />\n";
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
      echo "<select name='{$elem['nom']}' id='{$elem['nom']}' style='width:305px;'>\n";
      echo "<option value='0'>0</option>\n";
      echo "<option value='1' $selected>1</option>\n";
      echo "</select>\n";
      break;

    // Checkboxes
    // Valeurs proposées (champ valeurs) = tableau PHP à 2 dimensions
    // Valeurs choisies (champ valeur) =  tableau PHP à 1 dimension
    case "checkboxes" :
      $valeurs=json_decode(str_replace("&#34;",'"',$elem['valeurs']),true);
      $choisies=json_decode(html_entity_decode($elem['valeur'],ENT_QUOTES|ENT_IGNORE,'UTF-8'),true);

      if(is_array($valeurs)){
	foreach($valeurs as $val){
	  $checked=in_array($val[0],$choisies)?"checked='checked'":null;
	  echo "<input type='checkbox' name='{$elem['nom']}[]' id='{$elem['nom']}[]' value='{$val[0]}' $checked /> {$val[1]}<br/>\n";
	}
      }
      break;

    // Select avec valeurs séparées par des virgules
    case "enum" :
      echo "<select name='{$elem['nom']}' id='{$elem['nom']}' style='width:305px;'>\n";
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
      echo "<select name='{$elem['nom']}' id='{$elem['nom']}' style='width:305px;'>\n";
      $options=json_decode(str_replace("&#34;",'"',$elem['valeurs']),true);
      foreach($options as $option){
	$selected=$option[0]==$elem['valeur']?"selected='selected'":null;
	echo "<option value='{$option[0]}' $selected >{$option[1]}</option>\n";
      }
      echo "</select>\n";
      break;

    case "password" :
      echo "<input type='password' name='{$elem['nom']}' id='{$elem['nom']}' value='{$elem['valeur']}' style='width:300px;'/>\n";
      break;

    case "info" :
      echo $elem['valeur'];
      break;                		

    case "textarea" :
      $valeur=str_replace("<br/>","\n",$elem['valeur']);
      echo "<textarea name='{$elem['nom']}' id='{$elem['nom']}' style='width:300px;height:100px;' rows='1' cols='1'>$valeur</textarea>\n";
      break;

    case "date" :
      echo "<input type='text' name='{$elem['nom']}' id='{$elem['nom']}' value='".dateFr3($elem['valeur'])."' style='width:300px;' class='datepicker'/>\n";
      break;

    default :
      echo "<input type='text' name='{$elem['nom']}' id='{$elem['nom']}' value='{$elem['valeur']}' style='width:300px;'/>\n";
      break;
  }

  $commentaires=str_replace("[TEMP]",$tmp_dir,$elem['commentaires']);
  $commentaires=str_replace("[SERVER]",$url,$commentaires);
  echo "</td><td>$commentaires</td>\n";
  echo "</tr>\n";
  
  if($elem['nom'] == 'LDAP-ID-Attribute'){
    echo "<tr><td>Tester</td>\n";
    echo "<td><input type='button' value='Tester' onclick='ldaptest();' id='LDAP-Test' /></td>\n";
    echo "<td>Tester les paramètres LDAP</td></tr>\n";
  }

  if($elem['nom'] == 'Mail-Planning'){
    echo "<tr><td>Tester</td>\n";
    echo "<td><input type='button' value='Tester' onclick='mailtest();' id='Mail-Test' /></td>\n";
    echo "<td>Tester les paramètres de messagerie. Un e-mail sera envoy&eacute; aux adresses mention&eacute;es dans le champ Mail-Planning.</td></tr>\n";
  }

  
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