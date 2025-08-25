<?php

$sql[] = "ALTER TABLE `{$dbprefix}absences_recurrentes` CHANGE `end` `end` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `supprime` `supprime` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `absent` `absent` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `supprime` `supprime` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `grise` `grise` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_lignes` CHANGE `type` `type` VARCHAR(6) NOT NULL DEFAULT 'poste';";

$sql[] = "UPDATE `{$dbprefix}absences_recurrentes` SET `end` = 0 WHERE `end` = 1;";
$sql[] = "UPDATE `{$dbprefix}absences_recurrentes` SET `end` = 1 WHERE `end` > 1;";

$sql[] = "UPDATE `{$dbprefix}personnel` SET `supprime` = 0 WHERE `supprime` = 1;";
$sql[] = "UPDATE `{$dbprefix}personnel` SET `supprime` = 1 WHERE `supprime` = 2;";
$sql[] = "UPDATE `{$dbprefix}personnel` SET `supprime` = 2 WHERE `supprime` > 2;";

$sql[] = "UPDATE `{$dbprefix}pl_poste` SET `absent` = 0 WHERE `absent` = 1;";
$sql[] = "UPDATE `{$dbprefix}pl_poste` SET `absent` = 1 WHERE `absent` = 2;";
$sql[] = "UPDATE `{$dbprefix}pl_poste` SET `absent` = 2 WHERE `absent` > 2;";

$sql[] = "UPDATE `{$dbprefix}pl_poste` SET `supprime` = 0 WHERE `supprime` = 1;";
$sql[] = "UPDATE `{$dbprefix}pl_poste` SET `supprime` = 1 WHERE `supprime` > 1;";

$sql[] = "UPDATE `{$dbprefix}pl_poste` SET `grise` = 0 WHERE `grise` = 1;";
$sql[] = "UPDATE `{$dbprefix}pl_poste` SET `grise` = 1 WHERE `grise` > 1;";
