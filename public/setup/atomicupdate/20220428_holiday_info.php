<?php

$db = new db();
$db->select('conges_infos');
if ($db->result) {
    foreach ($db->result as $elem) {
        $new = html_entity_decode($elem['texte'], ENT_QUOTES|ENT_IGNORE, "UTF-8");
        $new = addslashes($new);
        $sql[] = "UPDATE `{$dbprefix}conges_infos` SET `texte` = '$new' where `id` = '{$elem['id']}';";
    }
}
