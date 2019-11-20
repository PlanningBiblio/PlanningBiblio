<?php

$db = new db();
$db->query("SELECT * FROM `{$dbprefix}select_services`;");

if ($db->result) {
    foreach ($db->result as $elem) {
        $elem['valeur'] = html_entity_decode($elem['valeur'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $elem['valeur'] = addslashes($elem['valeur']);
        $sql[] = "UPDATE `{$dbprefix}personnel` set `service` = '{$elem['id']}' WHERE `service` = '{$elem['valeur']}';";
    }
}