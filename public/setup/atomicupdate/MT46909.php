<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Mail-UnsubscribeLink', 'boolean', '', '1', 'Messagerie','120', 'Ajouter un lien de désinscription dans l\'entête des e-mails (recommandé).');";

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Active ou désactive l\'envoi des e-mails.', `ordre` = '10' WHERE `nom` = 'Mail-IsEnabled';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Utiliser un relais SMTP (IsSMTP) ou le programme \"mail\" du serveur (IsMail).', `ordre` = '20' WHERE `nom` = 'Mail-IsMail-IsSMTP';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Nom d\'hôte du serveur pour l\'envoi des e-mails.', `ordre` = '30' WHERE `nom` = 'Mail-Hostname';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '40' WHERE `nom` = 'Mail-Host';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '50' WHERE `nom` = 'Mail-Port';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Cryptage utilisé par le serveur STMP.', `ordre` = '60' WHERE `nom` = 'Mail-SMTPSecure';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Le serveur SMTP requiert-il une authentification ?', `ordre` = '70' WHERE `nom` = 'Mail-SMTPAuth';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Nom d\'utilisateur pour le serveur SMTP.', `ordre` = '80' WHERE `nom` = 'Mail-Username';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Mot de passe pour le serveur SMTP.', `ordre` = '90' WHERE `nom` = 'Mail-Password';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Adresse e-mail de l\'expediteur.', `ordre` = '100' WHERE `nom` = 'Mail-From';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Nom de l\'expediteur.', `ordre` = '110' WHERE `nom` = 'Mail-FromName';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Signature des e-mails.', `ordre` = '130' WHERE `nom` = 'Mail-Signature';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Adresses e-mails de la cellule planning, séparées par des ;', `ordre` = '140' WHERE `nom` = 'Mail-Planning';";

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Version de l\'application' WHERE `nom` = 'Version';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'URL de l\'application' WHERE `nom` = 'URL';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Affiche ou non l\'utilisateur \"tout le monde\" dans le menu.' WHERE `nom` = 'toutlemonde';";
