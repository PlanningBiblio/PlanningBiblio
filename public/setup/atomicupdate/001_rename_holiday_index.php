<?php 

$sql[] = "UPDATE `{$dbprefix}acces` SET `page`='/holiday' WHERE `page`='/holiday/index';";
$sql[] = "UPDATE `{$dbprefix}menu` SET `url`='/holiday' WHERE `url`='/holiday/index';";
$sql[] = "UPDATE `{$dbprefix}menu` SET `url`='/holiday?recup=1' WHERE `url`='/holiday/index?recup=1';";