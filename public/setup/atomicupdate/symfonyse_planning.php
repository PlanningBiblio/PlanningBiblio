<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/poste/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/index' WHERE `url`='planning/poste/index.php';";

$db = new db();
$db->select('pl_poste_tab');
if ($db->result) {
    foreach ($db->result as $tab) {
        $id = $tab['id'];
        $old = $tab['nom'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $sql[] = "UPDATE `{$dbprefix}pl_poste_tab` SET `nom` = '$new' WHERE `id` = '$id';";
        }
    }
}
