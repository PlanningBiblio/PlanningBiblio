<?php

$do_after_groupes = true;

$postes = array();

$db = new db();
$db->select2('postes', array('id', 'nom', 'groupe'));


if($db->result) {
    foreach($db->result as $elem) {
        $postes[$elem['id']] = array("id" => $elem['id'], "nom" => $elem['nom'], "groupe" => $elem['groupe']);
    }
}

$db = new db();
$db->select2("select_groupes",array("id","valeur"), null,null);
$groupes = $db->result;
if($groupes){
    foreach($groupes as $elem){
        $sql[] = "UPDATE `{$dbprefix}postes` SET `groupe_id`= '{$elem['id']}'  WHERE `groupe` = '{$elem['valeur']}';";
    }
}


$sql[] = "ALTER TABLE `{$dbprefix}postes` DROP `groupe`";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `groupe_id` `groupe` int(11) NOT NULL DEFAULT 0";

$db2 = new db();
$db2->select("select_groupes","*", null,null);
if($db2->result){
    foreach ($db2->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}select_groupes` SET `valeur`= '$new'  WHERE `id` = '$id';";
        }
    }
}

$after[] = function() {
    global $do_after_groupes, $postes;
    if(isset($do_after_groupes)){
        $db = new db();
        $db->select2('postes', array('id', 'nom', 'groupe'), array("groupe"=> "= 0"));

        if ($db->result){
            echo  "Le groupe des postes suivants doit être vérifié :<br>\n";
            foreach($db->result as $poste) {
                $id = $poste['id'];
                echo"Poste N° {$id} : {$postes[$id]['nom']}. Groupe d'origine = \"{$postes[$id]['groupe']}\"  \n";
            }
        }
    }
};
