<?php


$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/agent' WHERE `page`='personnel/index.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/agent' WHERE `url`='personnel/index.php';";

?>
