<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/agents.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`= '/statistics/agent' WHERE `url` = 'statistiques/agents.php';";
