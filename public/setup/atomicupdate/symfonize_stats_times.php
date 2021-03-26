 <?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/temps.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/statistics/time' WHERE `url`='statistiques/temps.php';";

?>