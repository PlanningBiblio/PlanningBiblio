<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/absence' WHERE `nom`='Absences - Index';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`= 'absences/voir.php';";
$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/absence/add' WHERE `nom` = 'Absences - Ajouter';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/absence' WHERE `url` = 'absences/voir.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/absence/nom' WHERE `titre` = 'Ajouter une absence';";



?>
