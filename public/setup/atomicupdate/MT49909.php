<?php

$sql[] = "ALTER TABLE `{$dbprefix}absences_recurrentes` CHANGE `end` `end` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}personnel` CHANGE `supprime` `supprime` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `absent` `absent` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `supprime` `supprime` TINYINT(1) NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `grise` `grise` TINYINT(1) NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_lignes` CHANGE `type` `type` VARCHAR(6) NOT NULL DEFAULT '';";
