<?php

$sql[] = "ALTER TABLE `{$dbprefix}postes` ADD COLUMN `teleworking` ENUM('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' AFTER `statistiques`;";
