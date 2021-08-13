<?php

$sql[] = "ALTER TABLE `{$dbprefix}config` ADD `extra` varchar(100) AFTER `valeurs`;";
$sql[] = "UPDATE `{$dbprefix}config` SET `extra` = 'onchange=\'mail_config();\'' WHERE `nom` = 'Mail-IsMail-IsSMTP';";
$sql[] = "DELETE FROM `{$dbprefix}config` WHERE `nom` = 'Mail-WordWrap';";
