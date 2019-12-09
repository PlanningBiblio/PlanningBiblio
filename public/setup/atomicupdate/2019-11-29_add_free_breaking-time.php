<?php
$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('PlanningHebdo-PauseLibre', 'boolean', '0', 'Ajoute la possibilité de saisir un temps de pause libre dans le planning de présence (Module Planning Hebdo uniquement)', 'Heures de présence', 65);";

$sql[] = "ALTER TABLE `{$dbprefix}planning_hebdo` ADD COLUMN `breaktime` TEXT NOT NULL AFTER `temps`;";
