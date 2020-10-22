<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/framework' WHERE `page` = 'planning/postes_cfg/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/framework' WHERE `url` = 'planning/postes_cfg/index.php';";