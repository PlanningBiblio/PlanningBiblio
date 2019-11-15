<?php
$sql[] = "DROP TABLE `{$dbprefix}plugins`;";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE page='plugins/%';";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE page='planningHebdo/configuration.php';";
