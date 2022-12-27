<?php

$sql[] = "ALTER TABLE `{$dbprefix}postes` ADD COLUMN IF NOT EXISTS `lunch` TINYINT(1) NOT NULL DEFAULT 0 AFTER `bloquant`;";
