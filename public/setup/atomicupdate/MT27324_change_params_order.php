<?php
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '70' WHERE `nom` = 'Absences-notifications-A4';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '80' WHERE `nom` = 'Absences-notifications-B1';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '90' WHERE `nom` = 'Absences-notifications-B2';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '100' WHERE `nom` = 'Absences-notifications-B3';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '110' WHERE `nom` = 'Absences-notifications-B4';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '120' WHERE `nom` = 'Absences-notifications-agent-par-agent';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '130' WHERE `nom` = 'Absences-notifications-titre';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '140' WHERE `nom` = 'Absences-notifications-message';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '150' WHERE `nom` = 'Absences-DelaiSuppressionDocuments';";
$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = '365' WHERE `nom` = 'Absences-DelaiSuppressionDocuments';";