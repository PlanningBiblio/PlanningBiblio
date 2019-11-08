<?php
$sql[] = "UPDATE `{$dbprefix}acces` SET `page` = '/absence' WHERE `page` = 'absences/ajouter.php';";
$sql[] = "UPDATE `{$dbprefix}menu` SET `url` = '/absence' WHERE `url` = 'absences/ajouter.php';";
$sql[] = "DELETE FROM `{$dbprefix}acces` WHERE `page` = 'absences/modif.php';";

$sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Non justifiée' WHERE `valeur` = 'Non justifi&eacute;e';";
$sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Congés payés' WHERE `valeur` = 'Congés pay&eacute;s';";
$sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Congé maternité' WHERE `valeur` = 'Cong&eacute; maternit&eacute;';";
$sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Réunion syndicale' WHERE `valeur` = 'R&eacute;union syndicale';";
$sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Grève' WHERE `valeur` = 'Gr&egrave;ve';";
$sql[] = "UPDATE `{$dbprefix}select_abs` SET `valeur` = 'Réunion' WHERE `valeur` = 'R&eacute;union';";
