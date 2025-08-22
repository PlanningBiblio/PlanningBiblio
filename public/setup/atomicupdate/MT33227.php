<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`, `technical`) VALUES ('Mail-SMTPAutoTLS', 'boolean', '', '1', 'Messagerie', '70', 'Activer ou désactiver le mode Auto TLS', 1);";


$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 10 WHERE `nom` = 'Mail-IsEnabled';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 20 WHERE `nom` = 'Mail-IsMail-IsSMTP';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 30 WHERE `nom` = 'Mail-Hostname';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 40 WHERE `nom` = 'Mail-Host';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 50 WHERE `nom` = 'Mail-Port';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 60 WHERE `nom` = 'Mail-SMTPSecure';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 80 WHERE `nom` = 'Mail-SMTPAuth';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 90 WHERE `nom` = 'Mail-Username';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 100 WHERE `nom` = 'Mail-Password';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 110 WHERE `nom` = 'Mail-From';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 120 WHERE `nom` = 'Mail-FromName';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 130 WHERE `nom` = 'Mail-Signature';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 140 WHERE `nom` = 'Mail-Planning';";
