<?php

$sql[]="UPDATE `{$dbprefix}config` SET `commentaires` = 'Nombre de semaines pour la rotation des heures de présence' WHERE `nom` = 'nb_semaine';";
$sql[]="UPDATE `{$dbprefix}config` SET `valeurs` = '[[0, \"Désactivé\"], [1, \"Horaires différents les semaines avec samedi travaillé\"], [2, \"Horaires différents les semaines avec samedi travaillé et les semaines à ouverture restreinte\"]]' WHERE `nom` = 'EDTSamedi';";