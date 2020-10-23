<?php 

$db = new db();
$db->select2('conges', array('id', 'commentaires'), "`commentaires` LIKE '%&%'");

if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['commentaires'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}conges` SET `commentaires` = '$new' WHERE `id` = '$id';";
        }
    }
}

$db = new db();
$db->select2('menu', array('id','titre'), "`commentaires` LIKE '%&%'");

if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['titre'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}menu` SET `titre` = '$new' WHERE `id` = '$id';";
        }
    }
}

