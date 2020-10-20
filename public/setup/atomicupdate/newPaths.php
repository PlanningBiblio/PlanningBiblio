<?php
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Statistiques', 17, 'Accès aux statistiques', '/statistics/service','Statistiques','170');";
$sql[]="UPDATE `{$dbprefix}menu` SET `url` = '/statistics/service' WHERE `url` = 'statistiques/service.php' ;";

