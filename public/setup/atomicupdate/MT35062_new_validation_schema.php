<?php

// MT 35062. New validation schema.
$sql[] = "ALTER TABLE `{$dbprefix}responsables` CHANGE `notification` `notification_level1` INT(1) NOT NULL DEFAULT '0'";
$sql[] = "ALTER TABLE `{$dbprefix}responsables` ADD COLUMN `notification_level2` INT(1) NOT NULL DEFAULT '0' AFTER `notification_level1`";

$sql[] = "ALTER TABLE `{$dbprefix}responsables` ADD COLUMN `level1` INT(1) NOT NULL DEFAULT '1' AFTER `responsable`";
$sql[] = "ALTER TABLE `{$dbprefix}responsables` ADD COLUMN `level2` INT(1) NOT NULL DEFAULT '0' AFTER `level1`";

