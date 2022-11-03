<?php

$sql[] = "CREATE TABLE `{$dbprefix}pl_position_history` (
  id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  perso_ids TEXT NOT NULL,
  date DATE NULL,
  beginning TIME NOT NULL,
  end TIME NOT NULL,
  site INT(11) NOT NULL DEFAULT 1,
  position INT(11) NOT NULL,
  action VARCHAR(20) NOT NULL,
  undone TINYINT NOT NULL DEFAULT 0,
  archive TINYINT NOT NULL DEFAULT 0,
  play_before TINYINT NOT NULL DEFAULT 0,
  updated_by INT(11) NOT NULL,
  updated_at DATETIME NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";
