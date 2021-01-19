<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `groupe`='', `page`='/workinghour' WHERE `nom`='Planning Hebdo - suppression'";
$sql[]="UPDATE `{$dbprefix}acces` SET `nom`='Planning Hebdo - Admin N1' WHERE `nom`='Planning Hebdo - Index '";
$sql[]="UPDATE `{$dbprefix}acces` SET `page`='' WHERE `groupe`!=''  AND (`page` LIKE '%planningHebdo%' OR `page` = '/workinghour')";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `groupe`='' AND (`page` LIKE '%planningHebdo%' OR `page` = '/workinghour')";
