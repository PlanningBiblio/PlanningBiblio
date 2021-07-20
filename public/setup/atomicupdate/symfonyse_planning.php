<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/poste/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/index' WHERE `url`='planning/poste/index.php';";
