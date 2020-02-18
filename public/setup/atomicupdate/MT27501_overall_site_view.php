<?php
$sql[]="CREATE TABLE `{$dbprefix}hidden_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL DEFAULT '0',
  `hidden_sites` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$sql[] = "INSERT INTO `{$dbprefix}menu` (niveau1, niveau2, titre, url) VALUES(30, 105, 'Tous les sites', 'planning/poste/overall.php')";
$sql[] = "INSERT INTO `{$dbprefix}acces` (nom, groupe_id, page, ordre) VALUES('Tous les sites', 100, 'planning/poste/overall.php', 0)";
