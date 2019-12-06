<?php
$sql[]="INSERT INTO `{$dbprefix}config` VALUES (null,'Conges-demi-journees','boolean','0','Autorise la saisie de congés en demi-journée. Fonctionne uniquement avec le mode de saisie en jour','Congés','','7');";
$sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN halfday tinyint NULL DEFAULT 0 AFTER fin;";
$sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN start_halfday varchar(20) NULL DEFAULT '' AFTER halfday;";
$sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN end_halfday varchar(20) NULL DEFAULT '' AFTER start_halfday;";