<?php
/*
 * Planning Biblio, Version 2.7.11
 * Licence GNU/GPL (version 2 et au dela)
 * Voir les fichiers README.md et LICENSE
 * @copyright 2011-2018 Jérôme Combes

 * Fichier : init_templates.php
 * Création : mai 2018
 * Dernière modification : 24 mai 201_
 * @author Alex Arnaud <alex.arnaud@biblibre.com>

 * Description :
 *   Chargement de twig et initialisation
 *   des variables de template,
 */

$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig = new Twig_Environment($loader);

$templates_params = array(
    'version'             => $version,
    'displayed_version'   => $displayed_version,
    'themeJQuery'         => $themeJQuery,
    'theme'               => $theme,
    'favicon'             => $favicon,
    'msg'                 => $request->get('msg'),
    'msgType'             => $request->get('msgType'),
    'msg2'                => $request->get('msg2'),
    'msg2Type'            => $request->get('msg2Type'),
    'show_menu'           => $show_menu ? 1 : 0,
    'menu_js'             => $menu_js,
    'menu_entries'        => $menu_entries,
    'user_surname'        => $_SESSION['login_nom'],
    'user_firstname'      => $_SESSION['login_prenom'],
    'planninghebdo'       => $config['PlanningHebdo'],
    'ics_export'          => $config['ICS-Export'],
    'oups_auth_mode'      => $_SESSION['oups']['Auth-Mode'],
    'content_planning'    => $content_planning,
    'authorized'          => $authorized,
    'CSRFSession'         => $CSRFSession,
);
