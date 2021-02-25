<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Autoriser l\'enregistrement d\'absences sur des plannings en cours d\'élaboration' WHERE `nom`= 'Absences-planningVide';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Autoriser l\'enregistrement d\'absences après validation des plannings' WHERE `nom`= 'Absences-apresValidation';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '12' WHERE `nom`= 'Conges-Rappels-N1';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '14' WHERE `nom`= 'Conges-Rappels-N2';";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-planningVide','boolean','1','Congés', 'Autoriser l\'enregistrement de congés sur des plannings en cours d\'élaboration','8');";
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `categorie`, `commentaires`, `ordre`) VALUES ('Conges-apresValidation','boolean','1', 'Congés', 'Autoriser l\'enregistrement de congés après validation des plannings', '9');";

