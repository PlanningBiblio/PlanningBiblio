<?php

$sql[]="UPDATE`{$dbprefix}acces` SET  `page`='/statistics/positionsummary' WHERE `page`='statistiques/postes_synthese.php';";

$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/statistics/positionsummary' WHERE `url`='statistiques/postes_synthese.php';";
