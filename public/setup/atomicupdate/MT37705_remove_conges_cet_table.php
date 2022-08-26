<?php

$sql[] = "DROP TABLE IF EXISTS `{$dbprefix}conges_cet`;";
$sql[] = "DELETE FROM {$dbprefix}acces WEHERE page = 'conges/cet.php';";