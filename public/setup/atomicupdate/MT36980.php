<?php

$sql[] = "CREATE TABLE `pl_poste_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `perso_ids` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `beginning` time NOT NULL DEFAULT '00:00:00',
  `end` time NOT NULL DEFAULT '00:00:00',
  `site` int(11) NOT NULL DEFAULT 1,
  `position` int(11) NOT NULL DEFAULT 0,
  `action` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` int(11) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
