<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Absences-blocage', 'boolean', '0', 'Permettre le blocage des absences et congés sur une période définie par les gestionnaires. Ce paramètre empêchera les agents qui n\'ont pas le droits de gérer les absences d\'enregistrer absences et congés sur les périodes définies. En configuration multi-sites, les agents de tous les sites seront bloqués sans distinction.', 'Absences', '', NULL, 5);";

$sql[] = "INSERT IGNORE INTO `{$dbprefix}menu` (`niveau1`, `niveau2`, `titre`, `url`, `condition`) VALUES (10, 25, 'Bloquer les absences', '/absence/block', 'config=Absences-blocage');";

$sql[]="CREATE TABLE IF NOT EXISTS `{$dbprefix}absence_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start` date NOT NULL DEFAULT '0000-00-00',
  `end` date NOT NULL DEFAULT '0000-00-00',
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

