<?php

$sql[]=" UPDATE `{$dbprefix}acces` SET `page`='/framework-group' WHERE `page` = 'planning/postes_cfg/groupes.php';";

$sql[]=" DELETE FROM `{$dbprefix}acces` WHERE `page`= 'planning/postes_cfg/groupes2.php';";
