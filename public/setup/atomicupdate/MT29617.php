<?php

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Conges-tous', 'boolean', '0', 'Autoriser l\'enregistrement de congés pour tous les agents en une fois','Congés','6');";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='4' WHERE `nom`='Conges-validation';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='5' WHERE `nom`='Conges-Validation-N2';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='6' WHERE `nom`='Conges-tous';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='7' WHERE `nom`='Conges-Rappels';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='8' WHERE `nom`='Conges-Rappels-Jours';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='9' WHERE `nom`='Conges-demi-journees';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='10' WHERE `nom`='Conges-fullday-switching-time';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='11' WHERE `nom`='Conges-fullday-reference-time';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='12' WHERE `nom`='Conges-planningVide';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='13' WHERE `nom`='Conges-apresValidation';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='14' WHERE `nom`='Conges-Rappels-N1';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='15' WHERE `nom`='Conges-Rappels-N2';";
$sql[] = "UPDATE `{$dbprefix}config` set `ordre`='16' WHERE `nom`='Recup-Uneparjour';";
