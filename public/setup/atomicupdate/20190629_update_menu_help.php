<?php
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/help' WHERE `url` = 'aide/index.php';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/help' WHERE `page` = 'aide/index.php';";
