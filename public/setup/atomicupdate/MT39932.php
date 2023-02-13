<?php


// MT39932 - remove HTML entities from frameworks title
$db = new db();
$db->select2('pl_poste_lignes');

if ($db->result) {
    foreach ($db->result as $elem) {
        $old = $elem['poste'];
        $new = html_entity_decode($elem['poste'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}pl_poste_lignes` SET `poste` = '$new' WHERE `id` = '{$elem['id']}';";
        }
    }
}
