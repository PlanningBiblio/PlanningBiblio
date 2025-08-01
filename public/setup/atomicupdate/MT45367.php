<?php

$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `chgt_time` `chgt_time` DATETIME NULL DEFAULT NULL;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `statistiques` `statistiques` TINYINT(1) NULL DEFAULT 1;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `teleworking` `teleworking` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `bloquant` `bloquant` TINYINT(1) NULL DEFAULT 1;";

$sql[] = "UPDATE `{$dbprefix}postes` SET `statistiques` = 1 WHERE `statistiques` > 0;";
$sql[] = "UPDATE `{$dbprefix}postes` SET `teleworking` = 1 WHERE `teleworking` > 0;";
$sql[] = "UPDATE `{$dbprefix}postes` SET `quota_sp` = 1 WHERE `quota_sp` > 0;";
$sql[] = "UPDATE `{$dbprefix}postes` SET `lunch` = 1 WHERE `lunch` > 0;";
