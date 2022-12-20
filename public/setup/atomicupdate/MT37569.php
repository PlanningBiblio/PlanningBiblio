<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '19' WHERE `categorie` = 'Congés' AND `nom` = 'Recup-Uneparjour';";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` ( `nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Conges-transfer-comp-time', 'boolean', '0', 'Transférer les récupérations restantes sur le reliquat', 'Congés', '', NULL, '16');";

$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.planning_hebdo_daily.php', comments = 'Daily Cron for Planning Hebdo module' WHERE `command` = 'planningHebdo/cron.daily.php';";
$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.holiday_reset_remainder.php', comments = 'Reset holliday remainders' WHERE `command` = 'conges/cron.jan1.php';";
$sql[] = "UPDATE `{$dbprefix}cron` SET  `command` = 'cron.holiday_reset_credits.php', comments = 'Reset holliday credits' WHERE `command` = 'conges/cron.sept1.php';";

$sql[] = "INSERT IGNORE INTO `{$dbprefix}cron` (`m`, `h`, `dom`, `mon`, `dow`, `command`, `comments`) VALUES ( '0', '0', '1', '9', '*', 'cron.holiday_reset_comp_time.php', 'Reset holliday compensatory time');";

$sql[] = "ALTER TABLE `{$dbprefix}cron` ADD COLUMN IF NOT EXISTS `disabled` ENUM('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' AFTER `last`;";
