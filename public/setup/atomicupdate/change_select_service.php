<?php

$db = new db();
$db->select("select_services","*", null,null);
$services = array();
if($db->result){
    foreach($db->result as $elem){
        $services[$elem['id']] = $elem['valeur'];
    }
}


$db = new db();
$db->select("personnel","*", null,null);
if($db->result){
    foreach($db->result as $elem){
        $agent_id = $elem['id'];
        $service_id = array_search($elem['service_tmp'], $services);
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `service`     = '$service_id'  WHERE `id` = '$agent_id';";

    }
}

$sql[] = "ALTER TABLE `{$dbprefix}personnel` DROP `service_tmp`;";

