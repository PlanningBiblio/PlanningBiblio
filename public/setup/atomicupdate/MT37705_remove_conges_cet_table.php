<?php

$sql[] = "DROP TABLE IF EXISTS `{$dbprefix}conges_cet`;";
$sql[] = "DELETE FROM {$dbprefix}acces WHERE page = 'conges/cet.php';";
