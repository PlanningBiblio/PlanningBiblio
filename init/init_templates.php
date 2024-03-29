<?php

use App\PlanningBiblio\Menu;

$m = new Menu();
$menu = $m->get();

$templates_params = array(
    'version'             => $version,
    'themeJQuery'         => $themeJQuery,
    'theme'               => $theme,
    'msg'                 => $request->get('msg'),
    'msgType'             => $request->get('msgType'),
    'msg2'                => $request->get('msg2'),
    'msg2Type'            => $request->get('msg2Type'),
    'show_menu'           => $show_menu ? 1 : 0,
    'menu_js'             => $menu['menu_js'],
    'menu_entries'        => $menu['menu_entries'],
    'user_surname'        => $_SESSION['login_nom'],
    'user_firstname'      => $_SESSION['login_prenom'],
    'planninghebdo'       => $config['PlanningHebdo'],
    'ics_export'          => $config['ICS-Export'],
    'CSRFSession'         => $CSRFSession,
);
