<?php

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Conges-fullday-reference-time','text','','Temps de référence (en heures) pour une journée complète. Si ce champ est renseigné et que les crédits de congés sont gérés en jours, la différence de temps de chaque journée sera créditée ou débitée du solde des récupérations. Format : entier ou décimal. Exemple : pour 7h30, tapez 7.5', 'Congés', '', '10');";

$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '8' WHERE `nom` = 'Conges-demi-journees';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '9' WHERE `nom` = 'Conges-fullday-switching-time';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '11' WHERE `nom` = 'Conges-planningVide';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '12' WHERE `nom` = 'Conges-apresValidation';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre`= '13' WHERE `nom` = 'Conges-Rappels-N1';";

$sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN `regul_id` INT(11) NULL DEFAULT NULL AFTER `info_date`;";
$sql[] = "ALTER TABLE `{$dbprefix}conges` ADD COLUMN `origin_id` INT(11) NULL DEFAULT NULL AFTER `regul_id`;";
