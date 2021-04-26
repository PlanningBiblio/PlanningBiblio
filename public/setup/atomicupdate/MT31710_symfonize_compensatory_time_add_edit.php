<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='conges/recup_pose.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/comp-time/new' WHERE `url`='conges/recup_pose.php';";


