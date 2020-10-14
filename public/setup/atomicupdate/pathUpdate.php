<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page`='/statistics/status' WHERE `page`='statistiques/statut.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET  `url`='/statistics/status' WHERE `url`='statistiques/statut.php';";