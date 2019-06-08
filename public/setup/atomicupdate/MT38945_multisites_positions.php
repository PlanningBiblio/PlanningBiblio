<?php
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site1-position', 'enum', '1', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°1 dans les menus', 'Multisites', '27');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site2-position', 'enum', '2', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°2 dans les menus', 'Multisites', '37');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site3-position', 'enum', '3', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°3 dans les menus', 'Multisites', '47');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site4-position', 'enum', '4', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°4 dans les menus', 'Multisites', '57');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site5-position', 'enum', '5', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°5 dans les menus', 'Multisites', '67');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site6-position', 'enum', '6', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°6 dans les menus', 'Multisites', '77');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site7-position', 'enum', '7', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°7 dans les menus', 'Multisites', '87');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site8-position', 'enum', '8', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°8 dans les menus', 'Multisites', '97');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site9-position', 'enum', '9', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°9 dans les menus', 'Multisites', '107');";
$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Multisites-site10-position', 'enum', '10', '0,1,2,3,4,5,6,7,8,9,10', 'Position du sites N°10 dans les menus', 'Multisites', '117');";

$sql[] = "ALTER TABLE `{$dbprefix}appel_dispo` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_notifications` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_modeles_tab` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_tab_grp` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}pl_poste_verrou` CHANGE `site` `site` INT(3);";
$sql[] = "ALTER TABLE `{$dbprefix}postes` CHANGE `site` `site` INT(3);";
