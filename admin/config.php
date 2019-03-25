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

use Model\ConfigParam;

// Contrôle si ce script est appelé directement, dans ce cas, affiche Accès Refusé et quitte
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    include_once "../include/accessDenied.php";
    exit;
}

// Dossier temporaire
$tmp_dir=sys_get_temp_dir();

$url = createURL();
$templates_params['CSRFSession'] = $CSRFSession;


$params = $request->request->all();
$configParams = $entityManager->getRepository(ConfigParam::class)->findBy(
    array(), array('ordre' => 'ASC', 'id' => 'ASC')
);


// Demo mode
if ($params && !empty($config['demo'])) {
    $warning = "La modification de la configuration n'est pas autorisée sur la version de démonstration.";
    $warning .= "#BR#Merci de votre compréhension";
    $templates_params['warning'] = $warning;
}
elseif ($params && CSRFTokenOK($params['CSRFToken'], $_SESSION)) {
    $templates_params['post'] = 1;

    foreach ($configParams as $cp) {

        if ($cp->type() == 'info') {
            continue;
        }

        // boolean and checkboxes elements.
        if (!isset($params[$cp->nom()])) {
            if ($cp->type() == 'boolean') {
                $params[$cp->nom()] = '0';
            } else {
                $params[$cp->nom()] = array();
            }
        }

        $value = $params[$cp->nom()];

        if (substr($cp->nom(), -9)=="-Password") {
            $value = encrypt($value);
        }

        // Checkboxes
        if (is_array($value)) {
            $value = json_encode($value);
        }

        try {
            $cp->valeur($value);
            $entityManager->persist($cp);
        }
        catch (Exception $e) {
            $templates_params['erreur'] = true;
        }
    }
    $entityManager->flush();

}

$elements = array();
foreach ($configParams as $cp) {
    $elem = array(
        'type'          => $cp->type(),
        'nom'           => $cp->nom(),
        'valeur'        => html_entity_decode($cp->valeur(), ENT_QUOTES|ENT_HTML5),
        'valeurs'       => html_entity_decode($cp->valeurs(), ENT_QUOTES|ENT_HTML5),
        'categorie'     => $cp->categorie(),
        'commentaires'  => html_entity_decode($cp->commentaires(), ENT_QUOTES|ENT_HTML5),
    );

    if ($cp->type() == "password") {
        $elem['valeur']=decrypt($elem['valeur']);
    }

    switch ($elem['type']) {
    case "checkboxes":
      $elem['valeurs'] = json_decode($elem['valeurs'], true);
      $elem['choisies'] = json_decode($elem['valeur'], true);
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

    $elem['commentaires'] = str_replace("[TEMP]", $tmp_dir, $elem['commentaires']);
    $elem['commentaires'] = str_replace("[SERVER]", $url, $elem['commentaires']);
    $elem['commentaires'] = html_entity_decode($elem['commentaires'], ENT_QUOTES|ENT_HTML5);

    $category = str_replace(' ', '', $elem['categorie']);

    # Transform bad encoded category name.
    if ($category == 'Cong&eacute;s') {
        $category = 'conges';
    }
    if ($category == 'Heuresdepr&eacute;sence') {
        $category = 'heurespresence';
    }

    $elements[$category][$cp->nom()] = $elem;
}
$templates_params['elements'] = $elements;

$template = $twig->load('admin/config.html.twig');
echo $template->render($templates_params);
