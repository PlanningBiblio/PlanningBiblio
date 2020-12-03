<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/absence' WHERE `page`='absences/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/absence' WHERE `url` = 'absences/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`= '/absence' WHERE `url` = 'absences/voir.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`= '/absence/add' WHERE `url` = '/absence' AND `titre` = 'Ajouter une absence';";

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/absence_info' WHERE `page`='/absences/info';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/absence_info' WHERE `url` = '/absences/info';";

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page` = 'absences/voir.php';";

?>
