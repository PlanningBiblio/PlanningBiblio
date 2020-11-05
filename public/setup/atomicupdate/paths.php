<?php

//todo : supprimer ligne 4 apres validation de la PR254
$sql[]="INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`groupe`,`page`,`categorie`,`ordre`) VALUES ('Planning Hebdo - Index','1101','Gestion des heures de présences, validation niveau 1','/workinghour','Heures de présence','80');";

$sql[]="UPDATE `{$dbprefix}acces` SET `nom` = 'Planning Hebdo - Ajout' `page`='/workinghour/add' WHERE `page`='planningHebdo/modif.php' ;";

$sql[]="DELETE FROM `{$dbprefix}acces` WHERE `page`='planningHebdo/valid.php' ;";