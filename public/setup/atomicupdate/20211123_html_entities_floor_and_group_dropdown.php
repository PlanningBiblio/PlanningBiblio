<?php

$db = new db();
$db->select2('select_etages');
$floors = array();
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $floors[$old] = $id;

        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        if ($new != $old) {
            $new = addslashes($new);
            $floors[$new] = $id;
            $sql[] = "UPDATE `{$dbprefix}select_etages` SET `valeur` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('select_groupes');
$groups = array();
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $groups[$old] = $id;

        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');

        if ($new != $old) {
            $new = addslashes($new);
            $groups[$new] = $id;
            $sql[] = "UPDATE `{$dbprefix}select_groupes` SET `valeur` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('postes');
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $floor_name = $elem['etage'];
        $group_name = $elem['groupe'];

        if ($floor_name && !isset($floors[$floor_name])) {
            // TODO utiliser la version cli ET web lors de l'intégration dans maj.php
            echo "\e[1m Étage non identifié: \"$floor_name\" : \033[31m[KO]\e[0m\n";
        } elseif ($floor_name) {
            $floor_id = $floors[$elem['etage']];
            $sql[] = "UPDATE `{$dbprefix}postes` SET `etage` = '$floor_id' WHERE `etage` = '$floor_name' AND id = $id;";
        }


        if ($group_name && !isset($groups[$group_name])) {
            // TODO utiliser la version cli ET web lors de l'intégration dans maj.php
            echo "\e[1m Groupe non identifié: \"$group_name\" : \033[31m[KO]\e[0m\n";
        } elseif ($group_name) {
            $group_id = $groups[$elem['groupe']];
            $sql[] = "UPDATE `{$dbprefix}postes` SET `groupe` = '$group_id' WHERE `groupe` = '$group_name' AND id = $id;";
        }
    }
}