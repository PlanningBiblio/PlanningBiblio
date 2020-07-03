<?php
// Remove HTML entities from pl_poste_tab

$db = new db();
$db->select2('pl_poste_tab', array('id', 'nom'), "`nom` LIKE '%&%'");

if ($db->result) {
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['nom'];

        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}pl_poste_tab` SET `nom` = '$new' WHERE `id` = '$id';";
        }
    }
}