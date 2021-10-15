<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='conges/credits.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/holiday/accounts' WHERE `url`='conges/credits.php';";