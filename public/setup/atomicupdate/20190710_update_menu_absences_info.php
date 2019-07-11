<?php
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/absences/info' WHERE `url` = 'absences/infos.php';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/absences/info' WHERE `page` = 'absences/infos.php';";
