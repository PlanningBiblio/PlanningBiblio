<?php
$sql[] = "UPDATE `{$dbprefix}acces` SET `nom` = 'Congés - Nouveau' WHERE `page` = '/holiday/new';";
$sql[] = "INSERT INTO `{$dbprefix}acces` (`nom`,`groupe_id`,`page`) VALUES (\"Congés - Enregistrer\",'100','/holiday');";