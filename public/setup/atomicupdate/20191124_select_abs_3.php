<?php

$db = new db();
$db->query("SELECT * FROM `{$dbprefix}select_abs`;");

if ($db->result) {
    foreach ($db->result as $elem) {
        $sql[] = "UPDATE `{$dbprefix}absences` set `motif` = '{$elem['id']}' WHERE `motif` = '{$elem['valeur']}';";
    }
}