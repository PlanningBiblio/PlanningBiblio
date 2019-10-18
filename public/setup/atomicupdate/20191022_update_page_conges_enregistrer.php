<?php
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/holiday/new' WHERE `url` = 'conges/enregistrer.php';";
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/holiday/new' WHERE `page` = 'conges/enregistrer.php';";
