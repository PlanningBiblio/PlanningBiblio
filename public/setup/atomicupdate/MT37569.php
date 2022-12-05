<?php

$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.planning_hebdo_daily.php', comments = 'Daily Cron for Planning Hebdo module' WHERE `command` = 'planningHebdo/cron.daily.php';";
$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.holiday_reset_remainder.php', comments = 'Reset holliday remainders' WHERE `command` = 'conges/cron.jan1.php';";
$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.holiday_reset_credits.php', comments = 'Reset holliday credits' WHERE `command` = 'conges/cron.sept1.php';";