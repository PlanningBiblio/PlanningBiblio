<?php

require_once(__DIR__.'/../public/include/class.menu.php');

$m = new menu();
$m->fetch();
$elements = $m->elements;

$menu_entries = array();
$menu_js = array();

$keys = array_keys($elements);
sort($keys);

foreach ($keys as $key) {
    $menu_entries[] = array(
        'key' => $key,
        'url' => $elements[$key][0]['url'],
        'title' => $elements[$key][0]['titre']
        );

    $menu_js[$key] = array(
        'key' => $key,
        'items' => array()
        );

    $keys2 = array_keys($elements[$key]);
    sort($keys2);
    unset($keys2[0]);

    $i=0;
    foreach ($keys2 as $key2) {
        $menu_js[$key]['items'][$i] = array(
            'key' => $key,
            'url' => $elements[$key][$key2]['url'],
            'title' => $elements[$key][$key2]['titre']
            );
        $i++;
    }
}