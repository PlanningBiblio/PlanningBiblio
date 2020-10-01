<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page` = '/closingday' WHERE `page` ='joursFeries/index.php';";

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page` ='joursFeries/valid.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/closingday' WHERE `url` ='joursFeries/index.php';";
?>