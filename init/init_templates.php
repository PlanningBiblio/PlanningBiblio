<?php


// TEST
$version = '19.04';
$theme = 'light_blue';
$show_menu = true;
$msg = null;
$msgType = null;
$msg2 = null;
$msg2Type = null;
$content_planning = null;
$authorized = true;
$CSRFSession = 'test';

$templates_params = array(
    'version'             => $version,
    'theme'               => $theme,
    'msg'                 => $msg, //$request->get('msg'),
    'msgType'             => $msgType, //$request->get('msgType'),
    'msg2'                => $msg2, //$request->get('msg2'),
    'msg2Type'            => $msg2Type, //$request->get('msg2Type'),
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
