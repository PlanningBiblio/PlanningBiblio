<?php

$sql[]="UPDATE `{$dbprefix}acces`SET `page` = '' WHERE `page`='personnel/index.php';";
$sql[]="UPDATE `{$dbprefix}acces` SET `page`= '' WHERE `page`='personnel/valid.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/agent' WHERE `url`='personnel/index.php';";

?>