<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '' WHERE `page`='statistiques/index.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/statistics' WHERE `url`='statistiques/index.php';";
?>