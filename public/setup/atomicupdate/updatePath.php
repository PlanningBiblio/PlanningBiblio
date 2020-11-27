<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/absence' WHERE `page`='absences/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/absence' WHERE `url` = 'absences/index.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`= '/absence' WHERE `url` = 'absences/voir.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`= '/absence/add' WHERE `url` = '/absence' AND `titre` = 'Ajouter une absence';";

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page` = 'absences/voir.php';";

?>
