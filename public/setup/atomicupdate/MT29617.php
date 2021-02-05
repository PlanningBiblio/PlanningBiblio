<?php

$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `ordre`) VALUES ('Conges-tous', 'boolean', '0', 'Autoriser l\'enregistrement de congés pour tous les agents en une fois','Congés','4');";
$sql[] = "UPDATE `{$dbprefix}config` SET `ordre` = '5' WHERE `nom` = 'Conges-Validation-N2';";

