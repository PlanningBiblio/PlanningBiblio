<?php
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/statistics/attendeesmissing' WHERE `url` = 'statistiques/presents_absents.php';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/statistics/attendeesmissing' WHERE `page` = ''statistiques/presents_absents.php';";
