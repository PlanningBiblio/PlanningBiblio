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
            if ($cli) {
                echo "\e[1m Étage non identifié: \"$floor_name\" : \033[31m[KO]\e[0m\n";
            } else {
                echo "Étage non identifié: \"$floor_name\" : <font style='color:red;'>Erreur</font><br/>\n";
            }
        } elseif ($floor_name) {
            $floor_id = $floors[$elem['etage']];
            $sql[] = "UPDATE `{$dbprefix}postes` SET `etage` = '$floor_id' WHERE `id` = $id;";
        }


        if ($group_name && !isset($groups[$group_name])) {
            if ($cli) {
                echo "\e[1m Groupe non identifié: \"$group_name\" : \033[31m[KO]\e[0m\n";
            } else {
                echo "Groupe non identifié: \"$group_name\" : <font style='color:red;'>Erreur</font><br/>\n";
            }
        } elseif ($group_name) {
            $group_id = $groups[$elem['groupe']];
            $sql[] = "UPDATE `{$dbprefix}postes` SET `groupe` = '$group_id' WHERE `id` = $id;";
        }
    }
}