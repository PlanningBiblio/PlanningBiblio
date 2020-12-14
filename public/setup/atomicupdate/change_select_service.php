<?php

$db = new db();
$db->select("select_services","*", null,null);

if($db->result){
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}select_services` SET `valeur`= '$new'  WHERE `id` = '$id';";
        }
    }
}

/*$sql[] = "ALTER TABLE `{$dbprefix}personnel` ADD `service_tmp` varchar(255);";

$db = new db();
$db->select("personnel","*", null,null);

if($db->result){
    foreach ($db->result as $elem) {
        $service = $elem['service'];
        $id = $elem['id'];
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `service_tmp` = '$service', `service` = null  WHERE `id` = '$id';";
    }
}

$sql[] = "ALTER TABLE `{$dbprefix}personnel` MODIFY `service` int(11) NOT NULL;";
 */

$db = new \db();
$db->selectInnerJoin(array("personnel", "service"), array("select_services", "valeur"), array("id", "service", "service_tmp"), array( array("name"=>"id", "as"=>"service_id"), "valeur"),array("service_tmp"=> '`select_services`.`valeur`'));





