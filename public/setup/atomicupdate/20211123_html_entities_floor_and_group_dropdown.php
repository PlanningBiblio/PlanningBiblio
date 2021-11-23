<?php

$db = new db();
$db->select2('select_etages');
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];

        $sql[] = "UPDATE `{$dbprefix}postes` SET `etage` = '$id' WHERE `etage` = '$old';";

        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}postes` SET `etage` = '$id' WHERE `etage` = '$new';";
            $sql[] = "UPDATE `{$dbprefix}select_etages` SET `valeur` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('select_groupes');
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];

        $sql[] = "UPDATE `{$dbprefix}postes` SET `groupe` = '$id' WHERE `groupe` = '$old';";

        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}postes` SET `groupe` = '$id' WHERE `groupe` = '$new';";
            $sql[] = "UPDATE `{$dbprefix}select_groupes` SET `valeur` = '$new' WHERE `id` = '$id';";
        }
    }
}