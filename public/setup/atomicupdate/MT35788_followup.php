<?php


$db = new db();
$db->select2('planning_hebdo');
if($db->result){
    foreach ($db->result as $workinghours) {
        $hours = json_decode(html_entity_decode(
            $workinghours['temps'],
            ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);

        foreach ($hours as $day => $times) {
            foreach ($times as $i => $time) {
                if ($time == '00:00:00') {
                    $hours[$day][$i] = '';
                }
            }
        }
        $hours = json_encode($hours);
        $id = $workinghours['id'];
        $sql[] = "UPDATE `{$dbprefix}planning_hebdo` SET `temps` = '$hours' WHERE `id` = $id;";
    }
}

$db = new db();
$db->select2('personnel');
if($db->result){
    foreach ($db->result as $agent) {
        $hours = json_decode(html_entity_decode(
            $agent['temps'],
            ENT_QUOTES|ENT_IGNORE, 'UTF-8'), true);

        if (!$hours) {
            continue;
        }

        foreach ($hours as $day => $times) {
            foreach ($times as $i => $time) {
                if ($time == '00:00:00') {
                    $hours[$day][$i] = '';
                }
            }
        }
        $hours = json_encode($hours);
        $id = $agent['id'];
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `temps` = '$hours' WHERE `id` = $id;";
    }
}