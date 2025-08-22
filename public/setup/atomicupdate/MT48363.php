<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`, `technical`) VALUES ('OIDC-Debug', 'boolean', '', '0', 'OpenID Connect', '60', 'Debug mode. Logs information to the log table.', 1);";
