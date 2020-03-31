<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `groupe_id` = '9';";
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`categorie`,`ordre`) VALUES (\"Enregistrement d'absences pour plusieurs agents\",'9','Enregistrement d&apos;absences pour plusieurs agents', 'Absences', '25');";