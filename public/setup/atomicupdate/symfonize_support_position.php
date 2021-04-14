<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/postes_renfort.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/statistics/supportposition' WHERE `url`='statistiques/postes_renfort.php';";
