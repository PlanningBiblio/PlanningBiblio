<?php

$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page`='conges/infos.php';";
$sql[] = "UPDATE `{$dbprefix}menu` SET `url`='/holiday-info' WHERE `url`='conges/infos.php';"; 
