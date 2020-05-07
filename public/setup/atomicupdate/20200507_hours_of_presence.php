<?php

$sql[] = "UPDATE `{$dbprefix}menu` SET `titre` = 'Heures de présence' WHERE `url` = 'planningHebdo/index.php';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Date de début permettant la rotation des heures de présence (pour l\'utilisation de 3 plannings hebdomadaires. Format JJ/MM/AAAA)' WHERE `nom` = 'dateDebutPlHebdo';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Horaires différents les semaines avec samedi travaillé et semaines à ouverture restreinte' WHERE `nom` = 'EDTSamedi';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Utiliser le module \“Planning Hebdo\”. Ce module permet d\'enregistrer plusieurs horaires de présence par agent en définissant des périodes d\'utilisation. (Incompatible avec l\'option EDTSamedi)' WHERE `nom` = 'PlanningHebdo';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Autoriser les agents à saisir leurs heures de présence (avec le module Planning Hebdo). Les heures saisies devront être validées par un administrateur' WHERE `nom` = 'PlanningHebdo-Agents';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Ajoute la possibilité de saisir un temps de pause libre dans les heures de présence (Module Planning Hebdo uniquement)' WHERE `nom` = 'PlanningHebdo-PauseLibre';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications d\'enregistrement de nouvelles heures de présence' WHERE `nom` = 'PlanningHebdo-notifications1';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Destinataires des notifications de modification des heures de présence' WHERE `nom` = 'PlanningHebdo-notifications2';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'La validation niveau 2 des heures de présence peut se faire directement ou doit attendre la validation niveau 1' WHERE `nom` = 'PlanningHebdo-Validation-N2';";