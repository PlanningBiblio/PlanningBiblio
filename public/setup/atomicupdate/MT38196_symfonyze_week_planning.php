<?php

// MT38196 symfonyze week planning.
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/poste/semaine.php';";
$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planning/index.php';";