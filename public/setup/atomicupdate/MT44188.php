<?php

$sql[] = "ALTER TABLE `{$dbprefix}personnel` ADD COLUMN IF NOT EXISTS `check_ms_graph` TINYINT(1) NOT NULL DEFAULT 0 AFTER `check_hamac`;";

if (!empty($_ENV['MS_GRAPH_CLIENT_ID'])) {
    $sql[] = "UPDATE `{$dbprefix}personnel` SET `check_ms_graph` = 1 WHERE `supprime` = '0';";
}
