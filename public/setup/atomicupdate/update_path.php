 <?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '' WHERE `page`='statistiques/temps.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/statistics/time' WHERE `url`='statistiques/temps.php';";

?>