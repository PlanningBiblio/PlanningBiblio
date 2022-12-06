<?php

$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.planning_hebdo_daily.php', comments = 'Daily Cron for Planning Hebdo module' WHERE `command` = 'planningHebdo/cron.daily.php';";
$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.holiday_reset_remainder.php', comments = 'Reset holliday remainders' WHERE `command` = 'conges/cron.jan1.php';";
$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.holiday_reset_credits.php', comments = 'Reset holliday credits' WHERE `command` = 'conges/cron.sept1.php';";

$sql[] = "INSERT IGNORE INTO `{$dbprefix}cron` (`m`, `h`, `dom`, `mon`, `dow`, `command`, `comments`) VALUES ( '0', '0', '1', '9', '*', 'cron.holiday_reset_comp_time.php', 'Reset holliday compensatory time');";

// FIXME Use column_exists function to make this update idempotent when moving it to maj.php
//if (!column_exists("{$dbprefix}cron", 'disabled')) {
    $sql[] = "ALTER TABLE `{$dbprefix}cron` ADD COLUMN `disabled` ENUM('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' AFTER `last`;";
//}