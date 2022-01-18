<?php

// MT 35062. New validation schema.
$sql[] = "ALTER TABLE `{$dbprefix}responsables` CHANGE `notification` `notification_level1` INT(1) NOT NULL DEFAULT '0'";
$sql[] = "ALTER TABLE `{$dbprefix}responsables` ADD COLUMN `notification_level2` INT(1) NOT NULL DEFAULT '0' AFTER `notification_level1`";

$sql[] = "ALTER TABLE `{$dbprefix}responsables` ADD COLUMN `level1` INT(1) NOT NULL DEFAULT '1' AFTER `responsable`";
$sql[] = "ALTER TABLE `{$dbprefix}responsables` ADD COLUMN `level2` INT(1) NOT NULL DEFAULT '0' AFTER `level1`";

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Absences-Validation-N2', 'enum2', '0', '[[0,\"Validation directe autoris&eacute;e\"],[1,\"L\'absence doit &ecirc;tre valid&eacute; au niveau 1\"]]', 'Absences', 'La validation niveau 2 des absences peut se faire directement ou doit attendre la validation niveau 1', '31')";

$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'conges/recuperation_modif.php';";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'conges/recuperation_valide.php';";
