<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES
          ('UserPreferences', 'boolean', '1', 'Autoriser les utilisateurs à enregistrer leurs préférences de navigation.', ' Divers', '', NULL, 40);";

$sql[] = "CREATE TABLE IF NOT EXISTS `{$dbprefix}user_preferences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_id` int(11) NOT NULL,
  `pref` varchar(50) NOT NULL UNIQUE,
  `value` smallint NOT NULL,
  `category` varchar(50),
  `description` varchar(250),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

