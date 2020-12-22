<?php

$do_after_statuts = true;

$personnel = array();

$db = new db();
$db->select2('personnel', array('id', 'nom', 'prenom', 'statut'), "WHERE `service` <> ''");


if($db->result) {
    foreach($db->result as $elem) {
        $personnel[$elem['id']] = array("id" => $elem['id'], "nom" => $elem['nom'], "prenom" => $elem['prenom'], "statut" => $elem['statut']);
    }
}

$db = new db();
$db->select2("select_statuts",array("id","valeur"), null,null);
$statuts = $db->result;
if($statuts){
    foreach($statuts as $elem){
        $sql[] = "UPDATE `{$dbprefix}personnel` SET `statut`= '{$elem['id']}'  WHERE `statut` = '{$elem['valeur']}';";
    }
}


$sql[] = "ALTER TABLE `{$dbprefix}personnel` MODIFY `statut` int(11) NOT NULL DEFAULT 0;";

$db2 = new db();
$db2->select("select_statuts","*", null,null);
if($db2->result){
    foreach ($db2->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}select_statuts` SET `valeur`= '$new'  WHERE `id` = '$id';";
        }
    }
}

$after[] = function() {
    global $do_after_statuts, $personnel;
    if(isset($do_after_statuts)){
        $db = new db();
        $db->select2('personnel', array('id', 'nom', 'prenom', 'statut'), array("statut"=> "= 0"));

        if ($db->result){
            echo  "Le statut des agents suivants doit être vérifié :<br>\n";
            foreach($db->result as $agent) {
                $id = $agent['id'];
                if (in_array($id, $personnel) and $personnel[$id]['statut'] == ''){
                    echo "Agent N° {$id} : {$personnel[$id]['prenom']} {$personnel[$id]['nom']}. Statut d'origine = \"{$personnel[$id]['statut']}\"  \n";
                }
            }
        }
    }
};
