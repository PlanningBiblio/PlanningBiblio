<?php

$sql[] = "CREATE TABLE `{$dbprefix}notifications_history` (
  id int(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  subject text NOT NULL,
  message text NOT NULL,
  date DATETIME NOT NULL,
  status text NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";

$sql[] = "CREATE TABLE `{$dbprefix}notifications_history_agents` (
  notification_id int(11) NOT NULL,
  agent_id int(11) NOT NULL,
  FOREIGN KEY(notification_id) REFERENCES {$dbprefix}notifications_history(id),
  FOREIGN KEY(agent_id) REFERENCES {$dbprefix}personnel(id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;";
