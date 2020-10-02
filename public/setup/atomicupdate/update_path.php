<?php

$sql[]="UPDATE `{$dbprefix}acces` SET `page`='/statistics/position' WHERE `page`='statistiques/postes.php' ;";

$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('stats postes par agent', 17, 'Accès aux statistiques', 'statistiques/postes.php','Statistiques','170');";

$sql[]="UPDATE `{$dbprefix}menu` SET `url`='/statistics/position' WHERE `url`='statistiques/postes.php' ;";

?>
