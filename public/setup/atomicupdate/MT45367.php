<?php

$sql[] = "ALTER TABLE `{$dbprefix}pl_poste` CHANGE `chgt_time` `chgt_time` DATETIME NULL DEFAULT NULL;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `statistiques` `statistiques` TINYINT(1) NULL DEFAULT 1;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `teleworking` `teleworking` TINYINT(1) NOT NULL DEFAULT 0;";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `bloquant` `bloquant` TINYINT(1) NULL DEFAULT 1;";
