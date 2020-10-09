<?php

$db = new db();
$db->select2('jours_feries', array('id', 'nom'), "`nom` LIKE '%&%'");

if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['nom'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}jours_feries` SET `nom` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('jours_feries', array('id', 'commentaire'), "`commentaire` LIKE '%&%'");

if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['commentaire'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}jours_feries` SET `commentaire` = '$new' WHERE `id` = '$id';";
        }
    }
}
