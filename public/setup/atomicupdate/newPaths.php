<?php

$sql[]="DELETE from `{$dbprefix}acces` WHERE `page` = 'planning/postes_cfg/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/framework' WHERE `url` = 'planning/postes_cfg/index.php';";