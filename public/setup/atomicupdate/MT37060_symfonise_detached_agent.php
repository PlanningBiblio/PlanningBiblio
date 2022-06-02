<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/volants/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/detached' WHERE `url`='planning/volants/index.php';";