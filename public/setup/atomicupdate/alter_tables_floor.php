<?php

$do_after_etages = true;

$postes = array();

$db = new db();
$db->select2('postes', array('id', 'nom', 'etage'));


if($db->result) {
    foreach($db->result as $elem) {
        $postes[$elem['id']] = array("id" => $elem['id'], "nom" => $elem['nom'], "etage" => $elem['etage']);
    }
}

$db = new db();
$db->select2("select_etages",array("id","valeur"), null,null);
$etages = $db->result;
if($etages){
    foreach($etages as $elem){
        $sql[] = "UPDATE `{$dbprefix}postes` SET `etage`= '{$elem['id']}'  WHERE `etage` = '{$elem['valeur']}';";
    }
}


$sql[] = "ALTER TABLE `{$dbprefix}postes` MODIFY `etage` int(11) NOT NULL DEFAULT 0;";

$db2 = new db();
$db2->select("select_etages","*", null,null);
if($db2->result){
    foreach ($db2->result as $elem) {
        $id = $elem['id'];
        $old = $elem['valeur'];
        $new = html_entity_decode($old, ENT_QUOTES|ENT_IGNORE, 'UTF-8');
        if ($new != $old) {
            $new = addslashes($new);
            $sql[] = "UPDATE `{$dbprefix}select_etages` SET `valeur`= '$new'  WHERE `id` = '$id';";
        }
    }
}

$after[] = function() {
    global $do_after_etages, $postes;
    if(isset($do_after_etages)){
        $db = new db();
        $db->select2('postes', array('id', 'nom', 'etage'), array("etage"=> "= 0"));

        if ($db->result){
            echo  "L'étage des postes suivants doit être vérifié :<br>\n";
            foreach($db->result as $poste) {
                $id = $poste['id'];
                echo"Poste N° {$id} : {$postes[$id]['nom']}. Etage d'origine = \"{$postes[$id]['etage']}\"  \n";
            }
        }
    }
};
