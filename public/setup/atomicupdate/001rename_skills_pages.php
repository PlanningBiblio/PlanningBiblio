<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page`='/skill' WHERE `page`='activites/index.php';";

$sql[]="UPDATE `{$dbprefix}acces` SET `page`='/skill/add' WHERE `page`='activites/modif.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/skill' where `url`='activites/index.php';";

$db = new db();
$db->select2('activites', array('id', 'nom'), "`nom` LIKE '%&%'");
if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['nom'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}activites` SET `nom` = '$new' WHERE `id` = '$id';";
        }
    }
}
?>