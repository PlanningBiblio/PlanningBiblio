<?php
/*
  * Planning Biblio, Version 2.7.11
  * Licence GNU/GPL (version 2 et au dela)
  * Voir les fichiers README.md et LICENSE
  * @copyright 2011-2018 Jérôme Combes

  * Fichier : init_menu.php
  * Création : 25 mai 2018
  * Dernière modification : 24 mai 2018
  * @author Alex Arnaud <alex.arnaud@biblibre.com>

  * Description :
  * Initialisation du contenu du menu
*/

require_once('include/class.menu.php');

$m = new menu();
$m->fetch();
$elements = $m->elements;

$menu_js = "zlien = new Array;\n";
$menu_entries = '';
$keys = array_keys($elements);
sort($keys);

foreach($keys as $key){
    $menu_entries .= "<li onmousemove='pop(zlien[$key],$(this))' class='menu_li'>";
    $menu_entries .= "<a href='index.php?page=" . $elements[$key][0]['url'] . "'";
    $menu_entries .= "class='ejsmenu2'>" . $elements[$key][0]['titre'] . "</a></li>\n";

    $menu_js .= "zlien[$key] = new Array;\n";
    $keys2 = array_keys($elements[$key]);
    sort($keys2);
    unset($keys2[0]);
    $i=0;
    foreach($keys2 as $key2){
        $menu_js .= "zlien[$key][$i] = ";
        $menu_js .= "\"<a href='index.php?page=" . $elements[$key][$key2]['url'];
        $menu_js .= "' class='ejsmenu'>" . $elements[$key][$key2]['titre'] . "<\/a>\";\n";
        $i++;
    }
}
