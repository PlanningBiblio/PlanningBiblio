<?php

$db = new db();
$db->query("SELECT * FROM `{$dbprefix}select_statuts`;");

if ($db->result) {
    foreach ($db->result as $elem) {
        $elem['valeur'] = addslashes($elem['valeur']);
        $sql[] = "UPDATE `{$dbprefix}personnel` set `statut` = '{$elem['id']}' WHERE `statut` = '{$elem['valeur']}';";
    }
}