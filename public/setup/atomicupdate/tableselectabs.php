$sql[] = "ALTER TABLE `{$dbprefix}select_abs` ADD COLUMN IF NOT EXISTS `absence_cumulee` INT(1) NOT NULL DEFAULT '0' AFTER `teleworking`;";
