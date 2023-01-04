<?php

$db = new db();
$db->select2('lignes');

if ($db->result) {
    foreach ($db->result as $elem) {
        $old = $elem['nom'];
        $new = html_entity_decode($elem['nom'], ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $sql[] = "UPDATE `{$dbprefix}lignes` SET `nom` = '$new' WHERE `id` = '{$elem['id']}';";
        }
    }
}
