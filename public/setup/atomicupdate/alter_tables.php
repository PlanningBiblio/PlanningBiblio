<?php

$db = new db();
$db->select2("select_services",array("id","valeur"), null,null);
$services = $db->result;
if($services){
    foreach($services as $elem){
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `service`= '{$elem['id']}'  WHERE `service` = '{$elem['valeur']}';";
    }
}


$db = new db();
$db->select("personnel","service",array("service" => "NOT REGEXP '^[0-9]+$'", null));
if(!$db->result){
    $sql[] = "ALTER TABLE `{$dbprefix}personnel` MODIFY `service` int(11) NOT NULL;";

    $db2 = new db();
    $db2->select("select_services","*", null,null);
    if($db2->result){
        foreach ($db2->result as $elem) {
            $id = $elem['id'];
            $old = $elem['valeur'];
            $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
            if ($new != $old) {
                $new = addslashes($new);
                $sql[] = "UPDATE `{$dbprefix}select_services` SET `valeur`= '$new'  WHERE `id` = '$id';";
            }
        }
    }
}
