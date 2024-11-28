<?php

$sql[] = "ALTER TABLE `{$dbprefix}absences` ADD COLUMN IF NOT EXISTS `ics_server` TINYINT NULL DEFAULT NULL AFTER `groupe`;";
$sql[] = "ALTER TABLE `{$dbprefix}absences` ADD COLUMN IF NOT EXISTS `imported_at` DATETIME NULL DEFAULT NULL AFTER `ical_key`;";
