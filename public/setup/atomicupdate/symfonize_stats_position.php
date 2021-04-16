<?php

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/postes.php' ;";

$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/statistics/position' WHERE `url`='statistiques/postes.php' ;";

?>
