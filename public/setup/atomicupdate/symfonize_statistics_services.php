<?php
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page` = 'statistiques/service.php'";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/statistics/service' WHERE `url` = 'statistiques/service.php' ;";

