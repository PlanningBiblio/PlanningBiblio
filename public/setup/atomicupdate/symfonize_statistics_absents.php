<?php
    $sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='statistiques/absences.php';";
    $sql[]="UPDATE `{$dbprefix}menu` SET  `url`='/statistics/absence' WHERE `url`='statistiques/absences.php';";
