<?php

$templates_params = array(
    'version'             => $version,
    'themeJQuery'         => $themeJQuery,
    'theme'               => $theme,
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
