<?php
$sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `commentaires`, `ordre` ) VALUES ('Conges-Mode', 'enum2', 'heures', '[[\"heures\",\"Heures\"],[\"jours\",\"Jours\"]]', 'Congés', 'Décompte des congés en heures ou en jours', '2');";
