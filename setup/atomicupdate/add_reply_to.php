<?php
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-ReplyTo','text','{$config['Mail-From']}','Adresse email de réponse.','Messagerie','','70');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Mail-ReturnPath','text','{$config['Mail-From']}','Adresse email de retour pour les erreurs.','Messagerie','','75');";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '10' WHERE `nom` = 'Mail-IsEnabled';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '15' WHERE `nom` = 'Mail-IsMail-IsSMTP';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '20' WHERE `nom` = 'Mail-WordWrap';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '25' WHERE `nom` = 'Mail-Hostname';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '30' WHERE `nom` = 'Mail-Host';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '35' WHERE `nom` = 'Mail-Port';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '40' WHERE `nom` = 'Mail-SMTPSecure';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '45' WHERE `nom` = 'Mail-SMTPAuth';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '50' WHERE `nom` = 'Mail-Username';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '55' WHERE `nom` = 'Mail-Password';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '60' WHERE `nom` = 'Mail-From';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '65' WHERE `nom` = 'Mail-FromName';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '80' WHERE `nom` = 'Mail-Signature';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '85' WHERE `nom` = 'Mail-Planning';";
