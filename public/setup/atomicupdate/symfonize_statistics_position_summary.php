<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/postes_synthese.php';";
$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/statistics/positionsummary' WHERE `url`='statistiques/postes_synthese.php';";
