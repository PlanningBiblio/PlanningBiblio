<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `extra`, `ordre`) VALUES ('Auth-LoginLayout', 'enum', 'firstname.lastname', 'Schéma à utiliser pour la construction des logins', 'Authentification', 'firstname.lastname,lastname.firstname,mail,mailPrefix', NULL, 10);";

$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 5 WHERE `nom` = 'Auth-Mode';";
