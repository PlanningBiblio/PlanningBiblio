<?php

$db = new db();
$db->select2('select_services', array('id', 'valeur'));
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $new = str_replace(array('"', "'"), ' ', $new);
        if ($new != $old) {
            $sql[] = "UPDATE `{$dbprefix}select_services` SET `valeur` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('select_statuts', array('id', 'valeur'));
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $new = str_replace(array('"', "'"), ' ', $new);
        if ($new != $old) {
            $sql[] = "UPDATE `{$dbprefix}select_statuts` SET `valeur` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('personnel', array('id', 'service', 'statut'));
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $oldservice = $elem['service'];
        $oldstatut = $elem['statut'];
        $newservice = html_entity_decode($oldservice, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $newservice = str_replace(array('"', "'"), ' ', $newservice);
        $newstatut = html_entity_decode($oldstatut, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        $newstatut = str_replace(array('"', "'"), ' ', $newstatut);

        if ($newservice != $oldservice) {
            $sql[] = "UPDATE `{$dbprefix}personnel` SET `service` = '$newservice' WHERE `id` = '$id';";
        }

        if ($newstatut != $oldstatut) {
            $sql[] = "UPDATE `{$dbprefix}personnel` SET `statut` = '$newstatut' WHERE `id` = '$id';";
        }
    }
}

// Remove duplicate.
$sql[] = "DELETE s1 FROM select_services s1 INNER JOIN select_services s2 WHERE s1.id < s2.id AND s1.valeur = s2.valeur";
$sql[] = "DELETE s1 FROM select_statuts s1 INNER JOIN select_statuts s2 WHERE s1.id < s2.id AND s1.valeur = s2.valeur";
