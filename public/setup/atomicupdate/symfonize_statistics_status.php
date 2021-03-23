<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/statut.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET  `url`='/statistics/status' WHERE `url`='statistiques/statut.php';";