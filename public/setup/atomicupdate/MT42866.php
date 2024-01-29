<?php

$sql[] = "ALTER TABLE `{$dbprefix}cron` ADD COLUMN IF NOT EXISTS `disabled` TINYINT(1) NOT NULL DEFAULT '0' AFTER `last`;";
