<?php
/**
Planning Biblio, Version 2.8.1
Licence GNU/GPL (version 2 et au dela)
Voir les fichiers README.md et LICENSE
@copyright 2011-2018 Jérôme Combes

Fichier : admin/config.php
Création : mai 2011
Dernière modification : 27 septembre 2018
@author Jérôme Combes <jerome@planningbiblio.fr>

Description :
Affiche et modifie les paramètres de configuration (Serveur Mail, autres options) : Formulaire et validation

Page appelée par la page index.php
*/

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once "../include/accessDenied.php";
    exit;
}

// Dossier temporaire
$tmp_dir=sys_get_temp_dir();

$url = createURL();
$templates_params['CSRFSession'] = $CSRFSession;

// Enregistrement des paramètres
if ($_POST) {

    $templates_params['post'] = 1;
    // Initilisation des variables
    $post=array();
    foreach ($_POST as $key => $value) {
        $key=filter_var($key, FILTER_SANITIZE_STRING);
        if (is_array($value)) {
            foreach ($value as $v2) {
                $post[$key][]=filter_var($v2, FILTER_SANITIZE_STRING);
            }
        } else {
            $post[$key]=filter_var($value, FILTER_SANITIZE_STRING);
        }
    }

    $CSRFToken = $post['CSRFToken'];
    unset($post['CSRFToken']);
  
    // Si les checkboxes ne sont pas cochées, elles ne sont pas transmises donc pas réinitialisées. Donc on les réinitialise ici.
    $db=new db();
    $db->select2("config", array('nom', 'type'), array("type"=>"IN boolean,checkboxes"));
    if ($db->result) {
        foreach ($db->result as $elem) {
            if (!array_key_exists($elem['nom'], $post)) {
                if ($elem['type'] == 'boolean') {
                    $post[$elem['nom']] = '0';
                } else {
                    $post[$elem['nom']] = array();
                }
            }
        }
    }

    $post['URL'] = $url;
  
    $erreur=false;
    $db=new dbh();
    $db->CSRFToken = $CSRFToken;
    $db->prepare("UPDATE `{$dbprefix}config` SET `valeur`=:valeur WHERE `nom`=:nom");
    foreach ($post as $key => $value) {
        if (!in_array($key, array("page","Valider","Annuler"))) {
            $value=str_replace("'", "&apos;", $value);
            if (substr($key, -9)=="-Password") {
                $value=encrypt($value);
            }
      
            // Checkboxes
            if (is_array($value)) {
                $value=json_encode($value);
            }

            $db->execute(array(":nom"=>$key,":valeur"=>$value));
        }
    }

    $templates_params['erreur'] = $erreur;
}


// Affichage des paramètres
$last_category=null;
$db=new db();
$db->query("SELECT * FROM `{$dbprefix}config` ORDER BY `categorie`,`ordre`,`id`;");

$elements = array();
foreach ($db->result as $elem) {
    if (substr($elem['nom'], -9)=="-Password") {
        $elem['valeur']=decrypt($elem['valeur']);
    }
    $elem['valeurs'] = html_entity_decode($elem['valeurs'], ENT_HTML5|ENT_QUOTES, 'UTF-8');

    switch ($elem['type']) {
    case "checkboxes":
      $elem['valeurs'] = json_decode(str_replace("&#34;", '"', $elem['valeurs']), true);
      $elem['choisies'] = json_decode(html_entity_decode($elem['valeur'], ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);
      break;

    // Select avec valeurs séparées par des virgules
    case "enum":
      $options=explode(",", $elem['valeurs']);
      $selected = null;
      foreach ($options as $option) {
          $selected = $option == htmlentities($elem['valeur'], ENT_QUOTES|ENT_IGNORE, "UTF-8", false) ? $elem['valeur'] : $selected;
      }
      $elem['valeur'] = $selected;
      $elem['options'] = $options;
      break;

    // Select avec valeurs dans un tableau PHP à 2 dimensions
    case "enum2":
      $elem['options'] = json_decode(str_replace("&#34;", '"', $elem['valeurs']), true);
      break;

    case "textarea":
      $elem['valeur'] = str_replace("<br/>", "\n", $elem['valeur']);
      break;

    case "date":
      $elem['valeur'] = dateFr3($elem['valeur']);
      break;

    default:
      break;
    }

    $commentaires = str_replace("[TEMP]", $tmp_dir, $elem['commentaires']);
    $commentaires = str_replace("[SERVER]", $url, $commentaires);
    $elem['commentaires'] = html_entity_decode($commentaires, ENT_HTML5|ENT_QUOTES, 'UTF-8');

    $category = str_replace(' ', '', $elem['categorie']);

    # Transform bad encoded category name.
    if ($category == 'Cong&eacute;s') {
        $category = 'conges';
    }
    if ($category == 'Heuresdepr&eacute;sence') {
        $category = 'heurespresence';
    }

    $elements[$category][] = $elem;
}
$templates_params['elements'] = $elements;

$template = $twig->load('admin/config.html.twig');
echo $template->render($templates_params);
