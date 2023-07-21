
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` TEXT COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `execution_time` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00', 
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
