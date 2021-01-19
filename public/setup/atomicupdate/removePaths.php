<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page`='' WHERE `groupe_id` > 0 AND `page` LIKE '%planningHebdo%' OR `page` = '/workinghour'";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `groupe_id` <= 0 AND `page` LIKE '%planningHebdo%' OR `page` = '/workinghour'";
