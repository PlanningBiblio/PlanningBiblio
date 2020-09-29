<?php 

$sql[]="UPDATE `{$dbprefix}acces` SET `page`='/statistics' WHERE `page` = 'statistiques/index.php';";

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/statistics.time' WHERE `page`='statistiques/temps.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/statistics' WHERE `url`='statistiques/index.php';";
?>