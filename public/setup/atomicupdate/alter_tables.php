<?php

$do_after_services = true;

$personnel = array();

$db = new db();
$db->select2('personnel', array('id', 'nom', 'prenom', 'service'));


if($db->result) {
    foreach($db->result as $elem) {
        $personnel[$elem['id']] = array("id" => $elem['id'], "nom" => $elem['nom'], "prenom" => $elem['prenom'], "service" => $elem['service']);
    }
}

$db = new db();
$db->select2("select_services",array("id","valeur"), null,null);
$services = $db->result;
if($services){
    foreach($services as $elem){
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `service`= '{$elem['id']}'  WHERE `service` = '{$elem['valeur']}';";
    }
}


$sql[] = "ALTER TABLE `{$dbprefix}personnel` MODIFY `service` int(11) NOT NULL DEFAULT 0;";

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

$after[] = function() {
    global $do_after_services, $personnel;
    $cpt = 0;
    if(isset($do_after_services)){
        $db = new db();
        $db->select2('personnel', array('id', 'nom', 'prenom', 'service'), array("service"=> "= 0"));

        if ($db->result){
            foreach($db->result as $agent) {
                $id = $agent['id'];
                if ($personnel[$id]['service'] != ''){
                    $cpt++;
                }
            }
            if ($cpt > 0){
                echo  "Le service des agents suivants doit être vérifié :<br>\n";
                foreach($db->result as $agent) {
                    $id = $agent['id'];
                    if (empty($personnel[$id]['service'])) {
                        continue;
                    }
                    echo"Agent N° {$id} : {$personnel[$id]['prenom']} {$personnel[$id]['nom']}. Service d'origine = \"{$personnel[$id]['service']}\"  \n";
                }
            }
        }
    }
};
