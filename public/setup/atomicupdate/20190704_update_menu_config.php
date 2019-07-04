<?php
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/config' WHERE `url` = 'admin/config.php';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/config' WHERE `page` = 'admin/config.php';";
