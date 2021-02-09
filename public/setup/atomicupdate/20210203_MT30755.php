<?php

$sql[] = "UPDATE `{$dbprefix}config` SET `valeurs` = '0,1,2,3,4,5,6,7,8,9,10' WHERE `nom` = 'nb_semaine';";

for ($i = 1; $i <= 10; $i++) {
    $sql[] = "INSERT INTO `{$dbprefix}config` (`nom`, `type`, `valeur`, `commentaires`, `categorie`, `valeurs`, `ordre`) VALUES ('Multisites-site$i-cycles','enum','','Nombre de semaines pour la rotation des heures de présence (prendra la valeur de l\'option de configuration nb_semaine si non définie)','Multisites',',1,2,3,4,5,6,7,8,9,10','" . ($i + 1) . "7');";
}

$sql[] = "UPDATE `{$dbprefix}config` SET `commentaires` = 'Nombre de semaines pour la rotation des heures de présence. Les valeurs supérieures à 3 ne peuvent être utilisées que si le paramètre PlanningHebdo est coché' WHERE `nom` = 'nb_semaine';";

