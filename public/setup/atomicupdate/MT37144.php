<?php

$sql[] = "ALTER TABLE `{$dbprefix}postes` ADD COLUMN IF NOT EXISTS `lunch` TINYINT(1) NOT NULL DEFAULT 0 AFTER `bloquant`;";

if (!empty($config['Position-Lunch']) and is_array($config['Position-Lunch'])) {
    foreach ($config['Position-Lunch'] as $elem) {
        $sql[] = "UPDATE `{$dbprefix}postes` SET `lunch` = '1' WHERE `id` = '$elem';";
    }
}
