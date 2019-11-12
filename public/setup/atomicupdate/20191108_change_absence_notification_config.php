<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications de nouvelles absences (Circuit A)' WHERE `nom` = 'Absences-notifications1';";
$sql[] = "UPDATE `{$dbprefix}config` SET `nom` = 'Absences-notifications-A1' WHERE `nom` = 'Absences-notifications1';";

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications de modification d&apos;absences (Circuit A)' WHERE `nom` = 'Absences-notifications2';";
$sql[] = "UPDATE `{$dbprefix}config` SET `nom` = 'Absences-notifications-A2' WHERE `nom` = 'Absences-notifications2';";

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications des validations niveau 1 (Circuit A)' WHERE `nom` = 'Absences-notifications3';";
$sql[] = "UPDATE `{$dbprefix}config` SET `nom` = 'Absences-notifications-A3' WHERE `nom` = 'Absences-notifications3';";

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications des validations niveau 2 (Circuit A)' WHERE `nom` = 'Absences-notifications4';";
$sql[] = "UPDATE `{$dbprefix}config` SET `nom` = 'Absences-notifications-A4' WHERE `nom` = 'Absences-notifications4';";


$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B1','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de nouvelles absences (Circuit B)','Absences','40');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B2','checkboxes','[0,1,2,3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications de modification d&apos;absences (Circuit B)','Absences','50');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B3','checkboxes','[1]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 1 (Circuit B)','Absences','60');";
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `commentaires`, `categorie`, `ordre`) VALUES ('Absences-notifications-B4','checkboxes','[3]','[[0,\"Agents ayant le droit de g&eacute;rer les absences\"],[1,\"Responsables directs\"],[2,\"Cellule planning\"],[3,\"Agent concern&eacute;\"]]','Destinataires des notifications des validations niveau 2 (Circuit B)','Absences','65');";

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Gestion des notifications et des droits de validations agent par agent. Si cette option est activée, les paramètres Absences-notifications-A1, A2, A3 et A4 ou B1, B2, B3 et B4 seront écrasés par les choix fait dans la page de configuration des notifications du menu Administration - Notifications / Validations' WHERE `nom` = 'Absences-notifications-agent-par-agent';",

$sql[] = "ALTER table `{$dbprefix}select_abs` ADD COLUMN `notification_workflow` CHAR(1) AFTER `type`;";
$sql[] = "UPDATE `{$dbprefix}select_abs` SET `notification_workflow` = 'A'; WHERE `type` != 1";