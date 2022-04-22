<?php

$db = new db();
$db->query("SELECT `valeur` FROM `{$dbprefix}config` WHERE `nom` = 'PlanningHebdo';");

if ( $db->result and $db->result[0]['valeur'] == '1' ) {
    $sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '0' WHERE `nom` = 'EDTSamedi';";
}

$sql[]="UPDATE `{$dbprefix}config` SET `commentaires` = 'Horaires différents les semaines avec samedi travaillé et semaines à ouverture restreinte. Ce paramètre est ignoré si PlanningHebdo est activé.' WHERE `nom`='EDTSamedi';";
