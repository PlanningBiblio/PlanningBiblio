<?php

$sql[]="INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `valeurs`, `categorie`, `ordre`, `commentaires`) VALUES ('Conges-fullday-switching-time', 'text', '4', '', 'Congés', '7', 'Temps définissant la bascule entre une demi-journée et une journée complète lorsque les crédits de congés sont comptés en jours. Format : entier ou décimal. Exemple : pour 3h30, tapez 3.5');";
