<?php
$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/position'  WHERE  `page` = 'postes/index.php' ;";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE  `page` = 'postes/valid.php' ;";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/position'  WHERE  `url` = 'postes/index.php' ;";

$db = new db();
$db->select2('select_etages', array('id', 'valeur'), "`valeur` LIKE '%&%'");
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}select_etages` SET `valeur` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('select_groupes', array('id', 'valeur'), "`valeur` LIKE '%&%'");
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}select_groupes` SET `valeur` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('postes', array('id', 'nom'), "`nom` LIKE '%&%'");
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['nom'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}postes` SET `nom` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('postes', array('id', 'etage'), "`etage` LIKE '%&%'");
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['etage'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}postes` SET `etage` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('postes', array('id', 'groupe'), "`groupe` LIKE '%&%'");
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['groupe'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}postes` SET `groupe` = '$new' WHERE `id` = '$id';";
        }
    }
}

?>
