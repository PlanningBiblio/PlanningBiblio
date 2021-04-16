<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='conges/recuperations.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/comp-time' WHERE `url`='conges/recuperations.php';";