<?php
$db = new db();
$db->select2('personnel', array('id', 'statut'), "`statut` LIKE '%&%'");

if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['statut'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}personnel` SET `statut` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db->select2('personnel', array('id', 'service'), "`service` LIKE '%&%'");

if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['service'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}personnel` SET `service` = '$new' WHERE `id` = '$id';";
        }
    }
}