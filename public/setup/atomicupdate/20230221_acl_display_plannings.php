<?php

$sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Poste', 1501, 'Consultation des plannings', '', 'Planning', 105);";

// Get sites config
$db = new db();
$db->select2('config', ['valeur'], ['nom' => 'Multisites-nombre']);
$sites = $db->result[0]['valeur'];

// Update ACLs
$db = new db();
$db->select2('personnel', ['id', 'droits']);

if ($db->result) {
    foreach ($db->result as $elem) {
        $id = $elem['id'];
        $acl = json_decode($elem['droits']);

        for($i=1; $i<=$sites; $i++) {
            $acl[] = (1500 + $i);
        }

        $acl = json_encode($acl);

        $sql[] = "UPDATE `{$dbprefix}personnel` SET `droits` = '$acl' WHERE `id` = $id;";
    }
}

$sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `matricule` `matricule` VARCHAR(100) NULL DEFAULT NULL;";
