<?php

$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `chgt_time` `chgt_time` DATETIME NULL DEFAULT NULL;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `bloquant` `bloquant` TINYINT(1) NULL DEFAULT '1';";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `statistiques` `statistiques` TINYINT(1) NULL DEFAULT 1;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `teleworking` `teleworking` TINYINT(1) NOT NULL DEFAULT 0;";

$sql[] = "UPDATE `{$dbprefix}postes` SET `bloquant` = 0 WHERE `bloquant` = 1;";
$sql[] = "UPDATE `{$dbprefix}postes` SET `statistiques` = 0 WHERE `statistiques` = 1;";
$sql[] = "UPDATE `{$dbprefix}postes` SET `teleworking` = 0 WHERE `teleworking` = 1;";

$sql[] = "UPDATE `{$dbprefix}postes` SET `bloquant` = 1 WHERE `bloquant` > 1;";
$sql[] = "UPDATE `{$dbprefix}postes` SET `statistiques` = 1 WHERE `statistiques` > 1;";
$sql[] = "UPDATE `{$dbprefix}postes` SET `teleworking` = 1 WHERE `teleworking` > 1;";

$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `bloquant` `bloquant` TINYINT(1) NOT NULL DEFAULT 1;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `statistiques` `statistiques` TINYINT(1) NOT NULL DEFAULT 1;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `teleworking` `teleworking` TINYINT(1) NOT NULL DEFAULT 0;";
