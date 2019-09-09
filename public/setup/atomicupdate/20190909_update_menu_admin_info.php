<?php
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/admin/info' WHERE `url` = 'infos/index.php';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/admin/info' WHERE `page` = 'infos/index.php';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/admin/info/add' WHERE `page` = 'infos/ajout.php';";

$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'infos/modif.php';";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'infos/supprime.php';";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'infos/ajout.php';";
