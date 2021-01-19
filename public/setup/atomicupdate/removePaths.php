<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page`='' WHERE `groupe`!=''  AND `page` LIKE '%planningHebdo%' OR `page` = '/workinghour'";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `groupe`='' AND `page` LIKE '%planningHebdo%' OR `page` = '/workinghour'";
