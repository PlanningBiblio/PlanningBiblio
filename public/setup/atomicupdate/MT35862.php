<?php

$db = new db();
$db->query("SELECT `id`, `droits` FROM `{$dbprefix}personnel` WHERE `droits` LIKE 'a%';");

if ($db->result) {
    foreach ($db->result as $elem) {
        $access = json_encode(unserialize($elem['droits']));
        if ($access == 'false') {
            $access = '';
        }
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `droits` = '$access' WHERE `id` = '{$elem['id']}';";
    }
}

$db = new db();
$db->query("SELECT `id`, `postes` FROM `{$dbprefix}personnel` WHERE `postes` LIKE 'a%';");

if ($db->result) {
    foreach ($db->result as $elem) {
        $skill = json_encode(unserialize($elem['postes']));
        if ($skill == 'false') {
            $skill = '';
        }
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `postes` = '$skill' WHERE `id` = '{$elem['id']}';";
    }
}