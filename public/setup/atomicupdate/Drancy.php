<?php

$sql[] = "ALTER TABLE `{$dbprefix}personnel` ADD COLUMN IF NOT EXISTS `check_ms_graph` TINYINT(1) NOT NULL DEFAULT 0 AFTER `check_hamac`;";
