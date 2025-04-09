<?php

$sql[] = "INSERT IGNORE INTO `{$dbprefix}config` (`nom`, `type`, `valeurs`, `valeur`, `categorie`, `ordre`, `commentaires`) VALUES ('Planning-IgnoreBreaks', 'boolean', '', '0', 'Planning','0', 'Si cette case est cochée, les périodes de pauses (ex: pause déjeuner) définies dans les heures de présence seront ignorées dans le menu permettant d\'ajouter les agents dans le planning et lors de l\'importation des modèles.');";

$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 1 where `nom` = 'ctrlHresAgents';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 2 where `nom` = 'CatAFinDeService';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 3 where `nom` = 'Planning-NbAgentsCellule';";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = 4 where `nom` = 'Planning-lignesVides';";
