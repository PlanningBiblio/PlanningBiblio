<?php

$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/model' WHERE `url` = 'planning/modeles/index.php';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/model' WHERE `page` = 'planning/modeles/index.php';";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'planning/modeles/modif.php';";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'planning/modeles/valid.php';";
