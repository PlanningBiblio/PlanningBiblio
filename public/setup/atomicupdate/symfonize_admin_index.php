<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='admin/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/admin' WHERE `url`='admin/index.php';";
