<?php

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
                $hours[$day][$i] =  App\PlanningBiblio\Helper\HourHelper::toHis($time);
            }
        }
        $hours = json_encode($hours);
        $id = $agent['id'];
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `temps` = '$hours' WHERE `id` = $id;";
    }
}

