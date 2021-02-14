<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `valeur` = 'softness' WHERE `nom` = 'Affichage-theme' AND `valeur` = 'default';";
$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Thème de l\'application.' WHERE `nom` = 'Affichage-theme';";
