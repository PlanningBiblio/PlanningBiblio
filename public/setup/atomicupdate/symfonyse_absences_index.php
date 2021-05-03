<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='absences/voir.php';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='absences/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/absence' WHERE `url`='absences/voir.php';";