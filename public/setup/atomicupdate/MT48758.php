<?php

$sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN IF NOT EXISTS `calculation` TEXT NOT NULL DEFAULT '' AFTER `heures`;";
