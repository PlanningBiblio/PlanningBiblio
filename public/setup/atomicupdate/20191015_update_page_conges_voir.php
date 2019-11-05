<?php
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/holiday/index' WHERE `url` = 'conges/voir.php';";
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/holiday/index?recup=1' WHERE `url` = 'conges/voir.php&amp;recup=1';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/holiday/index' WHERE `page` = 'conges/voir.php';";