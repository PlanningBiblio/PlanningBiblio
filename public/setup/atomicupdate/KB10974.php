<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '[[0,\"\"],[1,\"simple\"],[2,\"détaillé\"],[3,\"absents et présents\"],[4,\"absents et présents filtrés par site\"]]', `commentaires` = 'Choix des listes de présence et d\'absences à afficher sous les plannings' WHERE `nom` = 'Absences-planning';";
