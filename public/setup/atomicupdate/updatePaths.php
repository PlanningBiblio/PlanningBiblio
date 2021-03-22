<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/samedis.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET  `url`='/statistics/saturday' WHERE `url`='statistiques/samedis.php';";