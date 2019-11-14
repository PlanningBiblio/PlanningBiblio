<?php
$sql[] = "CREATE TABLE `{$dbprefix}absences_documents` (id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,absence_id int(11) NOT NULL,filename text NOT NULL, date DATETIME NOT NULL);";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}acces` VALUES(NULL, 'Absences - Voir document', 100, '', '/absences/document', 0, 'Absences')";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}acces` VALUES(NULL, 'Absences - liste documents', 100, '', '/absences/documents', 0, 'Absences')";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Absences-DelaiSuppressionDocuments', 'text', '90', 'Absences','100', 'Les documents associ&eacute;s aux absences sont supprim&eacute;s au-del&agrave; du nombre de jours d&eacute;finis par ce param&egrave;tre.');";
