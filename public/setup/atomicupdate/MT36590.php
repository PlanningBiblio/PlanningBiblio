<?php

$db = new db();
$db->query("SELECT `valeur` FROM `{$dbprefix}config` WHERE `nom` = 'PlanningHebdo';");

if ( $db->result and $db->result[0]['valeur'] == '1' ) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '0' WHERE `nom` = 'EDTSamedi';";
}

